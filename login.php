<?php
include 'config.php';
session_start();

/* ===== BACKEND (UNCHANGED) ===== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['username']  = $user['username'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] === 'trainer') {
                    header("Location: trainer_dashboard.php");
                } else {
                    header("Location: member_dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Fitness Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(to right,#eef5ff,#fff4ec);
}

.container{
    width:90%;
    max-width:1100px;
    display:flex;
    background:#fff;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    overflow:hidden;
}

/* LEFT FORM */
.form-section{
    flex:1;
    padding:45px 55px;
}

.form-section h2{
    font-size:28px;
    font-weight:700;
    color:#2c3e50;
    border-bottom:3px solid #ff9800;
    display:inline-block;
    padding-bottom:6px;
    margin-bottom:25px;
}

.form-section label{
    font-weight:500;
    font-size:14px;
}

.form-section input{
    width:100%;
    padding:12px;
    margin:10px 0 18px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:15px;
}

.form-section input:focus{
    outline:none;
    border-color:#ff9800;
    box-shadow:0 0 6px rgba(255,152,0,.4);
}

.form-section button{
    width:100%;
    padding:13px;
    background:linear-gradient(to right,#1565c0,#ff9800);
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}

.form-section button:hover{
    transform:scale(1.03);
    opacity:.95;
}

.form-section p{
    text-align:center;
    margin-top:18px;
    font-size:14px;
}

.form-section a{
    color:#1565c0;
    font-weight:600;
    text-decoration:none;
}

.form-section a:hover{
    color:#ff9800;
}

/* ERROR */
.error{
    background:#ffe0e0;
    color:#c0392b;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    font-size:14px;
}

/* RIGHT IMAGE */
.image-section{
    flex:1;
    background:url('img/1.jpg') center/cover no-repeat;
    position:relative;
}

.image-section::after{
    content:'';
    position:absolute;
    inset:0;
    background:rgba(21,101,192,.35);
}

/* RESPONSIVE */
@media(max-width:850px){
    .container{
        flex-direction:column;
    }
    .image-section{
        display:none;
    }
}
</style>
</head>

<body>

<div class="container">

    <!-- LEFT -->
    <div class="form-section">
        <h2>Login</h2>

        <?php if(isset($error)){ ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">
            <label>Email Address</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p>
            Back to <a href="index.php">Home</a>
        </p>
    </div>

    <!-- RIGHT -->
    <div class="image-section"></div>

</div>

</body>
</html>
