<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch workout plans assigned to this member or their classes
$query = $conn->prepare("
    SELECT 
        wp.*,
        ut.full_name as trainer_name,
        fc.class_name,
        a.appointment_date,
        CASE 
            WHEN wp.class_id IS NOT NULL THEN fc.class_name
            WHEN wp.appointment_id IS NOT NULL THEN CONCAT('Appointment - ', DATE_FORMAT(a.appointment_date, '%b %d, %Y'))
            ELSE 'Personal Plan'
        END as assignment_type
    FROM workout_plans wp
    LEFT JOIN Trainers t ON wp.trainer_id = t.trainer_id
    LEFT JOIN Users ut ON t.user_id = ut.user_id
    LEFT JOIN fitness_classes fc ON wp.class_id = fc.class_id
    LEFT JOIN appointments a ON wp.appointment_id = a.appointment_id
    WHERE wp.user_id = ? 
       OR wp.class_id IN (
           SELECT class_id FROM class_bookings 
           WHERE user_id = ? AND status = 'confirmed'
       )
    ORDER BY wp.created_at DESC
");
$query->bind_param("ii", $member_id, $member_id);
$query->execute();
$plans = $query->get_result();
?>

<!doctype html>
<html>
<head>
<title>My Workout Plans</title>
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
.plan-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: white;
    transition: all 0.3s ease;
}
.plan-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.badge-type {
    font-size: 0.8em;
    padding: 5px 10px;
}
.badge-personal {
    background-color: #007bff;
}
.badge-class {
    background-color: #28a745;
}
.badge-appointment {
    background-color: #6f42c1;
}
.duration-badge {
    background-color: #17a2b8;
    color: white;
}
.trainer-info {
    font-size: 0.9em;
    color: #6c757d;
}
.description-preview {
    max-height: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-icon {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 20px;
}
</style>
</head>

<body>
<div class="container-bordered mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">My Workout Plans</h4>
    <span class="badge bg-secondary"><?= $plans->num_rows ?> Plans</span>
</div>

<?php if ($plans->num_rows > 0): ?>
<div class="row">
    <?php while ($p = $plans->fetch_assoc()): ?>
    <div class="col-lg-6 mb-4">
        <div class="plan-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($p['title']) ?></h5>
                    <p class="trainer-info mb-0">
                        <i class="bi bi-person"></i> By: <?= htmlspecialchars($p['trainer_name']) ?>
                    </p>
                </div>
                
                <?php if ($p['class_id']): ?>
                    <span class="badge badge-type badge-class">Class Plan</span>
                <?php elseif ($p['appointment_id']): ?>
                    <span class="badge badge-type badge-appointment">Appointment</span>
                <?php else: ?>
                    <span class="badge badge-type badge-personal">Personal Plan</span>
                <?php endif; ?>
            </div>
            
            <?php if ($p['class_name']): ?>
                <div class="mb-2">
                    <span class="badge bg-info">
                        <i class="bi bi-people"></i> For: <?= htmlspecialchars($p['class_name']) ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ($p['appointment_date']): ?>
                <div class="mb-2">
                    <span class="badge bg-success">
                        <i class="bi bi-calendar"></i> <?= date("M d, Y", strtotime($p['appointment_date'])) ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ($p['start_date'] && $p['end_date']): ?>
                <div class="mb-3">
                    <span class="badge duration-badge">
                        <i class="bi bi-calendar-range"></i> 
                        <?= date("M d", strtotime($p['start_date'])) ?> - <?= date("M d, Y", strtotime($p['end_date'])) ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="description-preview mb-3">
                <?php 
                $description = strip_tags($p['description']);
                if (empty($description)) {
                    echo '<span class="text-muted">No description provided</span>';
                } else {
                    echo htmlspecialchars($description);
                }
                ?>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-auto">
                <small class="text-muted">
                    <i class="bi bi-clock"></i> Created: <?= date("M j, Y", strtotime($p['created_at'])) ?>
                </small>
                <div>
                    <a href="view_single_workout_plan.php?id=<?= $p['plan_id'] ?>" 
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="empty-icon">
        <i class="bi bi-clipboard-x"></i>
    </div>
    <h5 class="text-muted mb-3">No Workout Plans Yet</h5>
    <p class="text-muted mb-4">
        You don't have any workout plans assigned to you yet.<br>
        Contact your trainer or join a class to get started!
    </p>
    <div>
        <a href="member_classes.php" class="btn btn-primary me-2">
            <i class="bi bi-people"></i> Browse Classes
        </a>
        <a href="book_trainer.php" class="btn btn-success">
            <i class="bi bi-calendar-plus"></i> Book Appointment
        </a>
    </div>
</div>
<?php endif; ?>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

<script>
// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.plan-card');
    cards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Only navigate if click wasn't on a button or link
            if (!e.target.closest('a, button')) {
                const viewLink = this.querySelector('a.btn');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
            }
        });
    });
});
</script>
</body>
</html>