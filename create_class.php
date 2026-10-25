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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Create New Class - FitPro</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Create New Class</h1>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Create New Class Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Class Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="create_class.php">
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
                                    <input type="number" class="form-control" name="price" step="0.01" min="0" 
                                           value="0" placeholder="0 for free classes">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Create Class</button>
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