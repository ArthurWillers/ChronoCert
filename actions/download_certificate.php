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

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'coordenador') {
  $sql = "SELECT c.nome_do_arquivo, c.nome_pessoal, c.fk_categoria_id, c.fk_usuario_email, u.nome_de_usuario as student_name FROM certificado c JOIN usuario u ON c.fk_usuario_email = u.email WHERE c.nome_do_arquivo = ?";
  $result = $conn->execute_query($sql, [$filename]);
} else {
  $sql = "SELECT nome_do_arquivo, nome_pessoal, fk_categoria_id FROM certificado WHERE nome_do_arquivo = ? AND fk_usuario_email = ?";
  $result = $conn->execute_query($sql, [$filename, $_SESSION['user_email']]);
}

if (!$result || $result->num_rows == 0) {
  $db->close_connection();
  redirect_with_toast("../pages/dashboard.php", "Certificado não encontrado ou você não tem permissão para baixá-lo.", "danger");
  exit();
}

$file = $result->fetch_assoc();


// Nomes das categorias
$sql_cat = "SELECT nome FROM categoria WHERE id = ?";
$result_cat = $conn->execute_query($sql_cat, [$file['fk_categoria_id']]);
$category = $result_cat->fetch_assoc();

$file_path = $upload_dir . $file['nome_do_arquivo'];
$db->close_connection();

if (!file_exists($file_path)) {
  redirect_with_toast("../pages/dashboard.php", "Arquivo não encontrado no servidor.", "danger");
  exit();
}

$base_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $file['nome_pessoal']);
$category_name = str_replace('_', ' ', $category['nome']);

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'coordenador' && isset($file['student_name'])) {
  $student_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $file['student_name']);
  $internal_name = $student_name . " - " . $base_name . " - " . $category_name . ".pdf";
} else {
  $internal_name = $base_name . " - " . $category_name . ".pdf";
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $internal_name . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
