<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    session_unset();
    redirect_with_toast('../index.php', "Você não está logado. Faça login para deletar a conta.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_submit'])) {
    if (!isset($_POST['delete_confirm_email']) || $_POST['delete_confirm_email'] !== $_SESSION['user_email']) {
        redirect_with_toast('../pages/dashboard.php', 'Erro ao excluir a conta. O e-mail não corresponde ao e-mail da conta.');
    }

    $db = new db_connection();
    $conn = $db->open();

    $sql = "SELECT nome_do_arquivo FROM certificado WHERE fk_usuario_email = ?";
    $cert_result = $conn->execute_query($sql, [$_SESSION['user_email']]);

    if ($cert_result && $cert_result->num_rows > 0) {
        while ($row = $cert_result->fetch_assoc()) {
            $file_path = "../private/uploads/" . $row['nome_do_arquivo'];
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }
        $cert_result->free();
    }

    $sql = "DELETE FROM usuario WHERE email = ?";
    $result = $conn->execute_query($sql, [$_SESSION['user_email']]);

    if ($result) {
        $db->close();
        session_unset();
        redirect_with_toast('../index.php', 'Conta excluída com sucesso', 'success');
    } else {
        $db->close();
        redirect_with_toast('../pages/dashboard.php', 'Erro ao excluir a conta');
    }
} else {
    session_unset();
    redirect_with_toast('../index.php', 'Acesso não autorizado');
}
