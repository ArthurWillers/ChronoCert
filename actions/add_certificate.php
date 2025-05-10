<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  session_unset();
  redirect_with_toast('../index.php', "Você não está logado. Faça login para adicionar um certificado.", "danger");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_certificate'])) {

  $db = new db_connection();
  $conn = $db->open();

  $errors = [];
  $upload_dir = __DIR__ . '/../private/uploads/';


  $nome_pessoal = trim($_POST['nome_pessoal'] ?? '');
  $carga_horaria = floatval($_POST['carga_horaria'] ?? 0);
  $categoria = $_POST['categoria'] ?? '';

  if (empty($nome_pessoal)) {
    $errors[] = "Nome pessoal é obrigatório.";
  }

  if ($carga_horaria <= 0) {
    $errors[] = "A carga horária deve ser maior que zero.";
  }

  if (empty($categoria)) {
    $errors[] = "Selecione uma categoria.";
  }


  if (!isset($_FILES['arquivo_certificado']) || $_FILES['arquivo_certificado']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Erro no upload do arquivo. Verifique se selecionou um arquivo PDF válido.";
  } else {
    $tmp_name = $_FILES['arquivo_certificado']['tmp_name'];
    $name = basename($_FILES['arquivo_certificado']['name']);
    $size = $_FILES['arquivo_certificado']['size'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));


    if ($size > 10 * 1024 * 1024) {
      $errors[] = "O arquivo excede o limite de tamanho de 10MB.";
    }


    if ($ext !== 'pdf') {
      $errors[] = "Apenas arquivos PDF são permitidos.";
    } else {

      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $tmp_name);
      finfo_close($finfo);

      if ($mime !== 'application/pdf') {
        $errors[] = "O arquivo não é um PDF válido.";
      }
    }


    do {
      $new_filename = uniqid('cert_', true) . '.pdf';
      $check = $conn->execute_query("SELECT 1 FROM certificado WHERE nome_do_arquivo = ?", [$new_filename]);
      $exists = ($check && $check->num_rows > 0);
      if ($check) $check->free_result();
    } while ($exists);

    $destination = $upload_dir . $new_filename;


    if (empty($errors)) {
      if (!move_uploaded_file($tmp_name, $destination)) {
        $errors[] = "Falha ao mover o arquivo para o destino. Verifique as permissões do diretório.";
      } else {

        $sql = "INSERT INTO certificado (nome_do_arquivo, nome_pessoal, carga_horaria, categoria, fk_usuario_email) 
                        VALUES (?, ?, ?, ?, ?)";

        if (!$conn->execute_query($sql, [$new_filename, $nome_pessoal, $carga_horaria, $categoria, $_SESSION['user_email']])) {
          $errors[] = "Erro ao salvar os dados do certificado no banco de dados.";

          @unlink($destination);
        }
      }
    }
  }


  $db->close();


  if (!empty($errors)) {
    redirect_with_toast('../pages/dashboard.php', implode(' ', $errors), 'danger');
  } else {
    redirect_with_toast('../pages/dashboard.php', 'Certificado adicionado com sucesso!', 'success');
  }
} else {

  redirect_with_toast('../index.php', 'Acesso não autorizado', 'danger');
}
