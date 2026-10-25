<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   GET TRAINER ID
========================= */
$trainer = $conn->query("
    SELECT trainer_id 
    FROM trainers 
    WHERE user_id = $user_id
")->fetch_assoc();

$trainer_id = $trainer['trainer_id'];

/* =========================
   DASHBOARD COUNTS
========================= */

// Active clients (members who have appointments with this trainer)
$clients_count = $conn->query("
    SELECT COUNT(DISTINCT user_id) AS c
    FROM appointments
    WHERE trainer_id = $trainer_id
")->fetch_assoc()['c'] ?? 0;

// Sessions today
$sessions_today = $conn->query("
    SELECT COUNT(*) AS c
    FROM appointments
    WHERE trainer_id = $trainer_id
    AND DATE(appointment_date) = CURDATE()
    AND status IN ('scheduled','completed')
")->fetch_assoc()['c'] ?? 0;

// Total sessions
$total_sessions = $conn->query("
    SELECT COUNT(*) AS c
    FROM appointments
    WHERE trainer_id = $trainer_id
")->fetch_assoc()['c'] ?? 0;

// Average rating from trainerreviews table
$rating = $conn->query("
    SELECT ROUND(AVG(rating),1) AS avg_rating
    FROM trainerreviews
    WHERE trainer_id = $trainer_id
")->fetch_assoc()['avg_rating'] ?? 0;


// Diet plans created
$diet_plans = $conn->query("
    SELECT COUNT(*) AS c
    FROM dietary_plans
    WHERE trainer_id = $trainer_id
")->fetch_assoc()['c'] ?? 0;

// Upcoming sessions
$upcoming_sessions = $conn->query("
    SELECT a.appointment_date, u.full_name, a.session_type
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    WHERE a.trainer_id = $trainer_id
    AND a.status = 'scheduled'
    ORDER BY a.appointment_date
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Trainer Dashboard | GymMaster</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6fb;
    font-family: 'Segoe UI', sans-serif;
}

.stat-card {
    border-radius: 18px;
    padding: 22px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
}

.bg-clients { background: linear-gradient(135deg,#667eea,#764ba2); }
.bg-sessions { background: linear-gradient(135deg,#4facfe,#00f2fe); }
.bg-rating { background: linear-gradient(135deg,#fa709a,#fee140); }
.bg-diet { background: linear-gradient(135deg,#43e97b,#38f9d7); }

.stat-number {
    font-size: 2.3rem;
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

<div class="container py-4">

<h2 class="mb-4">
    <i class="fas fa-dumbbell me-2"></i>Trainer Dashboard
</h2>

<!-- STATS -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card bg-clients">
            <div class="stat-number"><?php echo $clients_count; ?></div>
            <div>Active Clients</div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card bg-sessions">
            <div class="stat-number"><?php echo $sessions_today; ?></div>
            <div>Sessions Today</div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card bg-rating">
            <div class="stat-number"><?php echo $rating ?: 'N/A'; ?></div>
            <div>Average Rating</div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card bg-diet">
            <div class="stat-number"><?php echo $diet_plans; ?></div>
            <div>Diet Plans Created</div>
        </div>
    </div>
</div>

<!-- QUICK ACTIONS + SCHEDULE -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="content-card p-4">
            <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            <hr>
            <div class="d-grid gap-2">
                <a href="workout_plans.php" class="btn btn-primary">
                    <i class="fas fa-running me-1"></i> Workout Plans
                </a>
                <a href="dietary_plans.php" class="btn btn-success">
                    <i class="fas fa-utensils me-1"></i> Diet Plans
                </a>
                <a href="attendance.php" class="btn btn-warning">
                    <i class="fas fa-clipboard-check me-1"></i> Attendance
                </a>
                <a href="my_schedule.php" class="btn btn-dark">
                    <i class="fas fa-calendar me-1"></i> My Schedule
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="content-card p-4">
            <h5><i class="fas fa-calendar-alt me-2"></i>Upcoming Sessions</h5>
            <hr>

            <?php if ($upcoming_sessions->num_rows > 0): ?>
                <?php while ($row = $upcoming_sessions->fetch_assoc()): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>
                            <?php echo htmlspecialchars($row['full_name']); ?>
                            <small class="text-muted">(<?php echo $row['session_type']; ?>)</small>
                        </span>
                        <span class="badge bg-primary">
                            <?php echo date('d M, h:i A', strtotime($row['appointment_date'])); ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    No upcoming sessions.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
