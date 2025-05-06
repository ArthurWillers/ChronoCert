<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_login'])) {

    $email_login = $_POST['email_login'] ?? '';
    $password_login = $_POST['password_login'] ?? '';

    if (empty($email_login) || empty($password_login)) {
        redirect_with_toast('../pages/login.php', 'Todos os campos devem ser preenchidos');
    }

    $db = new db_connection();
    $conn = $db->open();

    $sql = "SELECT * FROM usuario WHERE email = ?";
    $result = $conn->execute_query($sql, [$email_login]);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $result->free();
        $db->close();

        if (password_verify($password_login, $user['senha'])) {
            session_unset();
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['username'] = $user['nome_de_usuario'];
            $_SESSION['logged_in'] = true;
            redirect_with_toast('../pages/dashboard.php', 'Login realizado com sucesso', 'success');
        } else {
            session_unset();
            $_SESSION['logged_in'] = false;
            redirect_with_toast('../pages/login.php', 'E-mail ou senha incorretos');
        }
    } else {
        if ($result) $result->free();
        $db->close();
        session_unset();
        $_SESSION['logged_in'] = false;
        redirect_with_toast('../pages/login.php', 'E-mail ou senha incorretos');
    }
} else {
    session_unset();
    redirect_with_toast('../index.php', 'Acesso não autorizado');
}
