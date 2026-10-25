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
   CREATE NEW CLASS
----------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_class'])) {
    $class_name = $_POST['class_name'];
    $class_description = $_POST['class_description'];
    $class_type = $_POST['class_type'];
    $class_date = $_POST['class_date'];
    $duration_minutes = $_POST['duration_minutes'];
    $max_participants = $_POST['max_participants'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO Fitness_Classes (trainer_id, class_name, class_description, class_type, class_date, duration_minutes, max_participants, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiid", $actual_trainer_id, $class_name, $class_description, $class_type, $class_date, $duration_minutes, $max_participants, $price);

    if ($stmt->execute()) {
        $success = "Class created successfully!";
    } else {
        $error = "Error creating class: " . $conn->error;
    }
}

/* -----------------------------------------------------
   UPDATE CLASS
----------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_class'])) {
    $class_id = $_POST['class_id'];
    $class_name = $_POST['class_name'];
    $class_description = $_POST['class_description'];
    $class_type = $_POST['class_type'];
    $class_date = $_POST['class_date'];
    $duration_minutes = $_POST['duration_minutes'];
    $max_participants = $_POST['max_participants'];
    $price = $_POST['price'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE Fitness_Classes SET class_name=?, class_description=?, class_type=?, class_date=?, duration_minutes=?, max_participants=?, price=?, is_active=? WHERE class_id=? AND trainer_id=?");
    $stmt->bind_param("ssssiiidii", $class_name, $class_description, $class_type, $class_date, $duration_minutes, $max_participants, $price, $is_active, $class_id, $actual_trainer_id);

    if ($stmt->execute()) {
        $success = "Class updated successfully!";
    } else {
        $error = "Error updating class: " . $conn->error;
    }
}

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
    <title>Manage My Classes - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Manage My Classes</h1>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Create New Class Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Create New Class</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="manage_classes.php">
                            <input type="hidden" name="create_class" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class Name *</label>
                                    <input type="text" class="form-control" name="class_name" required>
                                </div>
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
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="class_description" rows="3" placeholder="Describe the class focus, intensity level, etc."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date & Time *</label>
                                    <input type="datetime-local" class="form-control" name="class_date" required 
                                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Duration (minutes) *</label>
                                    <input type="number" class="form-control" name="duration_minutes" min="15" max="180" value="60" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Participants *</label>
                                    <input type="number" class="form-control" name="max_participants" min="1" max="50" value="20" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" name="price" step="0.01" min="0" value="0" 
                                           placeholder="0 for free classes">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Create Class</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- My Classes List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">My Classes</h5>
                    </div>
                    <div class="card-body">
                        <?php if($my_classes->num_rows > 0): ?>
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
                                        <?php while($class = $my_classes->fetch_assoc()): 
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
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editClassModal<?php echo $class['class_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="attendance.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-outline-info">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Edit Class Modal -->
                                        <div class="modal fade" id="editClassModal<?php echo $class['class_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Class: <?php echo htmlspecialchars($class['class_name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="manage_classes.php">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                                            <input type="hidden" name="update_class" value="1">
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Class Name *</label>
                                                                    <input type="text" class="form-control" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Class Type *</label>
                                                                    <select class="form-select" name="class_type" required>
                                                                        <option value="yoga" <?php echo $class['class_type'] == 'yoga' ? 'selected' : ''; ?>>Yoga</option>
                                                                        <option value="cardio" <?php echo $class['class_type'] == 'cardio' ? 'selected' : ''; ?>>Cardio</option>
                                                                        <option value="strength" <?php echo $class['class_type'] == 'strength' ? 'selected' : ''; ?>>Strength</option>
                                                                        <option value="hiit" <?php echo $class['class_type'] == 'hiit' ? 'selected' : ''; ?>>HIIT</option>
                                                                        <option value="pilates" <?php echo $class['class_type'] == 'pilates' ? 'selected' : ''; ?>>Pilates</option>
                                                                        <option value="dance" <?php echo $class['class_type'] == 'dance' ? 'selected' : ''; ?>>Dance</option>
                                                                        <option value="other" <?php echo $class['class_type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="class_description" rows="3"><?php echo htmlspecialchars($class['class_description'] ?? ''); ?></textarea>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-4 mb-3">
                                                                    <label class="form-label">Date & Time *</label>
                                                                    <input type="datetime-local" class="form-control" name="class_date" 
                                                                           value="<?php echo date('Y-m-d\TH:i', strtotime($class['class_date'])); ?>" required>
                                                                </div>
                                                                <div class="col-md-4 mb-3">
                                                                    <label class="form-label">Duration (minutes) *</label>
                                                                    <input type="number" class="form-control" name="duration_minutes" value="<?php echo $class['duration_minutes']; ?>" min="15" max="180" required>
                                                                </div>
                                                                <div class="col-md-4 mb-3">
                                                                    <label class="form-label">Max Participants *</label>
                                                                    <input type="number" class="form-control" name="max_participants" value="<?php echo $class['max_participants']; ?>" min="1" max="50" required>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Price ($)</label>
                                                                    <input type="number" class="form-control" name="price" value="<?php echo $class['price']; ?>" step="0.01" min="0">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <div class="form-check form-switch mt-4">
                                                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active<?php echo $class['class_id']; ?>" <?php echo $class['is_active'] ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label" for="is_active<?php echo $class['class_id']; ?>">Active Class</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Update Class</button>
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
