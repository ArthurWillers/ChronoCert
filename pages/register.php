<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../assets/img/ChronoCert_logo.svg">
  <?php include '../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../assets/css/bootstrap_custom.css">
  <title>register - ChronoCert</title>
</head>

<body>
  <?php render_toast() ?>

  <nav class="navbar navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../assets/img/ChronoCert_logo_white.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle fw-bold">ChronoCert</span>
      </a>
      <a class="btn btn-outline-light" href="../index.php">Voltar</a>
    </div>
  </nav>

  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
      <div class="col-md-8 col-lg-6 col-xl-4 mx-auto">
        <div class="card shadow-lg p-4">
          <h3 class="text-center mb-4 squada-one-regular">Cadastro</h3>
          <form method="POST" action="../actions/register.php">

            <div class="input-group mb-3">
              <input type="text" name="username_register" class="form-control" placeholder="Digite seu nome de usuário" maxlength="255" required>
            </div>

            <div class="input-group mb-3">
              <input type="email" name="email" class="form-control" placeholder="Digite seu E-mail" maxlength="255" required>
            </div>

            <div class="input-group mb-3">
              <input id="password_register" type="password" name="password_register" class="form-control" placeholder="Digite sua senha" required>
              <button class="btn btn-outline-secondary" type="button"
                onclick="toggle_password_visibility('password_register', this)">
                <i class="bi bi-eye-slash"></i>
              </button>
            </div>

            <div class="input-group">
              <input id="confirm_password_register" type="password" name="confirm_password_register" class="form-control" placeholder="Confirme sua senha" required>
              <button class="btn btn-outline-secondary" type="button"
                onclick="toggle_password_visibility('confirm_password_register', this)">
                <i class="bi bi-eye-slash"></i>
              </button>
            </div>
            <div id="password_error_message" class="form-text text-danger"></div>

            <button type="submit" name="submit_register" class="btn btn-primary w-100 mt-3" disabled>Cadastrar</button>

            <div class="text-center mt-3">
              <span>Já tem uma conta?</span>
              <a href="./login.php" class="text-decoration-none cursor-pointer">Voltar para Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>


  <?php require_once '../includes/bootstrap_script.php' ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const user_field = document.querySelector('input[name="username_register"]');
      const email_field = document.querySelector('input[name="email"]');
      const password_field = document.querySelector('input[name="password_register"]');
      const confirm_field = document.querySelector('input[name="confirm_password_register"]');
      const register_btn = document.querySelector('button[name="submit_register"]');
      const error_message = document.getElementById('password_error_message');

      function validate_form() {
        const user_filled = user_field.value.trim() !== '';
        const email_filled = email_field.value.trim() !== '';
        const pass_filled = password_field.value.trim() !== '';
        const confirm_filled = confirm_field.value.trim() !== '';
        const all_filled = user_filled && email_filled && pass_filled && confirm_filled;
        const passwords_match = password_field.value === confirm_field.value;

        error_message.textContent = passwords_match ? '' : 'As senhas não coincidem.';
        register_btn.disabled = !(all_filled && passwords_match);
      }

      [user_field, email_field, password_field, confirm_field].forEach(field => {
        field.addEventListener('input', validate_form);
      });
    });
  </script>
  <script src="../assets/js/toast.js"></script>
  <script src="../assets/js/toggle_password_visibility.js"></script>
</body>

</html>