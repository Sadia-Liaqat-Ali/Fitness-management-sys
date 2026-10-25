<?php
ob_start();
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$diet_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch diet plan with details
$query = "
    SELECT 
        dp.*,
        t.user_id as trainer_user_id,
        ut.full_name as trainer_name,
        u.full_name as member_name,
        fc.class_name,
        a.appointment_date
    FROM dietary_plans dp
    LEFT JOIN Trainers t ON dp.trainer_id = t.trainer_id
    LEFT JOIN Users ut ON t.user_id = ut.user_id
    LEFT JOIN Users u ON dp.user_id = u.user_id
    LEFT JOIN fitness_classes fc ON dp.class_id = fc.class_id
    LEFT JOIN appointments a ON dp.appointment_id = a.appointment_id
    WHERE dp.diet_id = ?
";

// Add permission check based on role
if ($role === 'trainer') {
    // Trainer can view their own plans
    $query .= " AND dp.trainer_id IN (SELECT trainer_id FROM Trainers WHERE user_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $diet_id, $user_id);
} elseif ($role === 'member') {
    // Member can view plans assigned to them or their classes
    $query .= " AND (dp.user_id = ? OR dp.class_id IN (
        SELECT class_id FROM class_bookings WHERE user_id = ? AND status = 'confirmed'
    ))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $diet_id, $user_id, $user_id);
} else {
    // Admin can view all
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $diet_id);
}

$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    die("Diet plan not found or you don't have permission to view it.");
}
?>

<!doctype html>
<html>
<head>
<title>Diet Plan: <?= htmlspecialchars($plan['title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.container-bordered {
    border: 2px solid #6c757d;
    border-radius: 10px;
    padding: 25px;
    background-color: #f8f9fa;
    max-width: 900px;
    margin: auto;
}
.header-section {
    background-color: #d1e7dd;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}
.info-box {
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
}
.calorie-badge {
    font-size: 1.2em;
    padding: 8px 15px;
}
.meal-plan-content {
    white-space: pre-line;
    line-height: 1.8;
    font-size: 1.05em;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<div class="header-section">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="mb-2"><?= htmlspecialchars($plan['title']) ?></h4>
            <p class="text-muted mb-1">Created by: <strong><?= htmlspecialchars($plan['trainer_name']) ?></strong></p>
            <p class="text-muted mb-0">Created on: <?= date('F j, Y', strtotime($plan['created_at'])) ?></p>
        </div>
        <?php if ($plan['calories']): ?>
            <div>
                <span class="badge bg-warning text-dark calorie-badge">
                    <?= $plan['calories'] ?> Calories/Day
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="info-box">
            <h6>Assigned To</h6>
            <?php if ($plan['member_name']): ?>
                <p class="mb-0">👤 <strong><?= htmlspecialchars($plan['member_name']) ?></strong></p>
            <?php elseif ($plan['class_name']): ?>
                <p class="mb-0">🏫 <strong><?= htmlspecialchars($plan['class_name']) ?></strong> (Class)</p>
            <?php elseif ($plan['appointment_date']): ?>
                <p class="mb-0">📅 <strong>Appointment</strong> on <?= date('F j, Y', strtotime($plan['appointment_date'])) ?></p>
            <?php else: ?>
                <p class="text-muted mb-0">Not specifically assigned</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box">
            <h6>Description</h6>
            <p class="mb-0"><?= nl2br(htmlspecialchars($plan['description'] ?: 'No description provided')) ?></p>
        </div>
    </div>
</div>

<div class="info-box mb-4">
    <h5 class="mb-3">📋 Meal Plan Details</h5>
    <div class="meal-plan-content">
        <?= nl2br(htmlspecialchars($plan['meal_plan'])) ?>
    </div>
</div>

<?php if ($plan['notes']): ?>
<div class="info-box mb-4">
    <h5 class="mb-3">📝 Additional Notes</h5>
    <div class="notes-content">
        <?= nl2br(htmlspecialchars($plan['notes'])) ?>
    </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between mt-4">
    <?php if ($role === 'trainer'): ?>
        <a href="trainer_diet_plans.php" class="btn btn-secondary">Back to List</a>
        <div>
            <a href="edit_diet_plan.php?id=<?= $plan['diet_id'] ?>" class="btn btn-warning">Edit Plan</a>
            <a href="delete_diet_plan.php?id=<?= $plan['diet_id'] ?>" 
               class="btn btn-danger"
               onclick="return confirm('Are you sure you want to delete this diet plan?')">Delete</a>
        </div>
    <?php else: ?>
        <a href="member_diet_plans.php" class="btn btn-secondary">Back to My Diet Plans</a>
    <?php endif; ?>
</div>

</div>
</body>
</html>

<?php ob_end_flush(); ?>