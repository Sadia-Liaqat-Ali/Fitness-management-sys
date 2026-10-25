<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Add new membership plan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_plan'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration_months = $_POST['duration_months'];

    $stmt = $conn->prepare("INSERT INTO Memberships (name, description, price, duration_months) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $description, $price, $duration_months);

    if ($stmt->execute()) {
        $success = "Membership plan added successfully!";
    } else {
        $error = "Error adding plan: " . $conn->error;
    }
}

// Update membership plan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_plan'])) {
    $membership_id = $_POST['membership_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration_months = $_POST['duration_months'];

    $stmt = $conn->prepare("UPDATE Memberships SET name=?, description=?, price=?, duration_months=? WHERE membership_id=?");
    $stmt->bind_param("ssdii", $name, $description, $price, $duration_months, $membership_id);

    if ($stmt->execute()) {
        $success = "Membership plan updated successfully!";
    } else {
        $error = "Error updating plan: " . $conn->error;
    }
}

// Delete membership plan
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM Memberships WHERE membership_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success = "Membership plan deleted successfully!";
    } else {
        $error = "Error deleting plan: " . $conn->error;
    }
}

// Get all membership plans
$plans = $conn->query("SELECT * FROM Memberships ORDER BY price ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Manage Membership Plans - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Manage Membership Plans</h1>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add New Plan Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Add New Plan</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="manage_memberships.php">
                                    <input type="hidden" name="add_plan" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Plan Name *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Price ($) *</label>
                                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Duration (Months) *</label>
                                            <input type="number" class="form-control" name="duration_months" min="1" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Add Plan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Plans -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Existing Plans</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Plan Name</th>
                                                <th>Description</th>
                                                <th>Price</th>
                                                <th>Duration</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($plan = $plans->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($plan['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($plan['description'] ?? 'No description'); ?></td>
                                                <td>$<?php echo number_format($plan['price'], 2); ?></td>
                                                <td><?php echo $plan['duration_months']; ?> months</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPlanModal<?php echo $plan['membership_id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="manage_memberships.php?delete_id=<?php echo $plan['membership_id']; ?>" 
                                                           class="btn btn-outline-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this plan?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Plan Modal -->
                                            <div class="modal fade" id="editPlanModal<?php echo $plan['membership_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Plan: <?php echo htmlspecialchars($plan['name']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="manage_memberships.php">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="membership_id" value="<?php echo $plan['membership_id']; ?>">
                                                                <input type="hidden" name="update_plan" value="1">
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Plan Name *</label>
                                                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($plan['name']); ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Description</label>
                                                                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($plan['description'] ?? ''); ?></textarea>
                                                                </div>
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Price ($) *</label>
                                                                        <input type="number" class="form-control" name="price" value="<?php echo $plan['price']; ?>" step="0.01" min="0" required>
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Duration (Months) *</label>
                                                                        <input type="number" class="form-control" name="duration_months" value="<?php echo $plan['duration_months']; ?>" min="1" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Update Plan</button>
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>