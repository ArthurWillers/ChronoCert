<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_register'])) {

    $username_register = $_POST['username_register'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_register = $_POST['password_register'] ?? '';
    $confirm_password_register = $_POST['confirm_password_register'] ?? '';

    if (empty($username_register) || empty($email) || empty($password_register) || empty($confirm_password_register)) {
        redirect_with_toast('../pages/register.php', 'Todos os campos devem ser preenchidos');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_toast('../pages/register.php', 'E-mail inválido');
    }

    if ($password_register !== $confirm_password_register) {
        redirect_with_toast('../pages/register.php', 'As senhas não coincidem');
    }

    $db = new db_connection();
    $conn = $db->open();

    $sql = "SELECT email FROM usuario WHERE email = ?";
    $result = $conn->execute_query($sql, [$email]);
    if ($result && $result->num_rows > 0) {
        $result->free();
        $db->close();
        redirect_with_toast('../pages/register.php', 'Este e-mail já está cadastrado');
    }
    if ($result) $result->free();

    $sql = "SELECT nome_de_usuario FROM usuario WHERE nome_de_usuario = ?";
    $result = $conn->execute_query($sql, [$username_register]);
    if ($result && $result->num_rows > 0) {
        $result->free();
        $db->close();
        redirect_with_toast('../pages/register.php', 'Este nome de usuário já está cadastrado');
    }
    if ($result) $result->free();

    $hashed_password = password_hash($password_register, PASSWORD_BCRYPT);

    $sql = "INSERT INTO usuario (nome_de_usuario, email, senha) VALUES (?, ?, ?)";
    $result = $conn->execute_query($sql, [$username_register, $email, $hashed_password]);
    if ($result) {
        $db->close();
        redirect_with_toast('../pages/login.php', 'Cadastro realizado com sucesso', 'success');
    } else {
        $db->close();
        redirect_with_toast('../pages/register.php', 'Erro ao cadastrar usuário');
    }
} else {
    set_toast('Acesso não autorizado', 'danger');
    header('Location: ../index.php');
    exit();
}
