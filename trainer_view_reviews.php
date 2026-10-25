<?php
include 'config.php';
include 'nav.php';

// Ensure the user is logged in as a trainer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get trainer_id
$stmt = $conn->prepare("SELECT trainer_id FROM Trainers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Trainer profile not found.");
$trainer = $res->fetch_assoc();
$trainer_id = $trainer['trainer_id'];

// Fetch all reviews
$query = $conn->prepare("
    SELECT tr.Review, tr.Rating, tr.Created_at, u.full_name AS MemberName
    FROM TrainerReviews tr
    JOIN Users u ON tr.User_id = u.user_id
    WHERE tr.Trainer_id = ?
    ORDER BY tr.Created_at DESC
");
$query->bind_param("i", $trainer_id);
$query->execute();
$reviews = $query->get_result();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Member Reviews</title>
<style>
.container-bordered {
    border: 2px solid #6c757d;
    border-radius: 10px;
    padding: 15px; 
    background-color: #f8f9fa;
    margin-bottom: 15px;
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
    <h2 class="h5">Member Reviews for You</h2>
    <?php if ($reviews->num_rows > 0): ?>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Posted At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $reviews->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['MemberName']); ?></td>
                <td><?php echo htmlspecialchars($r['Rating']); ?> / 5</td>
                <td><?php echo htmlspecialchars($r['Review']); ?></td>
                <td><?php echo date("F j, Y, g:i a", strtotime($r['Created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="text-muted">No reviews yet.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
