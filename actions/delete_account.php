<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    session_unset();
    redirect_with_toast('../index.php', "Você não está logado.");
}

// Verificar se o usuário é coordenador
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    redirect_with_toast('../pages/dashboard.php', "Acesso negado. Apenas coordenadores podem deletar contas.");
}

// Deletar conta do coordenador (auto-exclusão)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_submit'])) {
    $email_to_delete = $_SESSION['user_email']; // Coordenador deletando a própria conta

    $db = new db_connection();
    $conn = $db->get_connection();

    // Deletar apenas a conta do coordenador
    $sql = "DELETE FROM usuario WHERE email = ?";
    $result = $conn->execute_query($sql, [$email_to_delete]);

    if ($result) {
        $db->close_connection();
        session_unset();
        redirect_with_toast('../index.php', 'Conta de coordenador excluída com sucesso.', 'success');
    } else {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir a conta');
    }
} // Deletar conta específica (coordenador deletando conta de aluno)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student_submit']) && isset($_POST['student_email'])) {
    $student_email = $_POST['student_email'];

    $db = new db_connection();
    $conn = $db->get_connection();

    // Verificar se o aluno pertence ao curso do coordenador
    $sql_verify = "SELECT u1.fk_curso_id as coord_curso, u2.fk_curso_id as student_curso 
                   FROM usuario u1, usuario u2 
                   WHERE u1.email = ? AND u2.email = ?";
    $verify_result = $conn->execute_query($sql_verify, [$_SESSION['user_email'], $student_email]);

    if ($verify_result && $verify_result->num_rows > 0) {
        $verify_data = $verify_result->fetch_assoc();
        $verify_result->free();

        if ($verify_data['coord_curso'] !== $verify_data['student_curso']) {
            $db->close_connection();
            redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro: Você só pode deletar alunos do seu próprio curso.');
        }
    } else {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro: Aluno não encontrado.');
    }

    // Deletar certificados do aluno
    $sql_certs = "SELECT nome_do_arquivo FROM certificado WHERE fk_usuario_email = ?";
    $cert_result = $conn->execute_query($sql_certs, [$student_email]);

    if ($cert_result && $cert_result->num_rows > 0) {
        while ($row = $cert_result->fetch_assoc()) {
            $file_path = "../private/uploads/" . $row['nome_do_arquivo'];
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }
        $cert_result->free();
    }

    // Deletar aluno
    $sql_delete = "DELETE FROM usuario WHERE email = ?";
    $delete_result = $conn->execute_query($sql_delete, [$student_email]);

    if ($delete_result) {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Aluno excluído com sucesso.', 'success');
    } else {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir aluno.');
    }
} else {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Acesso não autorizado.');
}
