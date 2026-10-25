<?php
ob_start();
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$plan_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch workout plan with details
$query = "
    SELECT 
        wp.*,
        t.user_id as trainer_user_id,
        ut.full_name as trainer_name,
        u.full_name as member_name,
        fc.class_name,
        fc.class_type,
        a.appointment_date,
        a.session_type
    FROM workout_plans wp
    LEFT JOIN Trainers t ON wp.trainer_id = t.trainer_id
    LEFT JOIN Users ut ON t.user_id = ut.user_id
    LEFT JOIN Users u ON wp.user_id = u.user_id
    LEFT JOIN fitness_classes fc ON wp.class_id = fc.class_id
    LEFT JOIN appointments a ON wp.appointment_id = a.appointment_id
    WHERE wp.plan_id = ?
";

// Add permission check based on role
if ($role === 'member') {
    // Member can view plans assigned to them or their classes
    $query .= " AND (wp.user_id = ? OR wp.class_id IN (
        SELECT class_id FROM class_bookings 
        WHERE user_id = ? AND status = 'confirmed'
    ))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $plan_id, $user_id, $user_id);
} else {
    // Trainer or admin can view if they have permission
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $plan_id);
}

$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    die("Workout plan not found or you don't have permission to view it.");
}

// Format dates
$start_date = $plan['start_date'] ? date('F j, Y', strtotime($plan['start_date'])) : 'Not set';
$end_date = $plan['end_date'] ? date('F j, Y', strtotime($plan['end_date'])) : 'Not set';
$created_date = date('F j, Y \a\t g:i A', strtotime($plan['created_at']));
$updated_date = date('F j, Y \a\t g:i A', strtotime($plan['updated_at']));
?>

<!doctype html>
<html>
<head>
<title>Workout Plan: <?= htmlspecialchars($plan['title']) ?></title>
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
}
.info-card {
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.badge-assignment {
    font-size: 0.9em;
    padding: 6px 12px;
}
.workout-description {
    white-space: pre-line;
    line-height: 1.8;
    font-size: 1.05em;
}
.meta-info {
    font-size: 0.9em;
    color: #6c757d;
}
.duration-highlight {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 15px;
    border-radius: 4px;
}
.icon-box {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<!-- Header -->
<div class="header-section">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-2"><?= htmlspecialchars($plan['title']) ?></h3>
            <p class="mb-0 opacity-75">
                <i class="bi bi-person-circle"></i> Created by: <?= htmlspecialchars($plan['trainer_name']) ?>
            </p>
        </div>
        <?php if ($plan['class_id']): ?>
            <span class="badge bg-light text-dark badge-assignment">
                <i class="bi bi-people"></i> Class Plan
            </span>
        <?php elseif ($plan['appointment_id']): ?>
            <span class="badge bg-light text-dark badge-assignment">
                <i class="bi bi-calendar-check"></i> Appointment Plan
            </span>
        <?php else: ?>
            <span class="badge bg-light text-dark badge-assignment">
                <i class="bi bi-person"></i> Personal Plan
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- Assignment Info -->
<div class="info-card">
    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Plan Information</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="d-flex mb-3">
                <div class="icon-box">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h6 class="mb-1">Assigned To</h6>
                    <p class="mb-0">
                        <?php if ($plan['member_name']): ?>
                            <strong><?= htmlspecialchars($plan['member_name']) ?></strong>
                        <?php elseif ($plan['class_name']): ?>
                            <strong><?= htmlspecialchars($plan['class_name']) ?></strong>
                            <?php if ($plan['class_type']): ?>
                                <br><small class="text-muted">Type: <?= $plan['class_type'] ?></small>
                            <?php endif; ?>
                        <?php elseif ($plan['appointment_date']): ?>
                            <strong>Appointment</strong>
                            <br><small class="text-muted">
                                <?= date('F j, Y', strtotime($plan['appointment_date'])) ?>
                                <?php if ($plan['session_type']): ?>
                                    • <?= $plan['session_type'] ?>
                                <?php endif; ?>
                            </small>
                        <?php else: ?>
                            <span class="text-muted">Not specifically assigned</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <?php if ($plan['start_date'] && $plan['end_date']): ?>
            <div class="duration-highlight">
                <h6 class="mb-2"><i class="bi bi-calendar-range"></i> Plan Duration</h6>
                <div class="d-flex justify-content-between">
                    <div>
                        <small>Start Date</small>
                        <p class="mb-0 fw-bold"><?= $start_date ?></p>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                    <div>
                        <small>End Date</small>
                        <p class="mb-0 fw-bold"><?= $end_date ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Workout Description -->
<div class="info-card">
    <h5 class="mb-3"><i class="bi bi-list-task"></i> Workout Description</h5>
    <div class="workout-description">
        <?php if (!empty($plan['description'])): ?>
            <?= nl2br(htmlspecialchars($plan['description'])) ?>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-clipboard text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No detailed workout description provided.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Meta Information -->
<div class="info-card">
    <h5 class="mb-3"><i class="bi bi-clock-history"></i> Plan History</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-2">
                <small class="text-muted">Created On</small>
                <p class="mb-0"><?= $created_date ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <small class="text-muted">Last Updated</small>
                <p class="mb-0"><?= $updated_date ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex justify-content-between mt-4">
    <?php if ($role === 'member'): ?>
        <a href="view_workout_plans.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to My Plans
        </a>
        <div>
            <?php if ($plan['class_id']): ?>
                <a href="class_details.php?id=<?= $plan['class_id'] ?>" class="btn btn-info">
                    <i class="bi bi-people"></i> View Class
                </a>
            <?php endif; ?>
            <?php if ($plan['appointment_id']): ?>
                <a href="appointment_details.php?id=<?= $plan['appointment_id'] ?>" class="btn btn-info">
                    <i class="bi bi-calendar"></i> View Appointment
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <a href="view_workout_plans.php" class="btn btn-secondary">Back to List</a>
    <?php endif; ?>
</div>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

<script>
// Add print functionality
function printPlan() {
    window.print();
}

// Add copy link functionality
function copyPlanLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Plan link copied to clipboard!');
    });
}
</script>
</body>
</html>

<?php ob_end_flush(); ?>