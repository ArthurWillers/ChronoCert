<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

// Check if user is logged in and is a coordinator
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect_with_toast('../index.php', 'Você não está logado.');
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    redirect_with_toast('../pages/dashboard.php', 'Acesso negado. Apenas coordenadores podem cadastrar alunos.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_register'])) {

    $username_register = $_POST['username_register'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_register = $_POST['password_register'] ?? '';
    $confirm_password_register = $_POST['confirm_password_register'] ?? '';
    $curso_id = $_POST['curso_id'] ?? '';

    if (empty($username_register) || empty($email) || empty($password_register) || empty($confirm_password_register) || empty($curso_id)) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Todos os campos devem ser preenchidos');
    }

    if ($password_register !== $confirm_password_register) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'As senhas não coincidem');
    }

    if (strlen($password_register) < 8) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'A senha deve ter pelo menos 8 caracteres');
    }

    $db = new db_connection();
    $conn = $db->get_connection();

    // Check if email already exists
    $sql_check = "SELECT email FROM usuario WHERE email = ?";
    $result_check = $conn->execute_query($sql_check, [$email]);

    if ($result_check && $result_check->num_rows > 0) {
        $result_check->free();
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Este e-mail já está cadastrado');
    }

    if ($result_check) $result_check->free();

    // Hash the password
    $hashed_password = password_hash($password_register, PASSWORD_DEFAULT);

    // Insert new student
    $sql_insert = "INSERT INTO usuario (email, nome_de_usuario, senha, tipo_de_conta, fk_curso_id) VALUES (?, ?, ?, 'aluno', ?)";
    $result_insert = $conn->execute_query($sql_insert, [$email, $username_register, $hashed_password, $curso_id]);

    $db->close_connection();

    if ($result_insert) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Aluno cadastrado com sucesso', 'success');
    } else {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao cadastrar aluno. Tente novamente.');
    }
} else {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Acesso não autorizado');
}
?>
