<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];
$trainer_info = $conn->query("SELECT trainer_id FROM Trainers WHERE user_id = $trainer_id")->fetch_assoc();
$actual_trainer_id = $trainer_info['trainer_id'];


// -----------------------------------------------
// AUTO-EXPIRE ANY PASSED APPOINTMENTS
// -----------------------------------------------
$expire_sql = "
    UPDATE Appointments
    SET status = 'expired'
    WHERE trainer_id = ?
    AND appointment_date < NOW()
    AND status IN ('pending', 'scheduled')
";

$stmt_expire = $conn->prepare($expire_sql);
$stmt_expire->bind_param("i", $actual_trainer_id);
$stmt_expire->execute();
$stmt_expire->close();


// Handle appointment actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE Appointments SET status = 'scheduled' WHERE appointment_id = ? AND trainer_id = ?");
            $stmt1->bind_param("ii", $appointment_id, $actual_trainer_id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("UPDATE Payments SET status = 'successful' WHERE service_id = ? AND service_type = 'personal_training'");
            $stmt2->bind_param("i", $appointment_id);
            $stmt2->execute();
            
            $conn->commit();
            $success = "Appointment confirmed successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error confirming appointment: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['cancel_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        $reason = $_POST['cancellation_reason'];
        
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE Appointments SET status = 'cancelled', notes = CONCAT(COALESCE(notes, ''), ' [Cancelled by trainer: ', ?, ']') WHERE appointment_id = ? AND trainer_id = ?");
            $stmt1->bind_param("sii", $reason, $appointment_id, $actual_trainer_id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("UPDATE Payments SET status = 'refunded' WHERE service_id = ? AND service_type = 'personal_training'");
            $stmt2->bind_param("i", $appointment_id);
            $stmt2->execute();
            
            $conn->commit();
            $success = "Appointment cancelled and payment refunded!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error cancelling appointment: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['mark_completed'])) {
        $appointment_id = $_POST['appointment_id'];
        $session_notes = $_POST['session_notes'];

        $stmt = $conn->prepare("UPDATE Appointments SET status = 'completed', notes = CONCAT(COALESCE(notes, ''), ' Session Notes: ', ?) WHERE appointment_id = ? AND trainer_id = ?");
        $stmt->bind_param("sii", $session_notes, $appointment_id, $actual_trainer_id);
        
        if ($stmt->execute()) {
            $success = "Appointment marked as completed!";
        } else {
            $error = "Error updating appointment: " . $conn->error;
        }
    }
}

// Fetch lists again AFTER auto-expire update
$pending_appointments = $conn->query("
    SELECT a.*, u.full_name as member_name, u.email, u.phone,
           p.amount, p.payment_method
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    LEFT JOIN Payments p ON p.service_id = a.appointment_id AND p.service_type = 'personal_training'
    WHERE a.trainer_id = $actual_trainer_id AND a.status = 'pending'
    ORDER BY a.appointment_date ASC
");

$scheduled_appointments = $conn->query("
    SELECT a.*, u.full_name as member_name, u.email, u.phone,
           p.amount, p.payment_method
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    LEFT JOIN Payments p ON p.service_id = a.appointment_id AND p.service_type = 'personal_training'
    WHERE a.trainer_id = $actual_trainer_id AND a.status = 'scheduled'
    ORDER BY a.appointment_date ASC
");

$completed_appointments = $conn->query("
    SELECT a.*, u.full_name as member_name, u.email, u.phone,
           p.amount, p.payment_method
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    LEFT JOIN Payments p ON p.service_id = a.appointment_id AND p.service_type = 'personal_training'
    WHERE a.trainer_id = $actual_trainer_id 
      AND a.status IN ('completed', 'cancelled', 'no_show', 'expired')
    ORDER BY a.appointment_date DESC
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Manage Appointments - FitPro</title>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Manage Appointments</h1>

            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Pending Appointments (Require Action) -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="card-title mb-0">Pending Approval</h5>
                </div>
                <div class="card-body">
                    <?php if($pending_appointments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Date & Time</th>
                                        <th>Duration</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($appointment = $pending_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($appointment['member_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($appointment['email']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo $appointment['duration_minutes']; ?> min</td>
                                        <td class="text-capitalize"><?php echo str_replace('_', ' ', $appointment['session_type']); ?></td>
                                        <td>$<?php echo number_format($appointment['amount'], 2); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" action="trainer_appointment.php" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                    <button type="submit" name="confirm_appointment" class="btn btn-success" 
                                                            onclick="return confirm('Confirm this appointment with <?php echo htmlspecialchars($appointment['member_name']); ?>?')">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $appointment['appointment_id']; ?>">
                                                    <i class="fas fa-times"></i> Decline
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Cancel Modal -->
                                    <div class="modal fade" id="cancelModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Decline Appointment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="trainer_appointment.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                        <p>Decline appointment with <strong><?php echo htmlspecialchars($appointment['member_name']); ?></strong>?</p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason for cancellation:</label>
                                                            <textarea class="form-control" name="cancellation_reason" rows="3" required placeholder="Please provide a reason..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="cancel_appointment" class="btn btn-danger">Confirm Decline</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No pending appointments requiring approval.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Scheduled Appointments -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Scheduled Appointments</h5>
                </div>
                <div class="card-body">
                    <?php if($scheduled_appointments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Date & Time</th>
                                        <th>Duration</th>
                                        <th>Type</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($appointment = $scheduled_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($appointment['member_name']); ?></strong></td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo $appointment['duration_minutes']; ?> min</td>
                                        <td class="text-capitalize"><?php echo str_replace('_', ' ', $appointment['session_type']); ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($appointment['email']); ?></small><br>
                                            <small><?php echo htmlspecialchars($appointment['phone'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal<?php echo $appointment['appointment_id']; ?>">
                                                <i class="fas fa-check-double"></i> Complete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Complete Session Modal -->
                                    <div class="modal fade" id="completeModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Complete Session</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="trainer_appointment.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                        <p>Mark session with <strong><?php echo htmlspecialchars($appointment['member_name']); ?></strong> as completed?</p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Session Notes:</label>
                                                            <textarea class="form-control" name="session_notes" rows="3" placeholder="Progress notes, achievements, recommendations..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="mark_completed" class="btn btn-success">Mark Completed</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No scheduled appointments.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent History</h5>
                </div>
                <div class="card-body">
                    <?php if($completed_appointments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($appointment = $completed_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['member_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td class="text-capitalize"><?php echo str_replace('_', ' ', $appointment['session_type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $appointment['status'] == 'completed' ? 'success' : 
                                                     ($appointment['status'] == 'cancelled' ? 'danger' : 'secondary');
                                            ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($appointment['amount'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No appointment history.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>