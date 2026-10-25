<?php
ob_start();
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$diet_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get trainer_id
$t = $conn->query("SELECT trainer_id FROM Trainers WHERE user_id = $user_id")->fetch_assoc();
$trainer_id = $t['trainer_id'];

/* ================= FETCH DIET PLAN ================= */
$stmt = $conn->prepare("
    SELECT * FROM dietary_plans 
    WHERE diet_id = ? AND trainer_id = ?
");
$stmt->bind_param("ii", $diet_id, $trainer_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) die("Diet plan not found or you don't have permission.");

/* ================= FETCH CLIENTS ================= */
$clients = $conn->prepare("
    SELECT DISTINCT u.user_id, u.full_name
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    WHERE a.trainer_id = ?
    UNION
    SELECT u.user_id, u.full_name
    FROM Users u
    JOIN dietary_plans dp ON dp.user_id = u.user_id
    WHERE dp.trainer_id = ?
    ORDER BY full_name ASC
");
$clients->bind_param("ii", $trainer_id, $trainer_id);
$clients->execute();
$clients_result = $clients->get_result();

/* ================= FETCH CLASSES ================= */
$classes = $conn->prepare("
    SELECT class_id, class_name
    FROM fitness_classes
    WHERE trainer_id = ?
    ORDER BY class_name ASC
");
$classes->bind_param("i", $trainer_id);
$classes->execute();
$classes_result = $classes->get_result();

/* ================= FETCH APPOINTMENTS ================= */
$appointments = $conn->prepare("
    SELECT a.appointment_id, u.full_name, a.appointment_date
    FROM appointments a
    JOIN Users u ON a.user_id = u.user_id
    WHERE a.trainer_id = ?
      AND a.status = 'scheduled'
      AND a.appointment_date >= NOW()
    ORDER BY a.appointment_date ASC
");
$appointments->bind_param("i", $trainer_id);
$appointments->execute();
$appointments_result = $appointments->get_result();

/* ================= UPDATE PLAN ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $calories = !empty($_POST['calories']) ? $_POST['calories'] : NULL;
    $meal_plan = $_POST['meal_plan'];
    $notes = $_POST['notes'];
    $user_assign = !empty($_POST['user_id']) ? $_POST['user_id'] : NULL;
    $class_assign = !empty($_POST['class_id']) ? $_POST['class_id'] : NULL;
    $appointment_assign = !empty($_POST['appointment_id']) ? $_POST['appointment_id'] : NULL;

    // Validate only one assignment
    $assignment_count = 0;
    if ($user_assign) $assignment_count++;
    if ($class_assign) $assignment_count++;
    if ($appointment_assign) $assignment_count++;
    
    if ($assignment_count > 1) {
        $error = "Please assign to only ONE of: Client, Class, or Appointment.";
    } else {
        $update = $conn->prepare("
            UPDATE dietary_plans
            SET title=?, description=?, calories=?, meal_plan=?, notes=?,
                user_id=?, class_id=?, appointment_id=?
            WHERE diet_id=? AND trainer_id=?
        ");
        $update->bind_param(
            "ssisssiiii",
            $title,
            $description,
            $calories,
            $meal_plan,
            $notes,
            $user_assign,
            $class_assign,
            $appointment_assign,
            $diet_id,
            $trainer_id
        );
        
        if ($update->execute()) {
            header("Location: trainer_diet_plans.php");
            exit();
        } else {
            $error = "Failed to update diet plan: " . $conn->error;
        }
    }
}
?>

<!doctype html>
<html>
<head>
<title>Edit Diet Plan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.container-bordered {
    border: 2px solid #6c757d;
    border-radius: 10px;
    padding: 20px;
    background-color: #f8f9fa;
    max-width: 950px;
    margin: auto;
}
.section-title {
    background-color: #d1e7dd;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}
.required:after {
    content: " *";
    color: red;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<h5 class="section-title">Edit Diet Plan</h5>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label class="form-label required">Diet Plan Title</label>
<input type="text" name="title" class="form-control"
       value="<?= htmlspecialchars($plan['title']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($plan['description']) ?></textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Daily Calories</label>
        <input type="number" name="calories" class="form-control" 
               value="<?= $plan['calories'] ?>" min="0" max="10000">
    </div>
</div>

<div class="mb-3">
<label class="form-label required">Meal Plan Details</label>
<textarea name="meal_plan" class="form-control" rows="6" required><?= htmlspecialchars($plan['meal_plan']) ?></textarea>
</div>

<div class="mb-3">
<label class="form-label">Additional Notes</label>
<textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($plan['notes']) ?></textarea>
</div>

<h6 class="mt-4 mb-3">Assignment (Choose One)</h6>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Assign to Client</label>
        <select name="user_id" class="form-select">
            <option value="">-- None --</option>
            <?php while ($c = $clients_result->fetch_assoc()): ?>
                <option value="<?= $c['user_id'] ?>"
                    <?= ($plan['user_id'] == $c['user_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['full_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Assign to Class</label>
        <select name="class_id" class="form-select">
            <option value="">-- None --</option>
            <?php while ($cls = $classes_result->fetch_assoc()): ?>
                <option value="<?= $cls['class_id'] ?>"
                    <?= ($plan['class_id'] == $cls['class_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cls['class_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Assign to Appointment</label>
        <select name="appointment_id" class="form-select">
            <option value="">-- None --</option>
            <?php while ($a = $appointments_result->fetch_assoc()): ?>
                <option value="<?= $a['appointment_id'] ?>"
                    <?= ($plan['appointment_id'] == $a['appointment_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['full_name']) ?> —
                    <?= date('M d, Y', strtotime($a['appointment_date'])) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="trainer_diet_plans.php" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-success">Update Diet Plan</button>
</div>

</form>
</div>

<script>
// Reset other selects when one is chosen
document.querySelectorAll('select[name="user_id"], select[name="class_id"], select[name="appointment_id"]').forEach(select => {
    select.addEventListener('change', function() {
        if (this.value !== "") {
            document.querySelectorAll('select[name="user_id"], select[name="class_id"], select[name="appointment_id"]').forEach(other => {
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