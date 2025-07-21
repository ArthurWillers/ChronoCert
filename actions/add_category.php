<?php

/**
 * Adicionar Categoria
 * 
 * Processa a adição de novas categorias de certificados.
 * Apenas coordenadores podem criar categorias.
 * Inclui validação de duplicatas e feedback ao usuário.
 */

require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect_with_toast('../index.php', 'Você não está logado.');
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    redirect_with_toast('../pages/dashboard.php', 'Acesso negado. Apenas coordenadores podem gerenciar categorias.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {

    // trim() limpa o whitespace
    $category_name = trim($_POST['category_name'] ?? '');
    $carga_maxima = (int)($_POST['carga_maxima'] ?? 40);

    if (empty($category_name)) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'O nome da categoria não pode estar vazio');
    }

    if ($carga_maxima <= 0) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'A carga horária máxima deve ser maior que zero');
    }

    $db = new db_connection();
    $conn = $db->get_connection();

    // Get coordinator's course
    $coordinator_email = $_SESSION['user_email'];
    $sql_course = "SELECT fk_curso_id FROM usuario WHERE email = ?";
    $course_result = $conn->execute_query($sql_course, [$coordinator_email]);

    if (!$course_result || $course_result->num_rows === 0) {
        if ($course_result) $course_result->free();
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro: Coordenador não encontrado');
    }

    $course_data = $course_result->fetch_assoc();
    $course_id = $course_data['fk_curso_id'];
    $course_result->free();

    $sql_check = "SELECT id FROM categoria WHERE nome = ? AND fk_curso_id = ?";
    $result_check = $conn->execute_query($sql_check, [$category_name, $course_id]);

    if ($result_check && $result_check->num_rows > 0) {
        $result_check->free();
        $db->close_connection();
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Esta categoria já existe neste curso');
    }

    if ($result_check) $result_check->free();
    $sql_insert = "INSERT INTO categoria (nome, fk_curso_id, carga_maxima) VALUES (?, ?, ?)";
    $result_insert = $conn->execute_query($sql_insert, [$category_name, $course_id, $carga_maxima]);

    $db->close_connection();

    if ($result_insert) {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Categoria adicionada com sucesso', 'success');
    } else {
        redirect_with_toast('../pages/coordinator_dashboard.php', 'Erro ao adicionar categoria. Tente novamente.');
    }
} else {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Acesso não autorizado');
}
