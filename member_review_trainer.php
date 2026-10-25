<?php
include 'config.php';
include 'nav.php';

// Ensure the user is logged in as a member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle new review submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $appointment_id = $_POST['appointment_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    // Fetch the trainer_id from the appointment
    $query = $conn->prepare("SELECT trainer_id FROM Appointments WHERE appointment_id = ? AND status = 'completed'");
    $query->bind_param('i', $appointment_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $trainer = $result->fetch_assoc();
        $trainer_id = $trainer['trainer_id'];

        // Insert review
        $stmt = $conn->prepare("INSERT INTO TrainerReviews (Appointment_id, Trainer_id, User_id, Rating, Review) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $appointment_id, $trainer_id, $user_id, $rating, $review_text);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Review submitted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Appointment not found or not completed.</div>";
    }
    $query->close();
}

// Handle review deletion
if (isset($_GET['delete_review_id'])) {
    $review_id = $_GET['delete_review_id'];
    $del = $conn->prepare("DELETE FROM TrainerReviews WHERE Review_id = ?");
    $del->bind_param("i", $review_id);
    if ($del->execute()) {
        echo "<div class='alert alert-success'>Review deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting review.</div>";
    }
    $del->close();
}

// Handle review editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_review'])) {
    $review_id = $_POST['review_id'];
    $updated_review_text = $_POST['updated_review_text'];

    $update = $conn->prepare("UPDATE TrainerReviews SET Review = ? WHERE Review_id = ?");
    $update->bind_param("si", $updated_review_text, $review_id);
    if ($update->execute()) {
        echo "<div class='alert alert-success'>Review updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating review.</div>";
    }
    $update->close();
}

// Fetch completed appointments not yet reviewed
$completed_stmt = $conn->prepare("
    SELECT a.appointment_id, u.full_name AS TrainerName, a.appointment_date
    FROM Appointments a
    JOIN Trainers t ON a.trainer_id = t.trainer_id
    JOIN Users u ON t.user_id = u.user_id
    WHERE a.user_id = ? AND a.status = 'completed'
    AND a.appointment_id NOT IN (SELECT Appointment_id FROM TrainerReviews WHERE User_id = ?)
");
$completed_stmt->bind_param('ii', $user_id, $user_id);
$completed_stmt->execute();
$completed_appointments = $completed_stmt->get_result();

// Fetch existing reviews
$reviews_stmt = $conn->prepare("
    SELECT tr.Review_id, tr.Rating, tr.Review, tr.Created_at, u.full_name AS TrainerName
    FROM TrainerReviews tr
    JOIN Trainers t ON tr.Trainer_id = t.trainer_id
    JOIN Users u ON t.user_id = u.user_id
    WHERE tr.User_id = ?
    ORDER BY tr.Created_at DESC
");
$reviews_stmt->bind_param('i', $user_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Trainer Reviews</title>
<style>
.container-bordered {
    border: 2px solid #6c757d; 
    border-radius: 10px;
    padding: 15px;
    background-color: #f8f9fa;
    margin-bottom: 20px;
    max-width: 1200px;
    margin-left: auto; 
    margin-right: auto;
}
.table thead th {
    background-color: #d1e7dd;
    color: #212529;
}
.table tbody tr:nth-child(even) {
    background-color: #f2f2f2; 
}
</style>
</head>
<body>

<div class="container-bordered mt-5">
    <h2 class="h5">Write a Review for Your Trainer</h2>
    <form method="POST">
        <div class="form-group mb-3">
            <label for="appointment_id">Select Completed Appointment:</label>
            <select id="appointment_id" name="appointment_id" class="form-control" required>
                <option value="">Select an appointment</option>
                <?php while ($row = $completed_appointments->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['appointment_id']); ?>">
                        Appointment ID: <?php echo htmlspecialchars($row['appointment_id']); ?> — Trainer: <?php echo htmlspecialchars($row['TrainerName']); ?> (<?php echo date("F j, Y", strtotime($row['appointment_date'])); ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="rating">Rating:</label>
            <select id="rating" name="rating" class="form-control" required>
                <option value="">Select a rating</option>
                <option value="1">1 Star</option>
                <option value="2">2 Stars</option>
                <option value="3">3 Stars</option>
                <option value="4">4 Stars</option>
                <option value="5">5 Stars</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="review_text">Review:</label>
            <textarea id="review_text" name="review_text" class="form-control" required></textarea>
        </div>
        <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Review</button>
    </form>
</div>

<div class="container-bordered mt-4">
    <h2 class="h5">Your Submitted Reviews</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Trainer</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Posted At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($review['TrainerName']); ?></td>
                <td><?php echo htmlspecialchars($review['Rating']); ?> / 5</td>
                <td><?php echo htmlspecialchars($review['Review']); ?></td>
                <td><?php echo date("F j, Y, g:i a", strtotime($review['Created_at'])); ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal_<?php echo $review['Review_id']; ?>">Edit</button>
                    <a href="?delete_review_id=<?php echo $review['Review_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this review?');">Delete</a>
                </td>
            </tr>

            <!-- Edit Review Modal -->
            <div class="modal fade" id="editModal_<?php echo $review['Review_id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Review</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="review_id" value="<?php echo $review['Review_id']; ?>">
                                <textarea name="updated_review_text" class="form-control" required><?php echo htmlspecialchars($review['Review']); ?></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_review" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
