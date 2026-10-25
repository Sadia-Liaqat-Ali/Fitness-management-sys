<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   ACTIVE MEMBERSHIP
========================= */
$membership = $conn->query("
    SELECT m.name, mm.end_date, mm.status
    FROM member_memberships mm
    JOIN memberships m ON mm.membership_id = m.membership_id
    WHERE mm.user_id = $user_id
    AND mm.status = 'active'
    ORDER BY mm.end_date DESC
    LIMIT 1
")->fetch_assoc();

/* =========================
   DASHBOARD COUNTS
========================= */

// Upcoming trainer appointments
$appointments_count = $conn->query("
    SELECT COUNT(*) AS c
    FROM appointments
    WHERE user_id = $user_id
    AND status IN ('pending','scheduled')
")->fetch_assoc()['c'];

// Class bookings
$class_bookings = $conn->query("
    SELECT COUNT(*) AS c
    FROM class_bookings
    WHERE user_id = $user_id
")->fetch_assoc()['c'];

// Workout plans (safe check)
$workout_plans = 0;
$check = $conn->query("SHOW TABLES LIKE 'workout_plans'");
if ($check && $check->num_rows > 0) {
    $workout_plans = $conn->query("
        SELECT COUNT(*) AS c
        FROM workout_plans
        WHERE user_id = $user_id
    ")->fetch_assoc()['c'];
}

// Payments made
$payments = $conn->query("
    SELECT COUNT(*) AS c
    FROM payments
    WHERE user_id = $user_id
    AND status = 'successful'
")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Member Dashboard | GymMaster</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6fb;
    font-family: 'Segoe UI', sans-serif;
}

.dashboard-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    padding: 2.5rem 0;
    border-radius: 0 0 35px 35px;
    margin-bottom: 2rem;
}

.stat-card {
    border-radius: 18px;
    padding: 22px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
}

.bg-appointments { background: linear-gradient(135deg,#4facfe,#00f2fe); }
.bg-classes { background: linear-gradient(135deg,#fa709a,#fee140); }
.bg-workouts { background: linear-gradient(135deg,#43e97b,#38f9d7); }
.bg-payments { background: linear-gradient(135deg,#667eea,#764ba2); }

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
}

.content-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,.1);
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="dashboard-header text-center">
    <h1>
        <i class="fas fa-user-circle me-2"></i>
        Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
    </h1>
    <p class="opacity-90">Member Dashboard Overview</p>
</div>

<div class="container pb-5">

<!-- STATS -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card bg-appointments">
            <div class="stat-number"><?php echo $appointments_count; ?></div>
            <div>Trainer Appointments</div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card bg-classes">
            <div class="stat-number"><?php echo $class_bookings; ?></div>
            <div>Class Bookings</div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card bg-workouts">
            <div class="stat-number"><?php echo $workout_plans; ?></div>
            <div>Workout Plans</div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card bg-payments">
            <div class="stat-number"><?php echo $payments; ?></div>
            <div>Payments Made</div>
        </div>
    </div>
</div>

<!-- MEMBERSHIP + QUICK LINKS -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="content-card p-4">
            <h5><i class="fas fa-id-card me-2"></i>Membership Status</h5>
            <hr>
            <?php if ($membership): ?>
                <p><strong>Plan:</strong> <?php echo $membership['name']; ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($membership['status']); ?></p>
                <p><strong>Expires:</strong> <?php echo date('d M Y', strtotime($membership['end_date'])); ?></p>
            <?php else: ?>
                <div class="alert alert-warning">
                    No active membership found.
                </div>
            <?php endif; ?>
            <a href="membership.php" class="btn btn-primary w-100 mt-2">
                Manage Membership
            </a>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="content-card p-4">
            <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            <hr>
            <div class="d-grid gap-2">
                <a href="book_trainer.php" class="btn btn-success">
                    <i class="fas fa-calendar-plus me-1"></i> Book Trainer
                </a>
                <a href="my_bookings.php" class="btn btn-info text-white">
                    <i class="fas fa-calendar-check me-1"></i> My Bookings
                </a>
                <a href="fitness_classes.php" class="btn btn-warning">
                    <i class="fas fa-users me-1"></i> View Classes
                </a>
                <a href="view_workout_plans.php" class="btn btn-secondary">
                    <i class="fas fa-dumbbell me-1"></i> Workout Plans
                </a>
                <a href="payments.php" class="btn btn-dark">
                    <i class="fas fa-credit-card me-1"></i> Payments
                </a>
            </div>
        </div>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
