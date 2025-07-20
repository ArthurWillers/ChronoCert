<?php
require_once '../includes/session_start.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não logado']);
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

$filename = $_POST['filename'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($filename) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit();
}

if (!in_array($status, ['não_verificado', 'válido', 'incerto'])) {
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit();
}

$db = new db_connection();
$conn = $db->get_connection();

$sql = "UPDATE certificado SET status = ? WHERE nome_do_arquivo = ?";
$result = $conn->execute_query($sql, [$status, $filename]);

$db->close_connection();

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
}
?>