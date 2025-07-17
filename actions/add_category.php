<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

// Check if user is logged in and is a coordinator
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect_with_toast('../index.php', 'Você não está logado.');
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    redirect_with_toast('../pages/dashboard.php', 'Acesso negado. Apenas coordenadores podem gerenciar categorias.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name'] ?? '');

    if (empty($category_name)) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'O nome da categoria não pode estar vazio');
    }

    $db = new db_connection();
    $conn = $db->get_connection();

    // Check if category already exists
    $sql_check = "SELECT id FROM categoria WHERE nome = ?";
    $result_check = $conn->execute_query($sql_check, [$category_name]);

    if ($result_check && $result_check->num_rows > 0) {
        $result_check->free();
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Esta categoria já existe');
    }

    if ($result_check) $result_check->free();

    // Insert new category
    $sql_insert = "INSERT INTO categoria (nome) VALUES (?)";
    $result_insert = $conn->execute_query($sql_insert, [$category_name]);

    $db->close_connection();

    if ($result_insert) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Categoria adicionada com sucesso', 'success');
    } else {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao adicionar categoria. Tente novamente.');
    }
} else {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Acesso não autorizado');
}
?>
