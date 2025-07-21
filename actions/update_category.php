<?php

/**
 * Atualizar Categoria
 * 
 * Processa a atualização de nomes de categorias existentes.
 * Apenas coordenadores podem editar categorias.
 */

require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

// Apenas coordenadores podem editar categorias
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Você não está logado.']);
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas coordenadores podem gerenciar categorias.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {

    $category_id = $_POST['category_id'] ?? '';
    $category_name = trim($_POST['category_name'] ?? '');
    $carga_maxima = (int)($_POST['carga_maxima'] ?? 0);

    if (empty($category_id) || empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'ID da categoria e nome são obrigatórios']);
        exit();
    }

    if ($carga_maxima <= 0) {
        echo json_encode(['success' => false, 'message' => 'A carga horária máxima deve ser maior que zero']);
        exit();
    }

    $db = new db_connection();
    $conn = $db->get_connection();

    $sql_update = "UPDATE categoria SET nome = ?, carga_maxima = ? WHERE id = ?";
    $result_update = $conn->execute_query($sql_update, [$category_name, $carga_maxima, $category_id]);

    $db->close_connection();

    if ($result_update) {
        echo json_encode(['success' => true, 'message' => 'Categoria atualizada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar categoria']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
}
