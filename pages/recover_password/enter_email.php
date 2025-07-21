<?php
require_once '../../includes/session_start.php';
require_once '../../includes/toast.php';
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../../assets/img/ChronoCert_logo.svg">
  <?php include '../../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../../assets/css/bootstrap_custom.css">
  <title>Alterar Senha - ChronoCert</title>
</head>

<body>
  <?php render_toast(); ?>

  <nav class="navbar navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../../assets/img/ChronoCert_logo_white.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle fw-bold">ChronoCert</span>
      </a>
      <a class="btn btn-outline-light" href="../../index.php">Voltar</a>
    </div>
  </nav>

  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
      <div class="col-md-8 col-lg-6 col-xl-4 mx-auto">
        <div class="card shadow-lg p-4">
          <h3 class="text-center mb-4">Alterar Senha</h3>
          <form method="POST" action="../../actions/recover_password/send_email.php" class="spinner-trigger">
            <div class="input-group mb-3">
              <input type="email" name="email_recover_password" id="email_recover_password" class="form-control" placeholder="Digite seu E-mail" required>
            </div>
            <button type="submit" name="submit_recover_password" class="btn btn-primary w-100" id="submit_btn" disabled>Prosseguir</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/spinner.php'?>
  <script src="../../assets/js/spinner.js"></script>
  <?php include '../../includes/bootstrap_script.php' ?>
  <script src="../../assets/js/toast.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const email_field = document.getElementById('email_recover_password');
      const submit_btn = document.getElementById('submit_btn');

      email_field.addEventListener('input', function() {
        submit_btn.disabled = !email_field.value.trim();
      });
    });
  </script>
</body>

</html>