<?php
include 'config.php';
include 'nav.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Admin can create classes for any trainer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_class'])) {
    $trainer_id = $_POST['trainer_id'];
    $class_name = $_POST['class_name'];
    $class_description = $_POST['class_description'];
    $class_type = $_POST['class_type'];
    $class_date = $_POST['class_date'];
    $duration_minutes = $_POST['duration_minutes'];
    $max_participants = $_POST['max_participants'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO Fitness_Classes (trainer_id, class_name, class_description, class_type, class_date, duration_minutes, max_participants, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiid", $trainer_id, $class_name, $class_description, $class_type, $class_date, $duration_minutes, $max_participants, $price);

    if ($stmt->execute()) {
        $success = "Class created successfully!";
    } else {
        $error = "Error creating class: " . $conn->error;
    }
}

// Get all classes across all trainers
$all_classes = $conn->query("
    SELECT fc.*, u.full_name as trainer_name
    FROM Fitness_Classes fc
    JOIN Trainers t ON fc.trainer_id = t.trainer_id
    JOIN Users u ON t.user_id = u.user_id
    ORDER BY fc.class_date DESC
");

// Get all active trainers for dropdown
$trainers = $conn->query("
    SELECT t.trainer_id, u.full_name 
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
    <title>Manage All Classes - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Manage All Classes (Admin)</h1>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <?php
                        $total_classes = $conn->query("SELECT COUNT(*) as count FROM Fitness_Classes WHERE class_date > NOW()")->fetch_assoc()['count'];
                        $total_participants = $conn->query("SELECT COUNT(*) as count FROM Class_Bookings WHERE status = 'confirmed'")->fetch_assoc()['count'];
                        $active_trainers = $conn->query("SELECT COUNT(*) as count FROM Trainers WHERE is_active = TRUE")->fetch_assoc()['count'];
                        ?>
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4><?php echo $total_classes; ?></h4>
                                <p>Upcoming Classes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4><?php echo $total_participants; ?></h4>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Class Form (Admin can create for any trainer) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Create New Class (Admin)</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="admin_classes.php">
                            <input type="hidden" name="create_class" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trainer *</label>
                                    <select class="form-select" name="trainer_id" required>
                                        <option value="">Select Trainer</option>
                                        <?php while($trainer = $trainers->fetch_assoc()): ?>
                                            <option value="<?php echo $trainer['trainer_id']; ?>">
                                                <?php echo htmlspecialchars($trainer['full_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class Name *</label>
                                    <input type="text" class="form-control" name="class_name" required>
                                </div>
                            </div>
                            
                            <!-- Rest of form same as trainer version -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class Type *</label>
                                    <select class="form-select" name="class_type" required>
                                        <option value="">Select Type</option>
                                        <option value="yoga">Yoga</option>
                                        <option value="cardio">Cardio</option>
                                        <option value="strength">Strength</option>
                                        <option value="hiit">HIIT</option>
                                        <option value="pilates">Pilates</option>
                                        <option value="dance">Dance</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date & Time *</label>
                                    <input type="datetime-local" class="form-control" name="class_date" required 
                                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="class_description" rows="3"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Duration (minutes) *</label>
                                    <input type="number" class="form-control" name="duration_minutes" min="15" max="180" value="60" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Participants *</label>
                                    <input type="number" class="form-control" name="max_participants" min="1" max="50" value="20" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" name="price" step="0.01" min="0" value="0">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Create Class</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- All Classes List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Classes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Trainer</th>
                                        <th>Type</th>
                                        <th>Date & Time</th>
                                        <th>Capacity</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($class = $all_classes->fetch_assoc()): 
                                        $participants_count = $conn->query("SELECT COUNT(*) as count FROM Class_Bookings WHERE class_id = {$class['class_id']} AND status = 'confirmed'")->fetch_assoc()['count'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($class['trainer_name']); ?></td>
                                        <td>
                                            <span class="badge bg-info text-capitalize">
                                                <?php echo $class['class_type']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($class['class_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $participants_count < $class['max_participants'] ? 'success' : 'danger'; ?>">
                                                <?php echo $participants_count; ?>/<?php echo $class['max_participants']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($class['price'] > 0): ?>
                                                $<?php echo number_format($class['price'], 2); ?>
                                            <?php else: ?>
                                                <span class="badge bg-success">Free</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($class['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>