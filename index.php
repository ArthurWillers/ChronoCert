<?php
require_once './includes/session_start.php';
require_once './includes/toast.php';


if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
  header("Location: ./pages/dashboard.php");
  exit();
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="./assets/img/ChronoCert_logo.svg">
  <?php include './includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="./assets/css/bootstrap_custom.css">
  <title>ChronoCert</title>
</head>

<body>

  <?php render_toast() ?>

  <div class="container-fluid min-vh-100 text-center text-white bg-primary d-flex flex-column justify-content-center align-items-center">
    <img src="./assets/img/ChronoCert_logo_white.png" alt="ChronoCert Logo" width="120" class="mb-3" />
    <h1 class="display-4 fw-bold mb-3">ChronoCert</h1>

    
    <div class="d-flex justify-content-center gap-3 mt-4">
      <a href="./pages/register.php" class="btn btn-outline-light btn-lg">Criar Conta</a>
      <a href="./pages/login.php" class="btn btn-outline-light btn-lg">Entrar</a>
    </div>
  </div>

  <?php include './includes/bootstrap_script.php' ?>
  <script src="./assets/js/toast.js"></script>
</body>

</html>