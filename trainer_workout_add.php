<?php
include 'config.php'; // database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $trainer_id = $_POST['trainer_id'];
    $user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : NULL;
    $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : NULL;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO workout_plans 
        (trainer_id, user_id, class_id, title, description, start_date, end_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $trainer_id, $user_id, $class_id, $title, $description, $start_date, $end_date);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Workout plan added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<form method="POST">
    <label>Trainer ID:</label><br>
    <input type="number" name="trainer_id" required><br>

    <label>User ID (optional):</label><br>
    <input type="number" name="user_id"><br>

    <label>Class ID (optional):</label><br>
    <input type="number" name="class_id"><br>

    <label>Title:</label><br>
    <input type="text" name="title" required><br>

    <label>Description:</label><br>
    <textarea name="description"></textarea><br>

    <label>Start Date:</label><br>
    <input type="date" name="start_date"><br>

    <label>End Date:</label><br>
    <input type="date" name="end_date"><br>

    <button type="submit">Add Workout Plan</button>
</form>
