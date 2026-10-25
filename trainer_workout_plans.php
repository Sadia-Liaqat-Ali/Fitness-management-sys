<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT trainer_id FROM Trainers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Trainer profile not found.");
$trainer = $res->fetch_assoc();
$trainer_id = $trainer['trainer_id'];

$query = $conn->prepare("
    SELECT wp.*, u.full_name AS member_name, fc.class_name
    FROM workout_plans wp
    LEFT JOIN Users u ON wp.user_id = u.user_id
    LEFT JOIN fitness_classes fc ON wp.class_id = fc.class_id
    WHERE wp.trainer_id = ?
    ORDER BY wp.created_at DESC
");
$query->bind_param("i", $trainer_id);
$query->execute();
$plans = $query->get_result();
?>

<!doctype html>
<html>
<head>
<title>Workout Plans</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.container-bordered {
    border: 2px solid #6c757d;
    border-radius: 10px;
    padding: 20px;
    background-color: #f8f9fa;
    max-width: 1200px;
    margin: auto;
}
.table thead th {
    background-color: #d1e7dd;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Workout Plans</h4>
    <a href="add_workout_plan.php" class="btn btn-success">+ Create Workout Plan</a>
</div>

<?php if ($plans->num_rows > 0): ?>
<table class="table table-bordered">
<thead>
<tr>
    <th>Title</th>
    <th>Assigned To</th>
    <th>Duration</th>
    <th>Created</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($p = $plans->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($p['title']) ?></td>
    <td>
        <?= $p['member_name'] ?? $p['class_name'] ?? '<span class="text-muted">Not Assigned</span>' ?>
    </td>
    <td>
        <?= $p['start_date'] ?> → <?= $p['end_date'] ?>
    </td>
    <td><?= date("F j, Y", strtotime($p['created_at'])) ?></td>
    <td>
        <a href="edit_workout_plan.php?id=<?= $p['plan_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
        <a href="delete_workout_plan.php?id=<?= $p['plan_id'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete this workout plan?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<p class="text-muted">No workout plans created yet.</p>
<?php endif; ?>

</div>
</body>
</html>
