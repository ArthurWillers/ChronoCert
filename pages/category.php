<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  session_unset();
  redirect_with_toast('../index.php', "Você não está logado. Faça login para deletar a conta.");
}

// Redirect coordinators to their specific dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'coordenador') {
  header('Location: coordinator_dashboard.php');
  exit();
}

$category_id = $_GET['category'] ?? null;
if ($category_id === null) {
  redirect_with_toast('./dashboard.php', "Categoria não especificada.");
}

$db = new db_connection();
$conn = $db->get_connection();

// Get category info from database
$sql_category = "SELECT * FROM categoria WHERE id = ?";
$category_result = $conn->execute_query($sql_category, [$category_id]);

if (!$category_result || $category_result->num_rows === 0) {
  if ($category_result) $category_result->free();
  $db->close_connection();
  redirect_with_toast('./dashboard.php', "Categoria inválida.");
}

$category_data = $category_result->fetch_assoc();
$category_result->free();

$category_name = $category_data['nome'];
$category_limit = 40;

$user_email = $_SESSION['user_email'];

$sql_user_course = "SELECT fk_curso_id FROM usuario WHERE email = ?";
$user_course_result = $conn->execute_query($sql_user_course, [$user_email]);
$user_course_id = null;

if ($user_course_result && $user_course_result->num_rows > 0) {
  $user_data = $user_course_result->fetch_assoc();
  $user_course_id = $user_data['fk_curso_id'];
}

if ($user_course_result) $user_course_result->free();
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../assets/img/ChronoCert_logo.svg">
  <?php include '../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../assets/css/bootstrap_custom.css">
  <title>Categoria - ChronoCert</title>
</head>

