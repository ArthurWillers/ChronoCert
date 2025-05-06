<?php
require_once '../../includes/session_start.php';
require_once '../../includes/toast.php';
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../../assets/img/ChronoCert_logo.png">
  <?php include '../../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../../assets/css/bootstrap_custom.css">
  <title>Redefinir Senha - ChronoCert</title>
</head>

<body>
  <?php render_toast(); ?>

  <nav class="navbar navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../../assets/img/ChronoCert_logo.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle">ChronoCert</span>
      </a>
      <a class="btn btn-outline-light" href="../../index.php">Voltar</a>
    </div>
  </nav>

  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
      <div class="col-md-8 col-lg-6 col-xl-4 mx-auto">
        <div class="card shadow-lg p-4">
          <h3 class="text-center mb-4">Redefinir Senha</h3>
          <form method="POST" action="../../actions/recover_password/recover_password.php">

            <div class="input-group mb-3">
              <input type="text" name="verification_code" class="form-control" placeholder="Digite o código de verificação" maxlength="8" required>
            </div>

            <div class="input-group mb-3">
              <input id="new_password" type="password" name="new_password" class="form-control" placeholder="Digite a nova senha" required>
              <button class="btn btn-outline-secondary" type="button" onclick="toggle_password_visibility('new_password', this)">
                <i class="bi bi-eye-slash"></i>
              </button>
            </div>

            <div class="input-group mb-3">
              <input id="confirm_new_password" type="password" name="confirm_new_password" class="form-control" placeholder="Confirme a nova senha" required>
              <button class="btn btn-outline-secondary" type="button" onclick="toggle_password_visibility('confirm_new_password', this)">
                <i class="bi bi-eye-slash"></i>
              </button>
            </div>
            <div id="password_error_message" class="form-text text-danger"></div>

            <button type="submit" name="submit_recover_password" class="btn btn-primary w-100 mt-3" disabled>Redefinir Senha</button>

            <div class="text-center mt-3">
              <span>Lembrou sua senha?</span>
              <a href="../login.php" class="text-decoration-none cursor-pointer">Voltar para Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/bootstrap_script.php' ?>
  <script src="../../assets/js/toast.js"></script>
  <script src="../../assets/js/toggle_password_visibility.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const code_field = document.querySelector('input[name="verification_code"]');
      const new_password_field = document.getElementById('new_password');
      const confirm_password_field = document.getElementById('confirm_new_password');
      const reset_btn = document.querySelector('button[name="submit_recover_password"]');
      const error_message = document.getElementById('password_error_message');

      function validate_form() {
        const code_filled = code_field.value.trim().length === 8;
        const new_pass_filled = new_password_field.value.trim() !== '';
        const confirm_pass_filled = confirm_password_field.value.trim() !== '';
        const all_filled = code_filled && new_pass_filled && confirm_pass_filled;
        const passwords_match = new_password_field.value === confirm_password_field.value;

        error_message.textContent = passwords_match ? '' : 'As senhas não coincidem.';
        reset_btn.disabled = !(all_filled && passwords_match);
      }

      code_field.addEventListener('input', validate_form);
      new_password_field.addEventListener('input', validate_form);
      confirm_password_field.addEventListener('input', validate_form);
    });
  </script>
</body>

</html>