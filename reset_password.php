<?php
session_start();
include 'config.php';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match!";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $user_id = $_SESSION['reset_user_id'];

        $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
            $success = "Password reset successful!";
            unset($_SESSION['reset_user_id']);
        } else {
            $error = "Something went wrong!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - FitPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #2b5876, #4e4376);
        }
        .card {
            border-radius: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">

            <div class="card shadow-lg">
                <div class="card-body p-5">

                    <h3 class="text-center mb-4">Reset Password</h3>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <a href="login.php" class="btn btn-success w-100 mt-3">Login Now</a>
                    <?php else: ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Reset Password</button>
                    </form>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
