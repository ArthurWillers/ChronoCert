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

// Verifica se a requisição é POST e tem os dados necessários
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_student_submit']) || !isset($_POST['student_email'])) {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Acesso não autorizado ou dados inválidos.');
}

$student_email = trim($_POST['student_email'] ?? '');

if (empty($student_email)) {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Email do aluno não fornecido');
}

$db = new db_connection();
$conn = $db->get_connection();

// Verifica se o usuário existe e é um aluno do mesmo curso do coordenador
$sql_check = "SELECT u.tipo_de_conta, u.fk_curso_id as student_curso, coord.fk_curso_id as coord_curso 
              FROM usuario u, usuario coord 
              WHERE u.email = ? AND coord.email = ?";
$result_check = $conn->execute_query($sql_check, [$student_email, $_SESSION['user_email']]);

if ($result_check && $result_check->num_rows > 0) {
    $user_data = $result_check->fetch_assoc();
    $result_check->free();

    // Impede a exclusão se não for aluno
    if ($user_data['tipo_de_conta'] !== 'aluno') {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Não é possível excluir este usuário pois não é um aluno');
    }

    // Verifica se o aluno pertence ao mesmo curso do coordenador
    if ($user_data['student_curso'] !== $user_data['coord_curso']) {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro: Você só pode excluir alunos do seu próprio curso.');
    }
} else {
    if ($result_check) $result_check->free();
    $db->close_connection();
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Aluno não encontrado');
} // Primeiro, busca todos os certificados do aluno para deletar os arquivos físicos
$sql_certificates = "SELECT nome_do_arquivo FROM certificado WHERE fk_usuario_email = ?";
$result_certificates = $conn->execute_query($sql_certificates, [$student_email]);

$upload_dir = __DIR__ . '/../private/uploads/';
$files_deleted = 0;
$files_errors = 0;

if ($result_certificates && $result_certificates->num_rows > 0) {
    while ($certificate = $result_certificates->fetch_assoc()) {
        $file_path = $upload_dir . $certificate['nome_do_arquivo'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $files_deleted++;
            } else {
                $files_errors++;
            }
        }
    }
    $result_certificates->free();
}

// Inicia transação para garantir consistência
$conn->begin_transaction();

try {
    // Deleta os certificados do banco de dados
    $sql_delete_certificates = "DELETE FROM certificado WHERE fk_usuario_email = ?";
    $result_delete_certificates = $conn->execute_query($sql_delete_certificates, [$student_email]);

    // Deleta o usuário
    $sql_delete_user = "DELETE FROM usuario WHERE email = ? AND tipo_de_conta = 'aluno'";
    $result_delete_user = $conn->execute_query($sql_delete_user, [$student_email]);

    if ($result_delete_user && $conn->affected_rows > 0) {
        $conn->commit();
        $db->close_connection();

        $message = 'Aluno excluído com sucesso';
        if ($files_deleted > 0) {
            $message .= " ($files_deleted arquivo(s) removido(s))";
        }
        if ($files_errors > 0) {
            $message .= " (Atenção: $files_errors arquivo(s) não puderam ser removidos)";
        }

        redirect_with_toast('../pages/coordinator_dashboard.php', $message, 'success');
    } else {
        $conn->rollback();
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir aluno. Tente novamente.');
    }
} catch (Exception $e) {
    $conn->rollback();
    $db->close_connection();
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir aluno: ' . $e->getMessage());
}
