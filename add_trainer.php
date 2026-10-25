<?php
include 'config.php';
include 'nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add to Users table
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $role = 'trainer';

    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO Users (username, password, role, full_name, email, phone, date_of_birth, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $username, $password, $role, $full_name, $email, $phone, $date_of_birth, $gender, $address);
        $stmt->execute();
        
        $user_id = $conn->insert_id;
        
        // Add to Trainers table
        $specialty = $_POST['specialty'];
        $biography = $_POST['biography'];
        $shift_start = $_POST['shift_start'];
        $shift_end = $_POST['shift_end'];
        $working_days = implode(',', $_POST['working_days'] ?? []);
        $hourly_rate = $_POST['hourly_rate'];
        
        $stmt2 = $conn->prepare("INSERT INTO Trainers (user_id, specialty, biography, shift_start, shift_end, working_days, hourly_rate) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssssd", $user_id, $specialty, $biography, $shift_start, $shift_end, $working_days, $hourly_rate);
        $stmt2->execute();
        
        $conn->commit();
        $success = "Trainer added successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error adding trainer: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Trainer - FitPro</title>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Add New Trainer</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="add_trainer.php">
                            <h5 class="mb-3">Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>

                            <hr>
                            <h5 class="mb-3">Professional Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="specialty" class="form-label">Specialty *</label>
                                    <input type="text" class="form-control" id="specialty" name="specialty" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hourly_rate" class="form-label">Hourly Rate ($) *</label>
                                    <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" step="0.01" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="biography" class="form-label">Biography</label>
                                <textarea class="form-control" id="biography" name="biography" rows="3"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shift_start" class="form-label">Shift Start *</label>
                                    <input type="time" class="form-control" id="shift_start" name="shift_start" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shift_end" class="form-label">Shift End *</label>
                                    <input type="time" class="form-control" id="shift_end" name="shift_end" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Working Days *</label>
                                <div class="row">
                                    <?php
                                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    foreach($days as $day): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="working_days[]" value="<?php echo $day; ?>" id="<?php echo $day; ?>">
                                                <label class="form-check-label text-capitalize" for="<?php echo $day; ?>">
                                                    <?php echo ucfirst($day); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Add Trainer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>