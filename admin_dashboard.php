<?php
include 'config.php';
include 'nav.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get stats
$members_count = $conn->query("SELECT COUNT(*) as count FROM Users WHERE role='member'")->fetch_assoc()['count'];
$trainers_count = $conn->query("SELECT COUNT(*) as count FROM Users WHERE role='trainer'")->fetch_assoc()['count'];
$active_memberships = $conn->query("SELECT COUNT(*) as count FROM Member_Memberships WHERE status='active'")->fetch_assoc()['count'];

// Get revenue (example calculation)
$revenue_result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status='successful' AND MONTH(payment_date) = MONTH(CURRENT_DATE())");
$revenue = $revenue_result ? ($revenue_result->fetch_assoc()['total'] ?? 0) : 0;

// Get recent appointments
$recent_appointments = $conn->query("SELECT a.*, u.full_name FROM appointments a JOIN Users u ON a.user_id = u.user_id WHERE a.status = 'scheduled' ORDER BY a.appointment_date DESC LIMIT 5");

// Get pending payments
$pending_payments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status='pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Admin Dashboard - GymMaster</title>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Glassmorphism Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* Stats Cards */
        .stat-card {
            padding: 1.5rem;
            border-radius: 16px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 16px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .stat-trend {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }
        
        /* Quick Actions */
        .action-btn {
            padding: 1rem;
            border-radius: 12px;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: center;
            color: #4a5568;
            text-decoration: none;
            display: block;
        }
        
        .action-btn:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            color: #667eea;
            transform: translateY(-2px);
        }
        
        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.5rem;
        }
        
        .action-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .action-desc {
            font-size: 0.8rem;
            color: #718096;
        }
        
        /* Recent Activity */
        .activity-item {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            background: #f8fafc;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: #a0aec0;
        }
        
        /* Upcoming Appointments */
        .appointment-card {
            padding: 1rem;
            border-radius: 12px;
            background: white;
            border: 1px solid #e2e8f0;
            margin-bottom: 0.75rem;
        }
        
        .appointment-time {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-text {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 1.8rem;
            }
            
            .dashboard-header {
                padding: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Admin Dashboard</h1>
                    <p class="welcome-text mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-calendar-alt me-2"></i><?php echo date('F j, Y'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container dashboard-container pb-5">
        <!-- Stats Overview -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="background: var(--primary-gradient);">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $members_count; ?></div>
                    <div class="stat-label">Total Members</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up me-1"></i>12% this month
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="background: var(--secondary-gradient);">
                    <div class="stat-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="stat-number"><?php echo $trainers_count; ?></div>
                    <div class="stat-label">Active Trainers</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up me-1"></i>2 new this week
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="background: var(--info-gradient);">
                    <div class="stat-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="stat-number"><?php echo $active_memberships; ?></div>
                    <div class="stat-label">Active Memberships</div>
                    <div class="stat-trend">
                        <i class="fas fa-sync-alt me-1"></i><?php echo round($active_memberships/$members_count*100, 1); ?>% active rate
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="background: var(--success-gradient);">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number">$<?php echo number_format($revenue, 2); ?></div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="stat-trend">
                        <?php if ($pending_payments > 0): ?>
                            <i class="fas fa-clock me-1"></i><?php echo $pending_payments; ?> pending
                        <?php else: ?>
                            <i class="fas fa-check me-1"></i>All payments cleared
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="row g-4">
            <!-- Left Column: Quick Actions -->
            <div class="col-lg-8">
                <div class="glass-card p-4 mb-4">
                    <h5 class="mb-4 d-flex align-items-center">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="add_member.php" class="action-btn">
                                <div class="action-icon" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); color: #667eea;">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="action-title">Add Member</div>
                                <div class="action-desc">Register new member</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="add_trainer.php" class="action-btn">
                                <div class="action-icon" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%); color: #f5576c;">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                                <div class="action-title">Add Trainer</div>
                                <div class="action-desc">Hire new trainer</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="manage_members.php" class="action-btn">
                                <div class="action-icon" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%); color: #00f2fe;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="action-title">Manage Members</div>
                                <div class="action-desc">View all members</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="manage_trainers.php" class="action-btn">
                                <div class="action-icon" style="background: linear-gradient(135deg, rgba(67, 233, 123, 0.1) 0%, rgba(56, 249, 215, 0.1) 100%); color: #38f9d7;">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="action-title">Manage Trainers</div>
                                <div class="action-desc">Trainer schedules</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="glass-card p-4">
                    <h5 class="mb-4 d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Upcoming Appointments
                    </h5>
                    <?php if ($recent_appointments && $recent_appointments->num_rows > 0): ?>
                        <?php while($appointment = $recent_appointments->fetch_assoc()): ?>
                            <div class="appointment-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($appointment['full_name']); ?></h6>
                                        <p class="mb-0 text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?> •
                                            <?php echo $appointment['session_type']; ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-primary"><?php echo date('M d', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No upcoming appointments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

                
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
            
            // Add hover effect to quick action buttons
            const actionBtns = document.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>