<?php
ob_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$plan_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$t = $conn->query("SELECT trainer_id FROM Trainers WHERE user_id = $user_id")->fetch_assoc();
$trainer_id = $t['trainer_id'];

$stmt = $conn->prepare("
    DELETE FROM workout_plans
    WHERE plan_id = ? AND trainer_id = ?
");
$stmt->bind_param("ii", $plan_id, $trainer_id);
$stmt->execute();

header("Location: trainer_workout_plans.php");
exit();

ob_end_flush();
