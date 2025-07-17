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

$category_id = $_GET['id'] ?? '';

if (empty($category_id)) {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'ID da categoria não fornecido');
}

$db = new db_connection();
$conn = $db->get_connection();

// Check if category has certificates associated
$sql_check = "SELECT COUNT(*) as count FROM certificado WHERE fk_categoria_id = ?";
$result_check = $conn->execute_query($sql_check, [$category_id]);

if ($result_check) {
    $row = $result_check->fetch_assoc();
    $count = $row['count'];
    $result_check->free();
    
    if ($count > 0) {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Não é possível excluir esta categoria pois existem certificados associados a ela');
    }
}

// Delete category
$sql_delete = "DELETE FROM categoria WHERE id = ?";
$result_delete = $conn->execute_query($sql_delete, [$category_id]);

$db->close_connection();

if ($result_delete) {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Categoria excluída com sucesso', 'success');
} else {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir categoria. Tente novamente.');
}
?>
