<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    session_unset();
    redirect_with_toast("../index.php", "Você não está logado. Faça login para baixar os certificados.", "danger");
    exit();
}


$upload_dir = __DIR__ . '/../private/uploads/';
$tmp_dir = __DIR__ . '/../private/tmp/';

$user_email = $_SESSION['user_email'];

$db = new db_connection();
$conn = $db->open();
$sql = "SELECT nome_do_arquivo, nome_pessoal, categoria FROM certificado WHERE fk_usuario_email = ?";
$result = $conn->execute_query($sql, [$user_email]);

if (!$result || $result->num_rows == 0) {
    $db->close();
    redirect_with_toast("../pages/dashboard.php", "Nenhum certificado encontrado.", "warning");
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'user';

$zip_name = 'certificados_' . $username . '.zip';
$zip_path = $tmp_dir . uniqid('chronocert_', true) . '.zip';

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    $db->close();
    redirect_with_toast("../pages/dashboard.php", "Falha ao criar o arquivo zip.", "danger");
    exit();
}

$has_files = false;
while ($file = $result->fetch_assoc()) {
    $file_path = $upload_dir . $file['nome_do_arquivo'];
    if (file_exists($file_path)) {

        $base_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $file['nome_pessoal']);
        $category_name = str_replace('_', ' ', $file['categoria']);
        $internal_name = $base_name . " - " . $category_name . ".pdf";

        $zip->addFile($file_path, $internal_name);
        $has_files = true;
    }
}
$zip->close();
$result->free();
$db->close();

if (!$has_files || !file_exists($zip_path)) {
    redirect_with_toast("../pages/dashboard.php", "Não foi possível criar o arquivo ZIP.", "danger");
    exit();
}

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_name . '"');
header('Content-Length: ' . filesize($zip_path));
header('Pragma: no-cache');
header('Expires: 0');

readfile($zip_path);
unlink($zip_path);
exit();
