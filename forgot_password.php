<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        $_SESSION['reset_user_id'] = $user['user_id'];

        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Email not found in our system!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - FitPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #4e4376, #2b5876);
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

                    <h3 class="text-center mb-4">Forgot Password</h3>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enter Your Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Continue</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none text-light">Back to Login</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
