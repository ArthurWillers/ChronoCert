<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  session_unset();
  redirect_with_toast("../index.php", "Você não está logado. Faça login para baixar os certificados.", "danger");
  exit();
}

$filename = $_GET['filename'] ?? '';
if (empty($filename)) {
  redirect_with_toast("../pages/dashboard.php", "Arquivo não especificado.", "danger");
  exit();
}

$upload_dir = __DIR__ . '/../private/uploads/';

$db = new db_connection();
$conn = $db->get_connection();
$sql = "SELECT nome_do_arquivo, nome_pessoal, categoria FROM certificado WHERE nome_do_arquivo = ? AND fk_usuario_email = ?";
$result = $conn->execute_query($sql, [$filename, $_SESSION['user_email']]);

if (!$result || $result->num_rows == 0) {
  $db->close_connection();
  redirect_with_toast("../pages/dashboard.php", "Certificado não encontrado ou você não tem permissão para baixá-lo.", "danger");
  exit();
}

$file = $result->fetch_assoc();
$file_path = $upload_dir . $file['nome_do_arquivo'];
$conn->close_connection();

if (!file_exists($file_path)) {
  $db->close_connection();
  redirect_with_toast("../pages/dashboard.php", "Arquivo não encontrado no servidor.", "danger");
  exit();
}

$base_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $file['nome_pessoal']);
$category_name = str_replace('_', ' ', $file['categoria']);
$internal_name = $base_name . " - " . $category_name . ".pdf";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $internal_name . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
