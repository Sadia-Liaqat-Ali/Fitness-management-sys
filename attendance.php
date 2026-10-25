<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get trainer's actual trainer_id
$trainer_info = $conn->query("SELECT trainer_id FROM Trainers WHERE user_id = $trainer_id")->fetch_assoc();
$actual_trainer_id = $trainer_info['trainer_id'];


// ------------------------------------------------------
// AUTO-DEACTIVATE EXPIRED CLASSES
// ------------------------------------------------------
$conn->query("
    UPDATE Fitness_Classes
    SET is_active = 0
    WHERE trainer_id = $actual_trainer_id
    AND is_active = 1
    AND DATE_ADD(class_date, INTERVAL duration_minutes MINUTE) < NOW()
");


// ------------------------------------------------------
// AUTO-MARK ABSENT (NO SHOW) FOR CLASSES
// ------------------------------------------------------
$conn->query("
    UPDATE Class_Bookings cb
    JOIN Fitness_Classes fc ON cb.class_id = fc.class_id
    SET cb.status = 'no_show'
    WHERE fc.trainer_id = $actual_trainer_id
    AND fc.is_active = 0
    AND cb.status = 'confirmed'
");


// ------------------------------------------------------
// AUTO-MARK ABSENT FOR APPOINTMENTS
// ------------------------------------------------------
$conn->query("
    UPDATE Appointments
    SET status = 'no_show'
    WHERE trainer_id = $actual_trainer_id
    AND status = 'scheduled'
    AND DATE_ADD(appointment_date, INTERVAL duration_minutes MINUTE) < NOW()
");



// ============= MANUAL MARKING =============

// Mark attendance for class manually
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_class_attendance'])) {
    $class_id = $_POST['class_id'];
    $user_id = $_POST['user_id'];
    $attendance_status = $_POST['attendance_status'];
    
    $stmt = $conn->prepare("UPDATE Class_Bookings SET status = ? WHERE class_id = ? AND user_id = ?");
    $stmt->bind_param("sii", $attendance_status, $class_id, $user_id);
    $stmt->execute();
    $success = "Attendance marked successfully!";
}

// Mark attendance for appointment manually
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_appointment_attendance'])) {
    $appointment_id = $_POST['appointment_id'];
    $attendance_status = $_POST['attendance_status'];
    
    $stmt = $conn->prepare("UPDATE Appointments SET status = ? WHERE appointment_id = ? AND trainer_id = ?");
    $stmt->bind_param("sii", $attendance_status, $appointment_id, $actual_trainer_id);
    $stmt->execute();
    $success = "Appointment attendance updated!";
}


// ============= QUERY DATA =============

// Upcoming classes (Next 7 days)
$upcoming_classes = $conn->query("
    SELECT fc.*, COUNT(cb.booking_id) AS total_bookings
    FROM Fitness_Classes fc
    LEFT JOIN Class_Bookings cb ON fc.class_id = cb.class_id AND cb.status = 'confirmed'
    WHERE fc.trainer_id = $actual_trainer_id
    AND fc.class_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
    AND fc.is_active = TRUE
    GROUP BY fc.class_id
    ORDER BY fc.class_date ASC
");

// Class participants list
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];

    $class_participants = $conn->query("
        SELECT cb.*, u.full_name, u.email
        FROM Class_Bookings cb
        JOIN Users u ON cb.user_id = u.user_id
        WHERE cb.class_id = $class_id
        ORDER BY u.full_name ASC
    ");

    $current_class = $conn->query("SELECT * FROM Fitness_Classes WHERE class_id = $class_id")->fetch_assoc();
}

// Today's appointments
$today_appointments = $conn->query("
    SELECT a.*, u.full_name, u.email
    FROM Appointments a
    JOIN Users u ON a.user_id = u.user_id
    WHERE a.trainer_id = $actual_trainer_id
    AND DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_date
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Attendance - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Attendance Management</h1>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Today's Appointments -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Today's Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if($today_appointments->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Client Name</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($appointment = $today_appointments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo $appointment['duration_minutes']; ?> minutes</td>
                                            <td class="text-capitalize"><?php echo str_replace('_', ' ', $appointment['session_type']); ?></td>
                                            <td>
                                                <span class="badge bg-warning">Scheduled</span>
                                            </td>
                                            <td>
                                                <form method="POST" action="attendance.php" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                    <input type="hidden" name="mark_appointment_attendance" value="1">
                                                    <select name="attendance_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">Mark Attendance</option>
                                                        <option value="completed">Completed</option>
                                                        <option value="no_show">No Show</option>
                                                        <option value="cancelled">Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No appointments scheduled for today.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming Classes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Upcoming Classes (Next 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_GET['class_id']) && $current_class): ?>
                            <!-- Class Participants List -->
                            <div class="mb-4">
                                <a href="attendance.php" class="btn btn-secondary mb-3">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Classes
                                </a>
                                <h5>Class: <?php echo htmlspecialchars($current_class['class_name']); ?></h5>
                                <p class="text-muted">
                                    <?php echo date('M d, Y g:i A', strtotime($current_class['class_date'])); ?> | 
                                    <?php echo $current_class['duration_minutes']; ?> minutes
                                </p>
                                
                                <?php if($class_participants->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Participant Name</th>
                                                    <th>Email</th>
                                                    <th>Booking Date</th>
                                                    <th>Attendance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($participant = $class_participants->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($participant['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($participant['booking_date'])); ?></td>
                                                    <td>
                                                        <form method="POST" action="attendance.php" class="d-inline">
                                                            <input type="hidden" name="class_id" value="<?php echo $current_class['class_id']; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $participant['user_id']; ?>">
                                                            <input type="hidden" name="mark_class_attendance" value="1">
                                                            <select name="attendance_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                                <option value="confirmed" <?php echo $participant['status'] == 'confirmed' ? 'selected' : ''; ?>>Present</option>
                                                                <option value="no_show" <?php echo $participant['status'] == 'no_show' ? 'selected' : ''; ?>>No Show</option>
                                                                <option value="cancelled" <?php echo $participant['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                <option value="completed" <?php echo $participant['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No participants booked for this class.</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Classes List -->
                            <?php if($upcoming_classes->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Class Name</th>
                                                <th>Date & Time</th>
                                                <th>Type</th>
                                                <th>Duration</th>
                                                <th>Participants</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($class = $upcoming_classes->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                                <td><?php echo date('M d, Y g:i A', strtotime($class['class_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-info text-capitalize">
                                                        <?php echo $class['class_type']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $class['duration_minutes']; ?> minutes</td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo $class['total_bookings']; ?> booked
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="attendance.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-sm btn-primary">
                                                        Take Attendance
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No upcoming classes in the next 7 days.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>