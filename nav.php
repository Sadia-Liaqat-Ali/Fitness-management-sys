<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FitSync Pro | Fitness</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root{
  --gradient-nav: linear-gradient(90deg,#007bff,#ff6600);
  --light-gradient: linear-gradient(180deg,#007bff1a,#ff66001a);
}

body{
  font-family:'Poppins',sans-serif;
  background:var(--light-gradient);
}

.navbar-custom{
  background:var(--gradient-nav);
  position:sticky;
  top:0;
  z-index:1030;
  box-shadow:0 3px 10px rgba(0,0,0,.2);
}

.navbar-custom .navbar-brand{
  font-weight:700;
  font-size:1.6rem;
  color:#fff!important;
}

.navbar-custom .nav-link{
  color:#fff!important;
  font-weight:500;
  padding:8px 15px!important;
  margin:0 5px;
  border-radius:6px;
  transition:.3s;
}

.navbar-custom .nav-link:hover{
  background:rgba(255,255,255,.2);
}

.dropdown-menu{
  border-radius:10px;
  border:none;
  box-shadow:0 8px 20px rgba(0,0,0,.15);
}

.badge{
  font-size:.65rem;
}
</style>
</head>

<body>

<header>
<nav class="navbar navbar-expand-lg navbar-custom">
<div class="container">

<!-- LOGO -->
<a class="navbar-brand" href="index.php">
<?php if(isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
<i class="fas fa-dumbbell me-2"></i>GymMaster <span class="text-warning">Admin</span>
<?php else: ?>
<i class="fas fa-dumbbell me-2"></i>GymMaster
<?php endif; ?>
</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navMenu">

<!-- LEFT LINKS -->
<ul class="navbar-nav me-auto">

<?php if(isset($_SESSION['user_id'])): ?>

<?php if($_SESSION['role']=='admin'): ?>
<li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fas fa-gauge me-1"></i>Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="manage_members.php"><i class="fas fa-users me-1"></i>Members</a></li>
<li class="nav-item"><a class="nav-link" href="manage_trainers.php"><i class="fas fa-user-tie me-1"></i>Trainers</a></li>
<li class="nav-item"><a class="nav-link" href="manage_memberships.php"><i class="fas fa-id-card me-1"></i>Memberships</a></li>
<li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-line me-1"></i>Reports</a></li>

<?php elseif($_SESSION['role']=='trainer'): ?>
<li class="nav-item"><a class="nav-link" href="trainer_dashboard.php"><i class="fas fa-gauge me-1"></i>Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="trainer_appointment.php"><i class="fas fa-calendar-check me-1"></i>Appointments</a></li>
<li class="nav-item"><a class="nav-link" href="classes_list.php"><i class="fas fa-chalkboard-teacher me-1"></i>Classes</a></li>
<li class="nav-item"><a class="nav-link" href="trainer_view_reviews.php"><i class="fas fa-star me-1"></i>Reviews</a></li>
<li class="nav-item"><a class="nav-link" href="trainer_workout_plans.php"><i class="fas fa-clipboard-list me-1"></i>Workout</a></li>
<li class="nav-item"><a class="nav-link" href="attendance.php"><i class="fas fa-check-circle me-1"></i>Attendance</a></li>
<li class="nav-item"><a class="nav-link" href="trainer_diet_plans.php"><i class="fas fa-apple-alt me-1"></i>Diet</a></li>

<?php else: ?>
<li class="nav-item"><a class="nav-link" href="member_dashboard.php"><i class="fas fa-gauge me-1"></i>Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="membership.php"><i class="fas fa-id-card me-1"></i>Membership</a></li>
<li class="nav-item"><a class="nav-link" href="fitness_progress.php"><i class="fas fa-chart-line me-1"></i>Progress</a></li>
<li class="nav-item"><a class="nav-link" href="book_trainer.php"><i class="fas fa-calendar-plus me-1"></i>Book Trainer</a></li>
<li class="nav-item"><a class="nav-link" href="my_booKings.php"><i class="fas fa-calendar me-1"></i>Bookings</a></li>
<li class="nav-item"><a class="nav-link" href="member_review_trainer.php"><i class="fas fa-star me-1"></i>Review</a></li>
<li class="nav-item"><a class="nav-link" href="fitness_classes.php"><i class="fas fa-users me-1"></i>Classes</a></li>
<li class="nav-item"><a class="nav-link" href="view_workout_plans.php"><i class="fas fa-dumbbell me-1"></i>Workouts</a></li>
<li class="nav-item"><a class="nav-link" href="member_diet_plans.php"><i class="fas fa-apple-alt me-1"></i>Diet</a></li>
<?php endif; ?>

<?php endif; ?>
</ul>


<?php if(!isset($_SESSION['user_id'])): ?>
<li class="nav-item"><a class="nav-link" href="about_us.php"><i class="fas fa-info-circle me-1"></i>About</a></li>
<li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>

<?php else: ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
<i class="fas fa-user-circle me-1"></i>
<?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
<?php if($_SESSION['role']=='admin'): ?><span class="badge bg-danger ms-1">ADMIN</span><?php endif; ?>
<?php if($_SESSION['role']=='trainer'): ?><span class="badge bg-warning text-dark ms-1">TRAINER</span><?php endif; ?>
<?php if($_SESSION['role']=='member'): ?><span class="badge bg-success ms-1">MEMBER</span><?php endif; ?>
</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key me-2"></i>Change Password</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
</ul>
</li>
<?php endif; ?>
</ul>

</div>
</div>
</nav>
</header>
</body>
</html>
