<?php
ob_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$diet_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get trainer_id
$t = $conn->query("SELECT trainer_id FROM Trainers WHERE user_id = $user_id")->fetch_assoc();
$trainer_id = $t['trainer_id'];

// Delete the diet plan
$stmt = $conn->prepare("
    DELETE FROM dietary_plans
    WHERE diet_id = ? AND trainer_id = ?
");
$stmt->bind_param("ii", $diet_id, $trainer_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Diet plan deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete diet plan.";
}

header("Location: trainer_diet_plans.php");
exit();

ob_end_flush();
?>