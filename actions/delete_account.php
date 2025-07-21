<?php

/**
 * Deletar Própria Conta do Coordenador
 * 
 * Permite que coordenadores deletem suas próprias contas.
 * Apenas coordenadores podem usar esta funcionalidade.
 */

require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    session_unset();
    redirect_with_toast('../index.php', "Você não está logado.");
}

// Verificar se o usuário é coordenador
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    redirect_with_toast('../pages/dashboard.php', "Acesso negado. Apenas coordenadores podem deletar suas contas.");
}

// Deletar conta do coordenador (auto-exclusão)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_submit'])) {
    $email_to_delete = $_SESSION['user_email']; // Coordenador deletando a própria conta

    $db = new db_connection();
    $conn = $db->get_connection();

    // Deletar apenas a conta do coordenador
    $sql = "DELETE FROM usuario WHERE email = ? AND tipo_de_conta = 'coordenador'";
    $result = $conn->execute_query($sql, [$email_to_delete]);

    if ($result && $conn->affected_rows > 0) {
        $db->close_connection();
        session_unset();
        redirect_with_toast('../index.php', 'Sua conta de coordenador foi excluída com sucesso.', 'success');
    } else {
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao excluir sua conta. Tente novamente.');
    }
} else {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Acesso não autorizado.');
}
