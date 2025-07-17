<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';

// Public registration is now disabled - only coordinators can register students
redirect_with_toast('../pages/login.php', 'O cadastro público foi desabilitado. Apenas coordenadores podem cadastrar novos alunos.');
?>
