<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Delete trainer
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $conn->begin_transaction();
    try {
        // First delete from Trainers table
        $stmt1 = $conn->prepare("DELETE FROM Trainers WHERE user_id = ?");
        $stmt1->bind_param("i", $delete_id);
        $stmt1->execute();
        
        // Then delete from Users table
        $stmt2 = $conn->prepare("DELETE FROM Users WHERE user_id = ? AND role = 'trainer'");
        $stmt2->bind_param("i", $delete_id);
        $stmt2->execute();
        
        $conn->commit();
        $success = "Trainer deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting trainer: " . $e->getMessage();
    }
}

// Update trainer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_trainer'])) {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialty = $_POST['specialty'];
    $biography = $_POST['biography'];
    $shift_start = $_POST['shift_start'];
    $shift_end = $_POST['shift_end'];
    $working_days = implode(',', $_POST['working_days'] ?? []);
    $hourly_rate = $_POST['hourly_rate'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $conn->begin_transaction();
    try {
        // Update Users table
        $stmt1 = $conn->prepare("UPDATE Users SET full_name=?, email=?, phone=? WHERE user_id=?");
        $stmt1->bind_param("sssi", $full_name, $email, $phone, $user_id);
        $stmt1->execute();
        
        // Update Trainers table
        $stmt2 = $conn->prepare("UPDATE Trainers SET specialty=?, biography=?, shift_start=?, shift_end=?, working_days=?, hourly_rate=?, is_active=? WHERE user_id=?");
        $stmt2->bind_param("sssssdii", $specialty, $biography, $shift_start, $shift_end, $working_days, $hourly_rate, $is_active, $user_id);
        $stmt2->execute();
        
        $conn->commit();
        $success = "Trainer updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating trainer: " . $e->getMessage();
    }
}

// Get all trainers with their details
$trainers = $conn->query("
    SELECT u.*, t.* 
    FROM Users u 
    JOIN Trainers t ON u.user_id = t.user_id 
    WHERE u.role = 'trainer'
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Manage Trainers - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Manage Trainers</h1>
                    <a href="add_trainer.php" class="btn btn-primary">
                        <i class="fas fa-dumbbell me-2"></i>Add New Trainer
                    </a>
                </div>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Specialty</th>
                                        <th>Hourly Rate</th>
                                        <th>Shift</th>
                                        <th>Status</th>
                                        <th>Join Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($trainer = $trainers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $trainer['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($trainer['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($trainer['specialty']); ?></span>
                                        </td>
                                        <td>$<?php echo $trainer['hourly_rate']; ?>/hr</td>
                                        <td>
                                            <?php echo date('g:i A', strtotime($trainer['shift_start'])); ?> - 
                                            <?php echo date('g:i A', strtotime($trainer['shift_end'])); ?>
                                        </td>
                                        <td>
                                            <?php if($trainer['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($trainer['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTrainerModal<?php echo $trainer['user_id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="manage_trainers.php?delete_id=<?php echo $trainer['user_id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this trainer?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Trainer Modal -->
                                    <div class="modal fade" id="editTrainerModal<?php echo $trainer['user_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Trainer: <?php echo htmlspecialchars($trainer['full_name']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="manage_trainers.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?php echo $trainer['user_id']; ?>">
                                                        <input type="hidden" name="update_trainer" value="1">
                                                        
                                                        <h6 class="mb-3">Personal Information</h6>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Full Name *</label>
                                                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($trainer['full_name']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Email *</label>
                                                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($trainer['email']); ?>" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Phone</label>
                                                                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($trainer['phone'] ?? ''); ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <div class="form-check form-switch mt-4">
                                                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active<?php echo $trainer['user_id']; ?>" <?php echo $trainer['is_active'] ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="is_active<?php echo $trainer['user_id']; ?>">Active Trainer</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <hr>
                                                        <h6 class="mb-3">Professional Information</h6>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Specialty *</label>
                                                                <input type="text" class="form-control" name="specialty" value="<?php echo htmlspecialchars($trainer['specialty']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Hourly Rate ($) *</label>
                                                                <input type="number" class="form-control" name="hourly_rate" value="<?php echo $trainer['hourly_rate']; ?>" step="0.01" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Biography</label>
                                                            <textarea class="form-control" name="biography" rows="3"><?php echo htmlspecialchars($trainer['biography'] ?? ''); ?></textarea>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Shift Start *</label>
                                                                <input type="time" class="form-control" name="shift_start" value="<?php echo $trainer['shift_start']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Shift End *</label>
                                                                <input type="time" class="form-control" name="shift_end" value="<?php echo $trainer['shift_end']; ?>" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Working Days *</label>
                                                            <div class="row">
                                                                <?php
                                                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                                                $working_days = explode(',', $trainer['working_days']);
                                                                foreach($days as $day): ?>
                                                                    <div class="col-md-4 mb-2">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="<?php echo $day; ?>" id="<?php echo $day . $trainer['user_id']; ?>" 
                                                                                <?php echo in_array($day, $working_days) ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label text-capitalize" for="<?php echo $day . $trainer['user_id']; ?>">
                                                                                <?php echo ucfirst($day); ?>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Update Trainer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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