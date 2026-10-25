<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_pass = $_POST["old_password"];
    $new_pass = $_POST["new_password"];
    $confirm_pass = $_POST["confirm_password"];

    if ($new_pass !== $confirm_pass) {
        $error = "New password and Confirm password do not match!";
    } else {
        $user_id = $_SESSION["user_id"];

        // Fetch current password
        $stmt = $conn->prepare("SELECT password FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!password_verify($old_pass, $result['password'])) {
            $error = "Old password is incorrect!";
        } else {
            $new_hashed = password_hash($new_pass, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
            $update->bind_param("si", $new_hashed, $user_id);

            if ($update->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Something went wrong. Try again!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password - FitPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">

            <div class="card shadow border-0">
                <div class="card-body p-5">

                    <h2 class="text-center mb-4">Change Password</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Old Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Update Password</button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="javascript:history.back()">Back</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
