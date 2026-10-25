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

/* -----------------------------------------------------
   AUTO-DEACTIVATE CLASSES THAT HAVE ALREADY FINISHED
----------------------------------------------------- */
$conn->query("
    UPDATE Fitness_Classes
    SET is_active = 0
    WHERE trainer_id = $actual_trainer_id
    AND is_active = 1
    AND DATE_ADD(class_date, INTERVAL duration_minutes MINUTE) < NOW()
");

/* -----------------------------------------------------
   FETCH TRAINER CLASSES
----------------------------------------------------- */
$my_classes = $conn->query("
    SELECT * FROM Fitness_Classes 
    WHERE trainer_id = $actual_trainer_id 
    ORDER BY class_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>My Classes - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">My Classes</h1>

                <a href="create_class.php" class="btn btn-primary mb-3">
                    <i class="fas fa-plus me-1"></i> Create New Class
                </a>

                <!-- My Classes List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Classes List</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($my_classes->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Type</th>
                                            <th>Date & Time</th>
                                            <th>Duration</th>
                                            <th>Capacity</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($class = $my_classes->fetch_assoc()): 
                                            $participants_count = $conn->query("SELECT COUNT(*) as count FROM Class_Bookings WHERE class_id = {$class['class_id']} AND status = 'confirmed'")->fetch_assoc()['count'];
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                            <td>
                                                <span class="badge bg-info text-capitalize">
                                                    <?php echo $class['class_type']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($class['class_date'])); ?></td>
                                            <td><?php echo $class['duration_minutes']; ?> min</td>
                                            <td>
                                                <span class="badge bg-<?php echo $participants_count < $class['max_participants'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $participants_count; ?>/<?php echo $class['max_participants']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($class['price'] > 0): ?>
                                                    $<?php echo number_format($class['price'], 2); ?>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Free</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($class['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="update_class.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="attendance.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-outline-info">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You haven't created any classes yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>