<body>
  <?php render_toast(); ?>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="./dashboard.php">
        <img src="../assets/img/ChronoCert_logo_white.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle fw-bold">ChronoCert</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar_content" aria-controls="navbar_content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar_content">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 flex-column flex-lg-row">
          <li class="nav-item mb-2 mb-lg-0 me-lg-3">
            <a class="btn btn-outline-light mb-2 mb-lg-0" href="./dashboard.php">
              Voltar ao Dashboard
            </a>
            <a class="btn btn-outline-light mb-2 mb-lg-0" href="#" data-bs-toggle="modal" data-bs-target="#add_certificate_modal">
              <i class="bi bi-cloud-upload"></i> Adicionar Certificado
            </a>
            <a class="btn btn-outline-light mb-2 mb-lg-0 ms-lg-2" href="../actions/download_certificates.php">
              <i class="bi bi-cloud-download"></i> Baixar Certificados
            </a>
          </li>
          <li class="nav-item dropdown ">
            <a class="nav-link dropdown-toggle p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end " style="right:0; left:auto;">
              <li><a class="dropdown-item" href="../actions/logout.php">Deslogar</a></li>
              <li><a href="./dashboard.php" class="dropdown-item">Dashboard</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid pb-5">

    <?php

    $sql = "SELECT SUM(carga_horaria) AS total 
            FROM certificado 
            WHERE fk_usuario_email = ? 
            AND fk_categoria_id = ?";

    try {
      $result = $conn->execute_query($sql, [$user_email, $category_id]);
      $row = $result->fetch_assoc();
      $total_hours = (float)($row['total'] ?? 0);

      if ($result) $result->free();

      if ($total_hours > $category_limit) {
        $total_hours = $category_limit;
      }


      $percentage = ($category_limit > 0) ? floor(($total_hours / $category_limit) * 100) : 0;
      if ($percentage > 100) {
        $percentage = 100;
      }


      $progress_color = "bg-primary";
      if ($percentage >= 100) {
        $progress_color = "bg-success";
      } elseif ($percentage >= 75) {
        $progress_color = "bg-info";
      } elseif ($percentage >= 50) {
        $progress_color = "bg-warning";
      } elseif ($percentage > 0) {
        $progress_color = "bg-danger";
      }


      echo "
      <div class='row my-4'>
        <div class='col-12'>
          <div class='card text-center shadow-lg'>
            <div class='card-body p-4'>
              <h5 class='card-title fw-bold mb-3'>" . htmlspecialchars(str_replace('_', ' ', $category_name)) . "</h5>
              <div class='position-relative mb-2'>
                <div class='fw-bold mb-2 fs-5'>{$total_hours}/{$category_limit}h</div>
                <div class='progress' style='height: 25px;'>
                  <div class='progress-bar {$progress_color} progress-bar-striped progress-bar-animated' 
                       role='progressbar' style='width:{$percentage}%'>
                    {$percentage}%
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>";
    } catch (Exception $e) {
      echo "<div class='alert alert-danger'>Erro ao calcular o total de horas.</div>";
    }
    ?>

    <div class="row">
      <?php

      $sql = "SELECT nome_do_arquivo, nome_pessoal, carga_horaria, status 
              FROM certificado 
              WHERE fk_usuario_email = ? 
              AND fk_categoria_id = ?";

      try {
        $result = $conn->execute_query($sql, [$user_email, $category_id]);

        if ($result->num_rows > 0) {
          while ($cert = $result->fetch_assoc()) {
            $file_name = htmlspecialchars($cert['nome_do_arquivo']);
            $display_name = htmlspecialchars($cert['nome_pessoal']);
            $hours = number_format($cert['carga_horaria'], 1);
            $status_color = $cert['status'] === 'válido' ? 'success' : ($cert['status'] === 'incerto' ? 'warning' : 'danger');
            $status_text = ucfirst(str_replace('_', ' ', $cert['status']));

            echo "
            <div class='col-md-6 col-lg-4 col-xl-3 mb-4'>
              <div class='card h-100 shadow-lg'>
                <div class='card-body text-center'>
                  <h5 class='card-title text-truncate' title='$display_name'>$display_name</h5>
                  <p class='card-text mb-2'>Carga horária: $hours h</p>
                  <p class='card-text mb-2'><span class='badge bg-$status_color'>$status_text</span></p>
                  <div class='row g-2'>
                    <div class='col-6'>
                      <a href='../actions/download_certificate.php?filename=$file_name' class='btn btn-sm btn-primary w-100'>
                        <i class='bi bi-cloud-download'></i> Download
                      </a>
                    </div>
                    <div class='col-6'>
                      <button class='btn btn-sm btn-danger w-100' data-bs-toggle='modal' 
                              data-bs-target='#deleteModal' 
                              data-file='$file_name' 
                              data-name='$display_name'>
                        <i class='bi bi-trash'></i> Excluir
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>";
          }
        } else {
          echo "<div class='col-12'><div class='alert alert-info text-center'>Nenhum certificado encontrado para esta categoria.</div></div>";
        }
      } catch (Exception $e) {
        echo "<div class='col-12'><div class='alert alert-danger'>Erro ao buscar certificados.</div></div>";
      }
      ?>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirmar exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Tem certeza que deseja excluir o certificado <span id="cert-name"></span>?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form action="../actions/delete_certificate.php" method="POST">
            <input type="hidden" name="file_name" id="file-to-delete">
            <input type="hidden" name="redirect" value="category.php?category=<?php echo urlencode($category_id); ?>">
            <button type="submit" class="btn btn-danger">Excluir</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="add_certificate_modal" tabindex="-1" aria-labelledby="add_certificate_modal_label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="add_certificate_modal_label">Adicionar Certificado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="../actions/add_certificate.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" class="form-control" name="nome_pessoal" placeholder="Digite o nome que consta no certificado" maxlength="255" required>
            </div>

            <div class="mb-3">
              <label class="form-label" for="carga_horaria">Carga Horária (h)</label>
              <input type="number" class="form-control" id="carga_horaria" name="carga_horaria" placeholder="Carga horária" step="0.01" min="0" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Categoria</label>
              <select name="categoria" class="form-select" required>
                <option disabled value="">Selecione a categoria do certificado</option>
                <?php 
                $sql_all_categories = "SELECT * FROM categoria WHERE fk_curso_id = ? ORDER BY nome";
                $all_categories_result = $conn->execute_query($sql_all_categories, [$user_course_id]);
                
                if ($all_categories_result && $all_categories_result->num_rows > 0) {
                  while ($cat = $all_categories_result->fetch_assoc()) {
                    $selected = ($cat['id'] == $category_id) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($cat['id']) . "' $selected>";
                    echo htmlspecialchars(str_replace('_', ' ', $cat['nome']));
                    echo "</option>";
                  }
                  $all_categories_result->free();
                }
                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="arquivo_certificado" class="form-label">Arquivo do Certificado (PDF)</label>
              <input id="arquivo_certificado" name="arquivo_certificado" type="file" class="form-control" accept=".pdf" required>
              <div class="form-text">Tamanho máximo: 10MB. Apenas arquivos PDF.</div>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary" name="submit_certificate" disabled>Salvar Certificado</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php 
  // Close database connection
  $db->close_connection();
  ?>

  <?php include '../includes/spinner.php' ?>
  <script src="../assets/js/spinner.js"></script>
  <?php include '../includes/bootstrap_script.php' ?>
  <script src="../assets/js/toast.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const certModal = document.getElementById('add_certificate_modal');
      const form = document.querySelector("#add_certificate_modal form");
      const submitBtn = form.querySelector('button[name="submit_certificate"]');
      const fileInput = document.getElementById("arquivo_certificado");
      const nomePessoal = form.querySelector('input[name="nome_pessoal"]');
      const cargaHoraria = form.querySelector('input[name="carga_horaria"]');
      const categoria = form.querySelector('select[name="categoria"]');

      const errorMsg = document.createElement("div");
      errorMsg.id = "file_error_message";
      errorMsg.className = "text-danger mt-2";
      fileInput.parentNode.appendChild(errorMsg);

      certModal.addEventListener('show.bs.modal', function() {
        form.reset();
        errorMsg.textContent = "";
        submitBtn.disabled = true;
      });

      function validateForm() {
        const maxSize = 10 * 1024 * 1024; // 10MB 
        let isValid = true;

        errorMsg.textContent = "";

        if (!nomePessoal.value.trim() || !categoria.value || !cargaHoraria.value || cargaHoraria.value <= 0) {
          isValid = false;
        }



        if (!fileInput.files || fileInput.files.length === 0) {
          isValid = false;
        } else {
          const file = fileInput.files[0];

          if (!file.type.match('application/pdf')) {
            errorMsg.textContent = "Erro: Apenas arquivos PDF são permitidos.";
            isValid = false;
          }

          if (file.size > maxSize) {
            errorMsg.textContent = "Erro: O tamanho máximo permitido é 10MB. Seu arquivo tem " +
              (file.size / (1024 * 1024)).toFixed(2) + "MB.";
            isValid = false;
          }
        }

        submitBtn.disabled = !isValid;
      }


      nomePessoal.addEventListener("input", validateForm);
      cargaHoraria.addEventListener("input", validateForm);
      categoria.addEventListener("change", validateForm);
      fileInput.addEventListener("change", validateForm);

      validateForm();
    });

    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        const fileName = button.getAttribute('data-file');
        const certName = button.getAttribute('data-name');

        const modalCertName = deleteModal.querySelector('#cert-name');
        const fileInput = deleteModal.querySelector('#file-to-delete');

        if (modalCertName) modalCertName.textContent = certName;
        if (fileInput) fileInput.value = fileName;
      });
    }
  </script>
</body>

</html>