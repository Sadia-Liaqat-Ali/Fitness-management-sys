<?php
ob_start();
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$plan_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$t = $conn->query("SELECT trainer_id FROM Trainers WHERE user_id = $user_id")->fetch_assoc();
$trainer_id = $t['trainer_id'];

/* ================= FETCH PLAN ================= */
$stmt = $conn->prepare("
    SELECT * FROM workout_plans 
    WHERE plan_id = ? AND trainer_id = ?
");
$stmt->bind_param("ii", $plan_id, $trainer_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) die("Workout plan not found.");

/* ================= FETCH MEMBERS ================= */
$members = $conn->prepare("
    SELECT DISTINCT u.user_id, u.full_name
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    WHERE a.trainer_id = ?
");
$members->bind_param("i", $trainer_id);
$members->execute();
$members_result = $members->get_result();

/* ================= FETCH CLASSES ================= */
$classes = $conn->prepare("
    SELECT class_id, class_name
    FROM fitness_classes
    WHERE trainer_id = ?
");
$classes->bind_param("i", $trainer_id);
$classes->execute();
$classes_result = $classes->get_result();

/* ================= UPDATE PLAN ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_assign = !empty($_POST['user_id']) ? $_POST['user_id'] : NULL;
    $class_assign = !empty($_POST['class_id']) ? $_POST['class_id'] : NULL;
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    if ($user_assign && $class_assign) {
        die("Cannot assign to both member and class.");
    }

    $update = $conn->prepare("
        UPDATE workout_plans
        SET title=?, description=?, user_id=?, class_id=?, start_date=?, end_date=?
        WHERE plan_id=? AND trainer_id=?
    ");
    $update->bind_param(
        "ssisssii",
        $title,
        $description,
        $user_assign,
        $class_assign,
        $start,
        $end,
        $plan_id,
        $trainer_id
    );
    $update->execute();

    header("Location: trainer_workout_plans.php");
    exit();
}
?>

<!doctype html>
<html>
<head>
<title>Edit Workout Plan</title>
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
</style>
</head>

<body>
<div class="container-bordered mt-5">

<h5 class="section-title">Edit Workout Plan</h5>

<form method="POST">

<div class="mb-3">
<label class="form-label">Plan Title</label>
<input type="text" name="title" class="form-control"
       value="<?= htmlspecialchars($plan['title']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Workout Description</label>
<textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($plan['description']) ?></textarea>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Assign to Member</label>
<select name="user_id" class="form-select">
<option value="">-- None --</option>
<?php while ($m = $members_result->fetch_assoc()): ?>
<option value="<?= $m['user_id'] ?>"
<?= ($plan['user_id'] == $m['user_id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($m['full_name']) ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Assign to Class</label>
<select name="class_id" class="form-select">
<option value="">-- None --</option>
<?php while ($c = $classes_result->fetch_assoc()): ?>
<option value="<?= $c['class_id'] ?>"
<?= ($plan['class_id'] == $c['class_id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($c['class_name']) ?>
</option>
<?php endwhile; ?>
</select>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Start Date</label>
<input type="date" name="start_date" class="form-control" value="<?= $plan['start_date'] ?>">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">End Date</label>
<input type="date" name="end_date" class="form-control" value="<?= $plan['end_date'] ?>">
</div>
</div>

<div class="d-flex justify-content-between">
<a href="trainer_workout_plans.php" class="btn btn-secondary">Back</a>
<button class="btn btn-success">Update Plan</button>
</div>

</form>
</div>
</body>
</html>

<?php ob_end_flush(); ?>
