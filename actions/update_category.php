<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

// Check if user is logged in and is a coordinator
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Você não está logado.']);
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas coordenadores podem gerenciar categorias.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'] ?? '';
    $category_name = trim($_POST['category_name'] ?? '');

    if (empty($category_id) || empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'ID da categoria e nome são obrigatórios']);
        exit();
    }

    $db = new db_connection();
    $conn = $db->get_connection();

    // Check if new name already exists (excluding current category)
    $sql_check = "SELECT id FROM categoria WHERE nome = ? AND id != ?";
    $result_check = $conn->execute_query($sql_check, [$category_name, $category_id]);

    if ($result_check && $result_check->num_rows > 0) {
        $result_check->free();
        $db->close_connection();
        echo json_encode(['success' => false, 'message' => 'Esta categoria já existe']);
        exit();
    }

    if ($result_check) $result_check->free();

    // Update category
    $sql_update = "UPDATE categoria SET nome = ? WHERE id = ?";
    $result_update = $conn->execute_query($sql_update, [$category_name, $category_id]);

    $db->close_connection();

    if ($result_update) {
        echo json_encode(['success' => true, 'message' => 'Categoria atualizada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar categoria']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
}
?>
