<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  session_unset();
  redirect_with_toast('../index.php', "Você não está logado. Faça login para excluir certificados.");
  exit;
}


$redirect_url = '../pages/' . $_POST['redirect'] ?? '../pages/dashboard.php';

if (!isset($_POST['file_name']) || empty($_POST['file_name'])) {
  redirect_with_toast('../pages/dashboard.php', "Nome do arquivo não fornecido.");
  exit;
}

$file_name = $_POST['file_name'];
$user_email = $_SESSION['user_email'];


$db = new db_connection();
$conn = $db->get_connection();

try {

  $check_sql = "SELECT * FROM certificado WHERE nome_do_arquivo = ? AND fk_usuario_email = ?";
  $check_result = $conn->execute_query($check_sql, [$file_name, $user_email]);

  if ($check_result->num_rows === 0) {
    redirect_with_toast($redirect_url, "Certificado não encontrado ou não pertence a você.");
    exit;
  }


  $delete_sql = "DELETE FROM certificado WHERE nome_do_arquivo = ? AND fk_usuario_email = ?";
  $delete_result = $conn->execute_query($delete_sql, [$file_name, $user_email]);

  if ($conn->affected_rows > 0) {

    $file_path = "../private/uploads/{$file_name}";

    if (file_exists($file_path)) {
      if (unlink($file_path)) {
        redirect_with_toast($redirect_url, "Certificado excluído com sucesso.", "success");
      } else {

        redirect_with_toast($redirect_url, "Certificado removido do sistema, mas o arquivo físico não pôde ser excluído.");
      }
    } else {

      redirect_with_toast($redirect_url, "Certificado excluído com sucesso.", "success");
    }
  } else {
    redirect_with_toast($redirect_url, "Erro ao excluir o certificado. Tente novamente.");
  }
} catch (Exception $e) {
  redirect_with_toast($redirect_url, "Erro ao excluir o certificado: " . $e->getMessage());
} finally {
  $conn->close_connection();
}
