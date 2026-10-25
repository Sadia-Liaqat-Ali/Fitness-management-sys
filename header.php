
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitSync Pro | Fitness</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      --gradient-nav: linear-gradient(90deg, #007bff, #ff6600);
      --light-gradient: linear-gradient(180deg, #007bff1a, #ff66001a);
    }

    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: var(--light-gradient);
      font-family: 'Poppins', sans-serif;
    }

    .navbar-custom {
      background: var(--gradient-nav);
      position: sticky;
      top: 0;
      z-index: 1030;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .navbar-custom .navbar-brand {
      font-weight: 700;
      font-size: 1.6rem;
      color: white !important;
    }

    .navbar-custom .nav-link {
      color: white !important;
      font-weight: 500;
      padding: 8px 15px !important;
      margin: 0 5px;
      transition: 0.3s;
    }

    .navbar-custom .nav-link:hover {
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">

    <a class="navbar-brand" href="index.php">
      <i class="fas fa-dumbbell me-2"></i>Fitness Management
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navMenu">
      <ul class="navbar-nav">

        <li class="nav-item">
          <a class="nav-link active" href="index.php">
            <i class="fas fa-home me-1"></i> Home
          </a>
        </li>

       
        <li class="nav-item">
          <a class="nav-link" href="who-we-are.php">
            <i class="fas fa-blog me-1"></i> Our Portfolio
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="login.php">
            <i class="fas fa-sign-in-alt me-1"></i> Login
          </a>
        </li>

      </ul>
    </div>

  </div>
</nav>

<
