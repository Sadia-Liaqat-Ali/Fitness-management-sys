<?php
ob_start();
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get trainer_id
$stmt = $conn->prepare("SELECT trainer_id FROM Trainers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$trainer = $stmt->get_result()->fetch_assoc();
$trainer_id = $trainer['trainer_id'];

/* =====================================================
   FETCH SCHEDULED APPOINTMENTS (NO DIET PLAN)
   ===================================================== */
$appointments = $conn->prepare("
    SELECT 
        a.appointment_id,
        a.appointment_date,
        u.full_name,
        u.user_id
    FROM appointments a
    JOIN users u ON u.user_id = a.user_id
    LEFT JOIN dietary_plans dp ON dp.appointment_id = a.appointment_id
    WHERE a.trainer_id = ?
      AND a.status = 'scheduled'
      AND a.appointment_date >= NOW()
      AND dp.diet_id IS NULL
    ORDER BY a.appointment_date ASC
");
$appointments->bind_param("i", $trainer_id);
$appointments->execute();
$appointments_result = $appointments->get_result();

/* =====================================================
   FETCH CLASSES WITH CONFIRMED MEMBERS (NO DIET PLAN)
   ===================================================== */
$classes = $conn->prepare("
    SELECT 
        fc.class_id,
        fc.class_name,
        fc.class_date
    FROM fitness_classes fc
    LEFT JOIN dietary_plans dp ON dp.class_id = fc.class_id
    WHERE fc.trainer_id = ?
      AND fc.is_active = 1
      AND fc.class_date >= NOW()
      AND dp.diet_id IS NULL
      AND EXISTS (
          SELECT 1 FROM class_bookings cb
          WHERE cb.class_id = fc.class_id
            AND cb.status = 'confirmed'
      )
    ORDER BY fc.class_date ASC
");
$classes->bind_param("i", $trainer_id);
$classes->execute();
$classes_result = $classes->get_result();

/* =====================================================
   FETCH CLIENTS WITH NO DIET PLAN
   ===================================================== */
$clients = $conn->prepare("
    SELECT DISTINCT u.user_id, u.full_name
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    WHERE a.trainer_id = ?
      AND NOT EXISTS (
          SELECT 1 FROM dietary_plans dp 
          WHERE dp.user_id = u.user_id 
          AND dp.trainer_id = ?
      )
    ORDER BY u.full_name ASC
");
$clients->bind_param("ii", $trainer_id, $trainer_id);
$clients->execute();
$clients_result = $clients->get_result();

/* =====================================================
   HANDLE FORM SUBMISSION
   ===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $calories = !empty($_POST['calories']) ? $_POST['calories'] : NULL;
    $meal_plan = trim($_POST['meal_plan']);
    $notes = trim($_POST['notes']);
    $appointment_id = !empty($_POST['appointment_id']) ? $_POST['appointment_id'] : NULL;
    $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : NULL;
    $user_id_assign = !empty($_POST['user_id']) ? $_POST['user_id'] : NULL;

    // Validate assignment (only one type)
    $assignment_count = 0;
    if ($appointment_id) $assignment_count++;
    if ($class_id) $assignment_count++;
    if ($user_id_assign) $assignment_count++;
    
    if ($assignment_count > 1) {
        $error = "Please assign to only ONE of: Appointment, Class, or Client directly.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO dietary_plans
            (trainer_id, appointment_id, class_id, user_id, title, description, calories, meal_plan, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiisisss",
            $trainer_id,
            $appointment_id,
            $class_id,
            $user_id_assign,
            $title,
            $description,
            $calories,
            $meal_plan,
            $notes
        );
        
        if ($stmt->execute()) {
            header("Location: trainer_diet_plans.php");
            exit();
        } else {
            $error = "Failed to create diet plan: " . $conn->error;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<title>Create Diet Plan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.container-bordered {
    border: 2px solid #6c757d;
    border-radius: 10px;
    padding: 25px;
    background-color: #f8f9fa;
    max-width: 950px;
    margin: auto;
}
.section-title {
    background-color: #d1e7dd;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}
.helper-text {
    font-size: 0.85rem;
    color: #6c757d;
}
.required:after {
    content: " *";
    color: red;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<h5 class="section-title">Create Diet Plan</h5>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">

<!-- TITLE -->
<div class="mb-3">
<label class="form-label required">Diet Plan Title</label>
<input type="text" name="title" class="form-control" required maxlength="100">
</div>

<!-- DESCRIPTION -->
<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="3" maxlength="500"></textarea>
</div>

<div class="row">
    <!-- CALORIES -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Daily Calories (optional)</label>
        <input type="number" name="calories" class="form-control" min="0" max="10000">
    </div>
</div>

<!-- MEAL PLAN -->
<div class="mb-3">
<label class="form-label required">Meal Plan Details</label>
<textarea name="meal_plan" class="form-control" rows="5" required></textarea>
<span class="helper-text">Include breakfast, lunch, dinner, snacks, portions, etc.</span>
</div>

<!-- NOTES -->
<div class="mb-3">
<label class="form-label">Additional Notes</label>
<textarea name="notes" class="form-control" rows="3" maxlength="500"></textarea>
<span class="helper-text">Allergies, supplements, timing instructions, etc.</span>
</div>

<h6 class="mt-4 mb-3">Assignment (Choose One)</h6>

<div class="row">
    <!-- APPOINTMENTS -->
    <div class="col-md-4 mb-3">
        <label class="form-label">Assign to Appointment</label>
        <select name="appointment_id" class="form-select">
            <option value="">-- Select Appointment --</option>
            <?php while ($a = $appointments_result->fetch_assoc()): ?>
                <option value="<?= $a['appointment_id'] ?>">
                    <?= htmlspecialchars($a['full_name']) ?> —
                    <?= date('d M Y, h:i A', strtotime($a['appointment_date'])) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <span class="helper-text">
            Scheduled appointments without diet plan
        </span>
    </div>

    <!-- CLASSES -->
    <div class="col-md-4 mb-3">
        <label class="form-label">Assign to Class</label>
        <select name="class_id" class="form-select">
            <option value="">-- Select Class --</option>
            <?php while ($c = $classes_result->fetch_assoc()): ?>
                <option value="<?= $c['class_id'] ?>">
                    <?= htmlspecialchars($c['class_name']) ?> —
                    <?= date('d M Y, h:i A', strtotime($c['class_date'])) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <span class="helper-text">
            Classes with confirmed members
        </span>
    </div>

    <!-- DIRECT CLIENT ASSIGNMENT -->
    <div class="col-md-4 mb-3">
        <label class="form-label">Assign Directly to Client</label>
        <select name="user_id" class="form-select">
            <option value="">-- Select Client --</option>
            <?php while ($client = $clients_result->fetch_assoc()): ?>
                <option value="<?= $client['user_id'] ?>">
                    <?= htmlspecialchars($client['full_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <span class="helper-text">
            Clients without a diet plan
        </span>
    </div>
</div>

<!-- ACTIONS -->
<div class="d-flex justify-content-between mt-4">
    <a href="trainer_diet_plans.php" class="btn btn-secondary">Back to List</a>
    <button type="submit" class="btn btn-success">Save Diet Plan</button>
</div>

</form>
</div>

<script>
// Reset other selects when one is chosen
document.querySelectorAll('select[name="appointment_id"], select[name="class_id"], select[name="user_id"]').forEach(select => {
    select.addEventListener('change', function() {
        if (this.value !== "") {
            document.querySelectorAll('select[name="appointment_id"], select[name="class_id"], select[name="user_id"]').forEach(other => {
                if (other !== this) {
                    other.value = "";
                }
            });
        }
    });
});
</script>
</body>
</html>

<?php ob_end_flush(); ?>