<?php
/**
 * Excluir Aluno
 * 
 * Processa a exclusão de contas de alunos do sistema.
 * Apenas coordenadores podem excluir alunos.
 */

require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect_with_toast('../index.php', 'Você não está logado.');
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    redirect_with_toast('../pages/dashboard.php', 'Acesso negado. Apenas coordenadores podem excluir alunos.');
}

$student_email = $_GET['email'] ?? '';

if (empty($student_email)) {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Email do aluno não fornecido');
}

$db = new db_connection();
$conn = $db->get_connection();

// Verifica se o usuário existe e é um aluno
$sql_check = "SELECT tipo_de_conta FROM usuario WHERE email = ?";
$result_check = $conn->execute_query($sql_check, [$student_email]);

if ($result_check && $result_check->num_rows > 0) {
    $user_data = $result_check->fetch_assoc();
    $result_check->free();
    
    // Impede a exclusão se não for aluno
    if ($user_data['tipo_de_conta'] !== 'aluno') {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Não é possível excluir este usuário pois não é um aluno');
    }
} else {
    if ($result_check) $result_check->free();
    $db->close_connection();
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Aluno não encontrado');
}

$sql_delete = "DELETE FROM usuario WHERE email = ? AND tipo_de_conta = 'aluno'";
$result_delete = $conn->execute_query($sql_delete, [$student_email]);

if ($result_delete && $conn->affected_rows > 0) {
    $db->close_connection();
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Aluno excluído com sucesso', 'success');
} else {
    $db->close_connection();
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir aluno. Tente novamente.');
}
?>
