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
   FETCH SCHEDULED APPOINTMENTS (NO WORKOUT PLAN)
   ===================================================== */
$appointments = $conn->prepare("
    SELECT 
        a.appointment_id,
        a.appointment_date,
        u.full_name
    FROM appointments a
    JOIN users u ON u.user_id = a.user_id
    LEFT JOIN workout_plans wp ON wp.appointment_id = a.appointment_id
    WHERE a.trainer_id = ?
      AND a.status = 'scheduled'
      AND a.appointment_date >= NOW()
      AND wp.plan_id IS NULL
    ORDER BY a.appointment_date ASC
");
$appointments->bind_param("i", $trainer_id);
$appointments->execute();
$appointments_result = $appointments->get_result();

/* =====================================================
   FETCH CONFIRMED CLASSES (NO WORKOUT PLAN, NOT PAST)
   ===================================================== */
$classes = $conn->prepare("
    SELECT 
        fc.class_id,
        fc.class_name,
        fc.class_date
    FROM fitness_classes fc
    LEFT JOIN workout_plans wp ON wp.class_id = fc.class_id
    WHERE fc.trainer_id = ?
      AND fc.is_active = 1
      AND fc.class_date >= NOW()
      AND wp.plan_id IS NULL
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
   HANDLE FORM SUBMISSION
   ===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $appointment_id = !empty($_POST['appointment_id']) ? $_POST['appointment_id'] : NULL;
    $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : NULL;

    if (($appointment_id && $class_id) || (!$appointment_id && !$class_id)) {
        die("Select EITHER an Appointment OR a Class.");
    }

    $stmt = $conn->prepare("
        INSERT INTO workout_plans
        (trainer_id, appointment_id, class_id, title, description)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiiss",
        $trainer_id,
        $appointment_id,
        $class_id,
        $title,
        $description
    );
    $stmt->execute();

    header("Location: trainer_workout_plans.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
<title>Create Workout Plan</title>
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
</style>
</head>

<body>
<div class="container-bordered mt-5">

<h5 class="section-title">Create Workout Plan</h5>

<form method="POST">

<!-- TITLE -->
<div class="mb-3">
<label class="form-label">Workout Plan Title</label>
<input type="text" name="title" class="form-control" required>
</div>

<!-- DESCRIPTION -->
<div class="mb-3">
<label class="form-label">Workout Description</label>
<textarea name="description" class="form-control" rows="4"></textarea>
</div>

<div class="row">

<!-- APPOINTMENTS -->
<div class="col-md-6 mb-3">
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
Only scheduled appointments without a workout plan
</span>
</div>

<!-- CLASSES -->
<div class="col-md-6 mb-3">
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
Only confirmed & upcoming classes without a workout plan
</span>
</div>

</div>

<!-- ACTIONS -->
<div class="d-flex justify-content-between mt-4">
<a href="trainer_workout_plans.php" class="btn btn-secondary">Back</a>
<button type="submit" class="btn btn-success">Save Workout Plan</button>
</div>

</form>
</div>
</body>
</html>

<?php ob_end_flush(); ?>
