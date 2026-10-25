<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Book a class (AUTOMATIC - no approval needed)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_class'])) {
    $class_id = $_POST['class_id'];
    $payment_method = $_POST['payment_method'];
    
    $conn->begin_transaction();
    try {
        // Check if class exists, is active, and has space
        $class = $conn->query("
            SELECT * FROM Fitness_Classes 
            WHERE class_id = $class_id 
            AND is_active = TRUE 
            AND current_participants < max_participants
            AND class_date > NOW()
        ")->fetch_assoc();
        
        if (!$class) {
            throw new Exception("Class is full, inactive, or not found.");
        }
        
        // Check if already booked
        $existing_booking = $conn->query("
            SELECT * FROM Class_Bookings 
            WHERE class_id = $class_id AND user_id = $user_id AND status = 'confirmed'
        ")->fetch_assoc();
        
        if ($existing_booking) {
            throw new Exception("You have already booked this class.");
        }
        
        // Create booking (AUTOMATIC CONFIRMATION)
        $stmt1 = $conn->prepare("INSERT INTO Class_Bookings (class_id, user_id, status) VALUES (?, ?, 'confirmed')");
        $stmt1->bind_param("ii", $class_id, $user_id);
        $stmt1->execute();
        
        $booking_id = $conn->insert_id;
        
        // Update class participants count
        $conn->query("UPDATE Fitness_Classes SET current_participants = current_participants + 1 WHERE class_id = $class_id");
        
        // Process payment if class has price
        if ($class['price'] > 0) {
            $transaction_id = 'CLS' . time() . $user_id;
            $stmt2 = $conn->prepare("INSERT INTO Payments (user_id, amount, payment_method, service_type, service_id, transaction_id, status) VALUES (?, ?, ?, 'class_booking', ?, ?, 'successful')");
            $stmt2->bind_param("idsis", $user_id, $class['price'], $payment_method, $booking_id, $transaction_id);
            $stmt2->execute();
        }
        
        $conn->commit();
        $success = "Class booked successfully! You are now enrolled.";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Cancel class booking
if (isset($_GET['cancel_booking'])) {
    $booking_id = $_GET['cancel_booking'];
    
    $conn->begin_transaction();
    try {
        // Get booking details
        $booking = $conn->query("
            SELECT cb.*, fc.class_id 
            FROM Class_Bookings cb 
            JOIN Fitness_Classes fc ON cb.class_id = fc.class_id 
            WHERE cb.booking_id = $booking_id AND cb.user_id = $user_id
        ")->fetch_assoc();
        
        if (!$booking) {
            throw new Exception("Booking not found.");
        }
        
        // Update booking status
        $conn->query("UPDATE Class_Bookings SET status = 'cancelled' WHERE booking_id = $booking_id");
        
        // Decrease class participants count
        $conn->query("UPDATE Fitness_Classes SET current_participants = current_participants - 1 WHERE class_id = {$booking['class_id']}");
        
        $conn->commit();
        $success = "Class booking cancelled successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Get available fitness classes with seat availability
$classes = $conn->query("
    SELECT fc.*, u.full_name as trainer_name, 
           (fc.max_participants - fc.current_participants) as available_spots,
           cb.booking_id as user_booking_id,
           CASE 
               WHEN fc.current_participants >= fc.max_participants THEN 'FULL'
               WHEN fc.class_date <= NOW() THEN 'EXPIRED'
               WHEN fc.is_active = FALSE THEN 'INACTIVE'
               ELSE 'AVAILABLE'
           END as booking_status
    FROM Fitness_Classes fc
    JOIN Trainers t ON fc.trainer_id = t.trainer_id
    JOIN Users u ON t.user_id = u.user_id
    LEFT JOIN Class_Bookings cb ON fc.class_id = cb.class_id AND cb.user_id = $user_id AND cb.status = 'confirmed'
    WHERE fc.class_date > NOW() AND fc.is_active = TRUE
    ORDER BY fc.class_date ASC
");

// Get user's class bookings
$my_bookings = $conn->query("
    SELECT cb.*, fc.class_name, fc.class_date, fc.class_type, u.full_name as trainer_name
    FROM Class_Bookings cb
    JOIN Fitness_Classes fc ON cb.class_id = fc.class_id
    JOIN Trainers tr ON fc.trainer_id = tr.trainer_id
    JOIN Users u ON tr.user_id = u.user_id
    WHERE cb.user_id = $user_id AND cb.status = 'confirmed'
    ORDER BY fc.class_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Fitness Classes - FitPro</title>
    <style>
        .class-card {
            transition: transform 0.2s;
        }
        .class-card:hover {
            transform: translateY(-2px);
        }
        .status-badge-full {
            background-color: #dc3545;
        }
        .status-badge-available {
            background-color: #28a745;
        }
        .status-badge-expired {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Fitness Classes</h1>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- My Bookings -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">My Class Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if($my_bookings->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Trainer</th>
                                            <th>Date & Time</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $my_bookings->fetch_assoc()): 
                                            $can_cancel = strtotime($booking['class_date']) > time();
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($booking['class_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-info text-capitalize">
                                                    <?php echo $booking['class_type']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($can_cancel): ?>
                                                    <a href="fitness_classes.php?cancel_booking=<?php echo $booking['booking_id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                        Cancel Booking
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You haven't booked any classes yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Available Classes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Classes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while($class = $classes->fetch_assoc()): 
                                $is_full = $class['available_spots'] <= 0;
                                $is_booked = !empty($class['user_booking_id']);
                                $can_book = !$is_full && !$is_booked && $role === 'member';
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 class-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                                        <span class="badge bg-primary text-capitalize"><?php echo $class['class_type']; ?></span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($class['class_description']); ?></p>
                                        
                                        <div class="mb-2">
                                            <strong>Trainer:</strong> <?php echo htmlspecialchars($class['trainer_name']); ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Date & Time:</strong> <?php echo date('M d, Y g:i A', strtotime($class['class_date'])); ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Duration:</strong> <?php echo $class['duration_minutes']; ?> minutes
                                        </div>
                                        <div class="mb-2">
                                            <strong>Available Spots:</strong> 
                                            <span class="badge <?php echo $is_full ? 'status-badge-full' : 'status-badge-available'; ?>">
                                                <?php echo $class['available_spots']; ?> / <?php echo $class['max_participants']; ?>
                                            </span>
                                        </div>
                                        <?php if($class['price'] > 0): ?>
                                            <div class="mb-2">
                                                <strong>Price:</strong> $<?php echo number_format($class['price'], 2); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <?php if($is_booked): ?>
                                            <button class="btn btn-success w-100" disabled>
                                                <i class="fas fa-check me-2"></i>Already Booked
                                            </button>
                                        <?php elseif($is_full): ?>
                                            <button class="btn btn-danger w-100" disabled>
                                                <i class="fas fa-times me-2"></i>Class Full
                                            </button>
                                        <?php elseif($can_book): ?>
                                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#bookClassModal<?php echo $class['class_id']; ?>">
                                                <i class="fas fa-calendar-plus me-2"></i>Book Class
                                            </button>
                                        <?php elseif($role !== 'member'): ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                Members Only
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                Cannot Book
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Book Class Modal -->
                            <?php if($can_book): ?>
                            <div class="modal fade" id="bookClassModal<?php echo $class['class_id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Book: <?php echo htmlspecialchars($class['class_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="fitness_classes.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                                <input type="hidden" name="book_class" value="1">
                                                
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    <strong>Seat Available!</strong> There are <?php echo $class['available_spots']; ?> spots left.
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <strong>Class:</strong> <?php echo htmlspecialchars($class['class_name']); ?>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Trainer:</strong> <?php echo htmlspecialchars($class['trainer_name']); ?>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Date:</strong> <?php echo date('M d, Y g:i A', strtotime($class['class_date'])); ?>
                                                </div>
                                                <?php if($class['price'] > 0): ?>
                                                    <div class="mb-3">
                                                        <strong>Price:</strong> $<?php echo number_format($class['price'], 2); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
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
                                                
                                                <div class="alert alert-info">
                                                    <small>
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        You will be automatically enrolled upon payment confirmation.
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Confirm & Enroll</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endwhile; ?>
                            
                            <?php if($classes->num_rows == 0): ?>
                            <div class="col-12">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                    <p>No upcoming classes available.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>