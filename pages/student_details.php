<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  session_unset();
  redirect_with_toast('../index.php', "Você não está logado.");
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'coordenador') {
  redirect_with_toast('../pages/dashboard.php', "Acesso negado. Você não é um coordenador.");
}

$student_email = $_GET['email'] ?? '';

if (empty($student_email)) {
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Email do aluno não fornecido');
}

$db = new db_connection();
$conn = $db->get_connection();

// Get student info
$sql_student = "SELECT * FROM usuario WHERE email = ? AND tipo_de_conta = 'aluno'";
$student_result = $conn->execute_query($sql_student, [$student_email]);

if (!$student_result || $student_result->num_rows === 0) {
    if ($student_result) $student_result->free();
    $db->close_connection();
    redirect_with_toast('../pages/coordinator_dashboard.php', 'Aluno não encontrado');
}

$student = $student_result->fetch_assoc();
$student_result->free();

// Get student certificates with category names
$sql_certificates = "SELECT nome_do_arquivo, nome_pessoal, carga_horaria, status, fk_categoria_id FROM certificado WHERE fk_usuario_email = ?";

$certificates_result = $conn->execute_query($sql_certificates, [$student_email]);

$sql_hours_by_category = "SELECT fk_categoria_id, SUM(carga_horaria) as total_horas FROM certificado WHERE fk_usuario_email = ?";
$hours_by_category_result = $conn->execute_query($sql_hours_by_category, [$student_email]);

// Get total hours
$sql_total = "SELECT SUM(carga_horaria) as total_horas FROM certificado WHERE fk_usuario_email = ?";
$total_result = $conn->execute_query($sql_total, [$student_email]);
$total_hours = 0;
if ($total_result && $total_result->num_rows > 0) {
    $total_row = $total_result->fetch_assoc();
    $total_hours = $total_row['total_horas'] ?? 0;
    $total_result->free();
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../assets/img/ChronoCert_logo.svg">
  <?php include '../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../assets/css/bootstrap_custom.css">
  <title>Detalhes do Aluno - ChronoCert</title>
</head>

<body>
  <?php render_toast(); ?>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../assets/img/ChronoCert_logo_white.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle fw-bold">ChronoCert - Detalhes do Aluno</span>
      </a>
      <a class="btn btn-outline-light" href="coordinator_dashboard.php">Voltar</a>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">
            <h3>Informações do Aluno</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p><strong>Nome:</strong> <?= htmlspecialchars($student['nome_de_usuario']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
              </div>
              <div class="col-md-6">
                <p><strong>Total de Horas:</strong> <span class="badge bg-primary fs-6"><?= number_format($total_hours, 1) ?> horas</span></p>
                <p><strong>Tipo de Conta:</strong> <?= ucfirst($student['tipo_de_conta']) ?></p>
              </div>
            </div>
          </div>
        </div>

        <?php if ($hours_by_category_result && $hours_by_category_result->num_rows > 0): ?>
        <div class="card mb-4">
          <div class="card-header">
            <h4>Horas por Categoria</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Categoria</th>
                    <th>Total de Horas</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($category_hours = $hours_by_category_result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars(str_replace('_', ' ', $category_hours['categoria_nome'])) ?></td>
                      <td><?= number_format($category_hours['total_horas'], 1) ?> horas</td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Certificados</h4>
            <span class="badge bg-secondary"><?= $certificates_result ? $certificates_result->num_rows : 0 ?> certificados</span>
          </div>
          <div class="card-body">
            <?php if ($certificates_result && $certificates_result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Nome do Arquivo</th>
                      <th>Nome Pessoal</th>
                      <th>Categoria</th>
                      <th>Status</th>
                      <th>Carga Horária</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $certificates = [];
                    if ($certificates_result) {
                      while ($cert = $certificates_result->fetch_assoc()) {
                        $certificates[] = $cert;
                      }
                    }
                    
                    usort($certificates, function($a, $b) {
                      if ($a['status'] === 'não_verificado' && $b['status'] !== 'não_verificado') return -1;
                      if ($a['status'] !== 'não_verificado' && $b['status'] === 'não_verificado') return 1;
                      return 0;
                    });
                    
                    foreach ($certificates as $certificate):
                      $sql_cat = "SELECT nome FROM categoria WHERE id = ?";
                      $cat_result = $conn->execute_query($sql_cat, [$certificate['fk_categoria_id']]);
                      $categoria_nome = '';
                      if ($cat_result && $cat_result->num_rows > 0) {
                        $cat_data = $cat_result->fetch_assoc();
                        $categoria_nome = $cat_data['nome'];
                        $cat_result->free();
                      }
                      
                      $status_color = $certificate['status'] === 'válido' ? 'success' : ($certificate['status'] === 'incerto' ? 'warning' : 'danger');
                      $status_text = ucfirst(str_replace('_', ' ', $certificate['status']));
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($certificate['nome_do_arquivo']) ?></td>
                        <td><?= htmlspecialchars($certificate['nome_pessoal']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars(str_replace('_', ' ', $categoria_nome)) ?></span></td>
                        <td><span class="badge bg-<?= $status_color ?>"><?= $status_text ?></span></td>
                        <td><?= number_format($certificate['carga_horaria'], 1) ?> horas</td>
                        <td>
                          <a href="../actions/download_certificate.php?filename=<?= urlencode($certificate['nome_do_arquivo']) ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i>
                          </a>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-success" onclick="updateStatus('<?= $certificate['nome_do_arquivo'] ?>', 'válido')" title="Válido">V</button>
                            <button class="btn btn-warning" onclick="updateStatus('<?= $certificate['nome_do_arquivo'] ?>', 'incerto')" title="Incerto">I</button>
                            <button class="btn btn-danger" onclick="deleteCertificate('<?= $certificate['nome_do_arquivo'] ?>')" title="Remover">R</button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center text-muted">
                <i class="bi bi-inbox fs-1"></i>
                <p class="mt-2">Este aluno ainda não possui certificados cadastrados.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../includes/spinner.php' ?>
  <script src="../assets/js/spinner.js"></script>
  <?php require_once '../includes/bootstrap_script.php' ?>
  <script src="../assets/js/toast.js"></script>

  <script>
    function updateStatus(filename, status) {
      fetch('../actions/update_certificate_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'filename=' + encodeURIComponent(filename) + '&status=' + status
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Erro ao atualizar status');
        }
      });
    }

    function deleteCertificate(filename) {
      if (confirm('Tem certeza que deseja remover este certificado?')) {
        fetch('../actions/delete_certificate.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'file_name=' + encodeURIComponent(filename)
        })
        .then(() => location.reload());
      }
    }
  </script>

</body>
</html>

<?php
if ($certificates_result) $certificates_result->free();
if ($hours_by_category_result) $hours_by_category_result->free();
$db->close_connection();
?>
