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

$sql_hours_by_category = "SELECT c.fk_categoria_id, cat.nome as categoria_nome, SUM(c.carga_horaria) as total_horas 
                          FROM certificado c 
                          INNER JOIN categoria cat ON c.fk_categoria_id = cat.id 
                          WHERE c.fk_usuario_email = ? 
                          AND c.status = 'válido'
                          GROUP BY c.fk_categoria_id, cat.nome";
$hours_by_category_result = $conn->execute_query($sql_hours_by_category, [$student_email]);

// Get total hours and max possible hours
$sql_total = "SELECT 
  (SELECT SUM(carga_horaria) FROM certificado WHERE fk_usuario_email = ? AND status = 'válido') as total_horas,
  (SELECT SUM(carga_maxima) FROM categoria WHERE fk_curso_id = (SELECT fk_curso_id FROM usuario WHERE email = ?)) as total_horas_maximas";
$total_result = $conn->execute_query($sql_total, [$student_email, $student_email]);
$total_hours = 0;
$total_max_hours = 0;
if ($total_result && $total_result->num_rows > 0) {
  $total_row = $total_result->fetch_assoc();
  $total_hours = $total_row['total_horas'] ?? 0;
  $total_max_hours = $total_row['total_horas_maximas'] ?? 0;
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
      <div class="ms-auto">
        <a class="btn btn-outline-light" href="coordinator_dashboard.php">
          <i class="bi bi-arrow-left me-1"></i>Voltar ao Dashboard
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-4 px-4">
    <!-- Header Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="text-dark fw-bold mb-0">
            <i class="bi bi-person-circle text-primary me-2"></i>
            Detalhes do Aluno: <?= htmlspecialchars($student['nome_de_usuario']) ?>
          </h2>
          <span class="badge bg-primary fs-5 px-3 py-2">
            <i class="bi bi-clock me-1"></i>
            <?= number_format($total_hours, 1) ?>/<?= number_format($total_max_hours, 1) ?> horas
          </span>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Student Info Card -->
      <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
              <i class="bi bi-info-circle me-2"></i>Informações do Aluno
            </h5>
          </div>
          <div class="card-body">
            <div class="text-center mb-4">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-person fs-1 text-white"></i>
              </div>
              <h4 class="text-primary fw-bold"><?= htmlspecialchars($student['nome_de_usuario']) ?></h4>
            </div>

            <div class="list-group list-group-flush">
              <div class="list-group-item d-flex align-items-center px-0">
                <i class="bi bi-envelope text-primary me-3"></i>
                <div>
                  <strong>Email:</strong><br>
                  <span class="text-muted"><?= htmlspecialchars($student['email']) ?></span>
                </div>
              </div>
              <div class="list-group-item d-flex align-items-center px-0">
                <i class="bi bi-clock text-success me-3"></i>
                <div>
                  <strong>Total de Horas:</strong><br>
                  <span class="badge bg-success fs-6"><?= number_format($total_hours, 1) ?>/<?= number_format($total_max_hours, 1) ?> horas</span>
                </div>
              </div>
              <div class="list-group-item d-flex align-items-center px-0">
                <i class="bi bi-person-badge text-info me-3"></i>
                <div>
                  <strong>Tipo de Conta:</strong><br>
                  <span class="badge bg-info"><?= ucfirst($student['tipo_de_conta']) ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Hours by Category and Certificates -->
      <div class="col-lg-8">
        <?php if ($hours_by_category_result && $hours_by_category_result->num_rows > 0): ?>
          <!-- Hours by Category Card -->
          <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
              <h5 class="mb-0">
                <i class="bi bi-pie-chart me-2"></i>Horas por Categoria
              </h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="border-0 text-success fw-semibold">
                        <i class="bi bi-tag me-1"></i>Categoria
                      </th>
                      <th class="border-0 text-success fw-semibold text-center">
                        <i class="bi bi-clock me-1"></i>Total de Horas
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($category_hours = $hours_by_category_result->fetch_assoc()): ?>
                      <tr>
                        <td class="align-middle">
                          <div class="d-flex align-items-center">
                            <i class="bi bi-tag-fill text-success me-2"></i>
                            <span class="fw-semibold"><?= htmlspecialchars(str_replace('_', ' ', $category_hours['categoria_nome'] ?? '')) ?></span>
                          </div>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge bg-success fs-6"><?= number_format($category_hours['total_horas'] ?? 0, 1) ?> horas</span>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Certificates Card -->
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0 text-dark fw-semibold">
                <i class="bi bi-award text-primary me-2"></i>
                Certificados do Aluno
              </h5>
              <span class="badge bg-primary fs-6">
                <?= $certificates_result ? $certificates_result->num_rows : 0 ?> certificados
              </span>
            </div>
          </div>
          <div class="card-body p-0">
            <?php if ($certificates_result && $certificates_result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="border-0 text-primary fw-semibold">
                        <i class="bi bi-person me-1"></i>Nome Pessoal
                      </th>
                      <th class="border-0 text-primary fw-semibold">
                        <i class="bi bi-tag me-1"></i>Categoria
                      </th>
                      <th class="border-0 text-primary fw-semibold text-center">
                        <i class="bi bi-check-circle me-1"></i>Status
                      </th>
                      <th class="border-0 text-primary fw-semibold text-center">
                        <i class="bi bi-clock me-1"></i>Carga Horária
                      </th>
                      <th class="border-0 text-primary fw-semibold text-center">
                        <i class="bi bi-gear me-1"></i>Ações
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($certificate = $certificates_result->fetch_assoc()):
                      $sql_cat = "SELECT nome FROM categoria WHERE id = ?";
                      $cat_result = $conn->execute_query($sql_cat, [$certificate['fk_categoria_id']]);
                      $categoria_nome = $cat_result && $cat_result->num_rows > 0 ? $cat_result->fetch_assoc()['nome'] : '';
                      if ($cat_result) $cat_result->free();

                      $status_color = $certificate['status'] === 'válido' ? 'success' : ($certificate['status'] === 'incerto' ? 'warning' : 'danger');
                      $status_text = ucfirst(str_replace('_', ' ', $certificate['status'] ?? ''));
                    ?>
                      <tr>
                        <td class="align-middle">
                          <i class="bi bi-person-circle text-primary me-1"></i>
                          <?= htmlspecialchars($certificate['nome_pessoal']) ?>
                        </td>
                        <td class="align-middle">
                          <span class="badge bg-info fs-6">
                            <i class="bi bi-tag me-1"></i>
                            <?= htmlspecialchars(str_replace('_', ' ', $categoria_nome)) ?>
                          </span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge bg-<?= $status_color ?> fs-6"><?= $status_text ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge bg-secondary fs-6"><?= number_format($certificate['carga_horaria'], 1) ?>h</span>
                        </td>
                        <td class="align-middle text-center">
                          <div class="btn-group" role="group">
                            <a href="../actions/download_certificate.php?filename=<?= urlencode($certificate['nome_do_arquivo']) ?>"
                              class="btn btn-sm btn-outline-primary" title="Download">
                              <i class="bi bi-download"></i>
                            </a>
                            <div class="btn-group btn-group-sm" role="group">
                              <button class="btn btn-outline-success"
                                onclick="updateStatus('<?= $certificate['nome_do_arquivo'] ?>', 'válido')"
                                title="Marcar como Válido">
                                <i class="bi bi-check-circle"></i>
                              </button>
                              <button class="btn btn-outline-warning"
                                onclick="updateStatus('<?= $certificate['nome_do_arquivo'] ?>', 'incerto')"
                                title="Marcar como Incerto">
                                <i class="bi bi-question-circle"></i>
                              </button>
                              <button class="btn btn-outline-danger"
                                onclick="showDeleteCertificateModal('<?= $certificate['nome_do_arquivo'] ?>', '<?= htmlspecialchars($certificate['nome_pessoal']) ?>')"
                                title="Remover Certificado">
                                <i class="bi bi-trash"></i>
                              </button>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                  <h5>Nenhum certificado cadastrado</h5>
                  <p class="mb-0">Este aluno ainda não possui certificados cadastrados no sistema.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Certificate Confirmation Modal -->
  <div class="modal fade" id="delete_certificate_modal" tabindex="-1" aria-labelledby="delete_certificate_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="delete_certificate_modal_label">
            <i class="bi bi-exclamation-triangle me-2"></i>Excluir Certificado
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="alert alert-danger d-flex align-items-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 me-3 mt-1"></i>
            <div>
              <h5 class="alert-heading mb-2">⚠️ Atenção! Esta ação é irreversível.</h5>
              <p class="mb-0">
                Ao excluir este certificado, ele será <strong>permanentemente removido</strong> do sistema e não poderá ser recuperado.
              </p>
            </div>
          </div>

          <div class="card border-danger">
            <div class="card-header bg-danger text-white">
              <h6 class="mb-0"><i class="bi bi-trash me-2"></i>O que será removido:</h6>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0">
                <li class="mb-2">
                  <i class="bi bi-x-circle text-danger me-2"></i>
                  <strong>Certificado:</strong> <span id="certificate-name-display"></span>
                </li>
                <li class="mb-2">
                  <i class="bi bi-x-circle text-danger me-2"></i>
                  <strong>Arquivo PDF</strong> do sistema
                </li>
                <li class="mb-0">
                  <i class="bi bi-x-circle text-danger me-2"></i>
                  <strong>Registro</strong> no banco de dados
                </li>
              </ul>
            </div>
          </div>

          <div class="mt-4 text-center">
            <p class="fw-semibold">Tem certeza de que deseja excluir este certificado permanentemente?</p>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancelar
          </button>
          <form action="../actions/delete_certificate.php" method="POST" class="d-inline spinner-trigger">
            <input type="hidden" name="file_name" id="certificate-file-input">
            <input type="hidden" name="redirect" value="student_details.php?email=<?php echo urlencode($student_email); ?>">
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-trash me-1"></i>Excluir Certificado
            </button>
          </form>
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
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
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

    function showDeleteCertificateModal(fileName, displayName) {
      // Set the certificate information in the modal
      document.getElementById('certificate-name-display').textContent = displayName;
      document.getElementById('certificate-file-input').value = fileName;

      // Show the modal
      const modal = new bootstrap.Modal(document.getElementById('delete_certificate_modal'));
      modal.show();
    }
  </script>

</body>

</html>

<?php
if ($certificates_result) $certificates_result->free();
if ($hours_by_category_result) $hours_by_category_result->free();
$db->close_connection();
?>