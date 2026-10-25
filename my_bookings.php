<?php 
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's appointments
$my_appointments = $conn->query("
    SELECT a.*, u.full_name AS trainer_name, tr.hourly_rate
    FROM Appointments a
    JOIN Trainers tr ON a.trainer_id = tr.trainer_id
    JOIN Users u ON tr.user_id = u.user_id
    WHERE a.user_id = $user_id
    ORDER BY a.appointment_date ASC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>My Bookings - FitPro</title>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">My Bookings</h1>

            <!-- Display success or error messages -->
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">My Appointments</h5>
                </div>
                <div class="card-body">
                    <?php if($my_appointments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Trainer</th>
                                        <th>Date & Time</th>
                                        <th>Duration</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($appointment = $my_appointments->fetch_assoc()): 
                                        $price = ($appointment['hourly_rate'] / 60) * $appointment['duration_minutes'];
                                        // Status badge colors
                                        $status_badge = [
                                            'pending' => 'warning',
                                            'scheduled' => 'success', 
                                            'completed' => 'info',
                                            'cancelled' => 'danger',
                                            'no_show' => 'secondary'
                                        ];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['trainer_name']); ?></td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo $appointment['duration_minutes']; ?> minutes</td>
                                        <td>
                                            <span class="badge bg-info text-capitalize">
                                                <?php echo str_replace('_', ' ', $appointment['session_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_badge[$appointment['status']] ?? 'secondary'; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($price, 2); ?></td>
                                        <td>
                                            <?php if($appointment['status'] == 'pending'): ?>
                                                <form method="POST" action="my_bookings.php" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this appointment request?');">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                    <input type="hidden" name="cancel_appointment" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">You don't have any appointments scheduled.</p>
                    <?php endif; ?>
                </div>
            </div>

            <a href="book_trainer.php" class="btn btn-outline-primary">
                <i class="fas fa-calendar-plus me-1"></i> View Available Trainers
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>