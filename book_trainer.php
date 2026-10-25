<?php 
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Book appointment with trainer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $trainer_id = $_POST['trainer_id'];
    $appointment_date = $_POST['appointment_date'];
    $duration_minutes = $_POST['duration_minutes'];
    $session_type = $_POST['session_type'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];

    // Get trainer hourly rate and name
    $trainer = $conn->query("
        SELECT t.hourly_rate, u.full_name 
        FROM Trainers t 
        JOIN Users u ON t.user_id = u.user_id 
        WHERE t.trainer_id = $trainer_id
    ")->fetch_assoc();

    $amount = ($trainer['hourly_rate'] / 60) * $duration_minutes;

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("INSERT INTO Appointments (trainer_id, user_id, appointment_date, duration_minutes, session_type, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt1->bind_param("iisiss", $trainer_id, $user_id, $appointment_date, $duration_minutes, $session_type, $notes);
        $stmt1->execute();

        $appointment_id = $conn->insert_id;
        $transaction_id = 'APT' . time() . $user_id;

        $stmt2 = $conn->prepare("INSERT INTO Payments (user_id, amount, payment_method, service_type, service_id, transaction_id, status) VALUES (?, ?, ?, 'personal_training', ?, ?, 'pending')");
        $stmt2->bind_param("idsis", $user_id, $amount, $payment_method, $appointment_id, $transaction_id);
        $stmt2->execute();

        $conn->commit();
        $success = "Appointment request sent to " . $trainer['full_name'] . "! The trainer will confirm your booking within 24 hours.";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error booking appointment: " . $e->getMessage();
    }
}

// Get available trainers
$trainers = $conn->query("
    SELECT t.*, u.full_name, u.email, u.phone,
           (SELECT COUNT(*) FROM Appointments a WHERE a.trainer_id = t.trainer_id AND a.status = 'scheduled') AS pending_sessions
    FROM Trainers t
    JOIN Users u ON t.user_id = u.user_id
    WHERE t.is_active = TRUE
    ORDER BY u.full_name ASC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Book Trainer - FitPro</title>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Available Trainers</h1>

            <a href="my_bookings.php" class="btn btn-outline-primary mb-3">
                <i class="fas fa-calendar-check me-1"></i> View My Bookings
            </a>

            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <?php while($trainer = $trainers->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($trainer['full_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Specialty:</strong> <?php echo htmlspecialchars($trainer['specialty']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Hourly Rate:</strong> PKR<?php echo number_format($trainer['hourly_rate'], 2); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Shift:</strong> 
                                <?php echo date('g:i A', strtotime($trainer['shift_start'])); ?> - 
                                <?php echo date('g:i A', strtotime($trainer['shift_end'])); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Working Days:</strong> 
                                <?php echo ucwords(str_replace(',', ', ', $trainer['working_days'])); ?>
                            </div>
                            <?php if($trainer['biography']): ?>
                                <div class="mb-3">
                                    <strong>About:</strong>
                                    <p class="small text-muted"><?php echo htmlspecialchars($trainer['biography']); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    <?php echo $trainer['pending_sessions']; ?> scheduled sessions
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#bookTrainerModal<?php echo $trainer['trainer_id']; ?>">
                                Request Appointment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Book Trainer Modal -->
                <div class="modal fade" id="bookTrainerModal<?php echo $trainer['trainer_id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Request Appointment: <?php echo htmlspecialchars($trainer['full_name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="book_trainer.php">
                                <div class="modal-body">
                                    <input type="hidden" name="trainer_id" value="<?php echo $trainer['trainer_id']; ?>">
                                    <input type="hidden" name="book_appointment" value="1">
                                    
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle me-2"></i>
                                            Your appointment request will be sent to the trainer for confirmation. Payment will be processed after confirmation.
                                            You can cancel your request anytime before the trainer approves it.
                                        </small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Session Type *</label>
                                            <select class="form-select" name="session_type" required>
                                                <option value="">Select Type</option>
                                                <option value="personal_training">Personal Training</option>
                                                <option value="consultation">Consultation</option>
                                                <option value="assessment">Assessment</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Duration (minutes) *</label>
                                            <select class="form-select" name="duration_minutes" required>
                                                <option value="30">30 minutes</option>
                                                <option value="60" selected>60 minutes</option>
                                                <option value="90">90 minutes</option>
                                                <option value="120">120 minutes</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Preferred Date & Time *</label>
                                        <input type="datetime-local" class="form-control" name="appointment_date" required 
                                               min="<?php echo date('Y-m-d\TH:i'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Any specific goals or requirements..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method *</label>
                                        <select class="form-select" name="payment_method" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="electronic_transfer">Electronic Transfer</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="stripe">Stripe</option>
                                            <option value="cash">Cash</option>
                                        </select>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <small>
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Estimated cost: PKR<?php echo number_format(($trainer['hourly_rate'] / 60) * 60, 2); ?> for 60 minutes. 
                                            Payment will be authorized but only charged after trainer confirmation.
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Send Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>