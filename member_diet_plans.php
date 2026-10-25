<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch diet plans assigned to this member or their classes
$query = $conn->prepare("
    SELECT 
        dp.*,
        ut.full_name as trainer_name,
        fc.class_name,
        a.appointment_date
    FROM dietary_plans dp
    LEFT JOIN Trainers t ON dp.trainer_id = t.trainer_id
    LEFT JOIN Users ut ON t.user_id = ut.user_id
    LEFT JOIN fitness_classes fc ON dp.class_id = fc.class_id
    LEFT JOIN appointments a ON dp.appointment_id = a.appointment_id
    WHERE dp.user_id = ? 
       OR dp.class_id IN (
           SELECT class_id FROM class_bookings 
           WHERE user_id = ? AND status = 'confirmed'
       )
    ORDER BY dp.created_at DESC
");
$query->bind_param("ii", $member_id, $member_id);
$query->execute();
$plans = $query->get_result();
?>

<!doctype html>
<html>
<head>
<title>My Diet Plans</title>
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
.plan-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: white;
}
.plan-card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}
.badge-calories {
    background-color: #ffc107;
    color: #000;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<h4 class="mb-4">My Diet Plans</h4>

<?php if ($plans->num_rows > 0): ?>
<div class="row">
    <?php while ($p = $plans->fetch_assoc()): ?>
    <div class="col-md-6 mb-3">
        <div class="plan-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="mb-0"><?= htmlspecialchars($p['title']) ?></h5>
                <?php if ($p['calories']): ?>
                    <span class="badge badge-calories"><?= $p['calories'] ?> kcal</span>
                <?php endif; ?>
            </div>
            
            <p class="text-muted mb-2">
                <small>By: <?= htmlspecialchars($p['trainer_name']) ?></small>
            </p>
            
            <p class="mb-2">
                <?php if ($p['class_name']): ?>
                    <span class="badge bg-info">For Class: <?= htmlspecialchars($p['class_name']) ?></span>
                <?php elseif ($p['appointment_date']): ?>
                    <span class="badge bg-success">Appointment Plan</span>
                <?php else: ?>
                    <span class="badge bg-primary">Personal Plan</span>
                <?php endif; ?>
            </p>
            
            <div class="mb-3">
                <?php 
                $preview = strip_tags($p['meal_plan']);
                echo strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                ?>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Created: <?= date("M j, Y", strtotime($p['created_at'])) ?>
                </small>
                <a href="view_diet_plan.php?id=<?= $p['diet_id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="text-center py-5">
    <p class="text-muted">No diet plans assigned to you yet.</p>
    <p class="text-muted">Contact your trainer or join a class to get a personalized diet plan.</p>
</div>
<?php endif; ?>

</div>
</body>
</html>