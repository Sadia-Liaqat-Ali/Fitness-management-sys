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

// Get all diet plans created by this trainer
$query = $conn->prepare("
    SELECT 
        dp.*, 
        u.full_name AS member_name, 
        fc.class_name,
        a.appointment_date
    FROM dietary_plans dp
    LEFT JOIN Users u ON dp.user_id = u.user_id
    LEFT JOIN fitness_classes fc ON dp.class_id = fc.class_id
    LEFT JOIN appointments a ON dp.appointment_id = a.appointment_id
    WHERE dp.trainer_id = ?
    ORDER BY dp.created_at DESC
");
$query->bind_param("i", $trainer_id);
$query->execute();
$plans = $query->get_result();
?>

<!doctype html>
<html>
<head>
<title>Diet Plans - Trainer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.container-bordered {
    border: 2px solid #6c757d;
    border-radius: 10px;
    padding: 20px;
    background-color: #f8f9fa;
    max-width: 1300px;
    margin: auto;
}
.table thead th {
    background-color: #d1e7dd;
}
.badge-calories {
    background-color: #ffc107;
    color: #000;
}
.assigned-to {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">My Diet Plans</h4>
    <a href="add_diet_plan.php" class="btn btn-success">+ Create New Diet Plan</a>
</div>

<?php if ($plans->num_rows > 0): ?>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-light">
<tr>
    <th>Title</th>
    <th>Assigned To</th>
    <th>Calories</th>
    <th>Meal Plan Preview</th>
    <th>Created</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($p = $plans->fetch_assoc()): ?>
<tr>
    <td>
        <strong><?= htmlspecialchars($p['title']) ?></strong>
        <?php if ($p['appointment_id']): ?>
            <br><small class="text-muted">Via Appointment: <?= date('M j, Y', strtotime($p['appointment_date'])) ?></small>
        <?php endif; ?>
    </td>
    <td class="assigned-to">
        <?php if ($p['member_name']): ?>
            <span class="text-primary">👤 <?= htmlspecialchars($p['member_name']) ?></span>
        <?php elseif ($p['class_name']): ?>
            <span class="text-success">🏫 <?= htmlspecialchars($p['class_name']) ?></span>
        <?php else: ?>
            <span class="text-muted">Not Assigned</span>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($p['calories']): ?>
            <span class="badge badge-calories"><?= $p['calories'] ?> kcal</span>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td>
        <?php 
        $preview = strip_tags($p['meal_plan']);
        echo strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
        ?>
    </td>
    <td><?= date("F j, Y", strtotime($p['created_at'])) ?></td>
    <td>
        <div class="btn-group btn-group-sm" role="group">
            <a href="edit_diet_plan.php?id=<?= $p['diet_id'] ?>" class="btn btn-warning">Edit</a>
            <a href="delete_diet_plan.php?id=<?= $p['diet_id'] ?>"
               class="btn btn-danger"
               onclick="return confirm('Are you sure you want to delete this diet plan?')">Delete</a>
            <a href="view_diet_plan.php?id=<?= $p['diet_id'] ?>" class="btn btn-info">View</a>
        </div>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="text-center py-5">
    <p class="text-muted">No diet plans created yet.</p>
    <a href="add_diet_plan.php" class="btn btn-success">Create Your First Diet Plan</a>
</div>
<?php endif; ?>

</div>
</body>
</html>