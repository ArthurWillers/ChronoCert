<?php
require_once '../../includes/session_start.php';
require_once '../../includes/toast.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_recover_password'])) {

  $email = $_SESSION['email_recover_password'] ?? '';
  $verification_code = $_POST['verification_code'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_new_password = $_POST['confirm_new_password'] ?? '';
  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);


  if (empty($email)) {
    redirect_with_toast('../../pages/recover_password/enter_email.php', 'Problema ao recuperar a senha. Tente novamente.', 'danger');
    exit();
  }


  if (empty($verification_code) || empty($new_password) || empty($confirm_new_password)) {
    redirect_with_toast('../../pages/recover_password/recover_password.php', 'Preencha todos os campos', 'danger');
    exit();
  }


  if ($new_password !== $confirm_new_password) {
    redirect_with_toast('../../pages/recover_password/recover_password.php', 'As senhas não coincidem', 'danger');
    exit();
  }


  if (strlen($verification_code) < 8) {
    redirect_with_toast('../../pages/recover_password/recover_password.php', 'O código de verificação deve ter 8 caracteres', 'danger');
    exit();
  }


  require_once '../../private/config/db_connection.php';
  $db = new db_connection();
  $conn = $db->open();


  $sql = "SELECT * FROM codigo_de_verificacao WHERE codigo = ? AND fk_usuario_email = ?";
  $result = $conn->execute_query($sql, [$verification_code, $email]);
  if ($result && $result->num_rows > 0) {

    $sql = "UPDATE usuario SET senha = ? WHERE email = ?";
    $conn->execute_query($sql, [$hashed_password, $email]);


    $sql = "DELETE FROM codigo_de_verificacao WHERE codigo = ? AND fk_usuario_email = ?";
    $conn->execute_query($sql, [$verification_code, $email]);

    $result->free();
    $db->close();

    redirect_with_toast('../../pages/login.php', 'Senha alterada com sucesso', 'success');
    exit();
  } else {
    if ($result) $result->free();
    $db->close();
    redirect_with_toast('../../pages/recover_password/recover_password.php', 'Código de verificação inválido ou expirado', 'danger');
    exit();
  }
} else {
  session_unset();
  redirect_with_toast('../../index.php', 'Acesso não autorizado', 'danger');
  exit();
}
