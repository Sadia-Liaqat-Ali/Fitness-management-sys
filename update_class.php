<?php
include 'config.php';
session_start();

/* ===============================
   AUTH CHECK (NO OUTPUT)
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===============================
   GET TRAINER ID
================================ */
$trainer_info = $conn->query(
    "SELECT trainer_id FROM Trainers WHERE user_id = $user_id"
)->fetch_assoc();

if (!$trainer_info) {
    header("Location: classes_list.php");
    exit();
}

$actual_trainer_id = $trainer_info['trainer_id'];

/* ===============================
   VALIDATE CLASS ID
================================ */
if (!isset($_GET['class_id'])) {
    header("Location: classes_list.php");
    exit();
}

$class_id = (int) $_GET['class_id'];

/* ===============================
   FETCH CLASS
================================ */
$class_details = $conn->query("
    SELECT * FROM Fitness_Classes
    WHERE class_id = $class_id
    AND trainer_id = $actual_trainer_id
");

if ($class_details->num_rows === 0) {
    header("Location: classes_list.php");
    exit();
}

$class = $class_details->fetch_assoc();

/* ===============================
   HANDLE UPDATE
================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_class'])) {

    $class_name = $_POST['class_name'];
    $class_description = $_POST['class_description'];
    $class_type = $_POST['class_type'];
    $class_date = $_POST['class_date'];
    $duration_minutes = $_POST['duration_minutes'];
    $max_participants = $_POST['max_participants'];
    $price = $_POST['price'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $conn->prepare("
        UPDATE Fitness_Classes
        SET class_name=?,
            class_description=?,
            class_type=?,
            class_date=?,
            duration_minutes=?,
            max_participants=?,
            price=?,
            is_active=?
        WHERE class_id=?
        AND trainer_id=?
    ");

    $stmt->bind_param(
        "ssssiiidii",
        $class_name,
        $class_description,
        $class_type,
        $class_date,
        $duration_minutes,
        $max_participants,
        $price,
        $is_active,
        $class_id,
        $actual_trainer_id
    );

    if ($stmt->execute()) {
        header("Location: classes_list.php?updated=1");
        exit();
    } else {
        $error = "Error updating class.";
    }
}
?>

<?php include 'nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Class - FitPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Edit Class</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Details</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="update_class.php?class_id=<?php echo $class['class_id']; ?>">
                        <input type="hidden" name="update_class" value="1">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class Name *</label>
                                <input type="text" class="form-control" name="class_name"
                                       value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class Type *</label>
                                <select class="form-select" name="class_type" required>
                                    <option value="yoga" <?php echo $class['class_type']=='yoga'?'selected':''; ?>>Yoga</option>
                                    <option value="cardio" <?php echo $class['class_type']=='cardio'?'selected':''; ?>>Cardio</option>
                                    <option value="strength" <?php echo $class['class_type']=='strength'?'selected':''; ?>>Strength</option>
                                    <option value="hiit" <?php echo $class['class_type']=='hiit'?'selected':''; ?>>HIIT</option>
                                    <option value="pilates" <?php echo $class['class_type']=='pilates'?'selected':''; ?>>Pilates</option>
                                    <option value="dance" <?php echo $class['class_type']=='dance'?'selected':''; ?>>Dance</option>
                                    <option value="other" <?php echo $class['class_type']=='other'?'selected':''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="class_description" rows="3"><?php
                                echo htmlspecialchars($class['class_description']);
                            ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date & Time *</label>
                                <input type="datetime-local" class="form-control" name="class_date"
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($class['class_date'])); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration (minutes) *</label>
                                <input type="number" class="form-control" name="duration_minutes"
                                       value="<?php echo $class['duration_minutes']; ?>" min="15" max="180" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Participants *</label>
                                <input type="number" class="form-control" name="max_participants"
                                       value="<?php echo $class['max_participants']; ?>" min="1" max="50" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price ($)</label>
                                <input type="number" class="form-control" name="price"
                                       value="<?php echo $class['price']; ?>" step="0.01" min="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           <?php echo $class['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Active Class</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Update Class
                            </button>
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
