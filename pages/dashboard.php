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

$_SESSION['email_recover_password'] = $_SESSION['user_email'] ?? null;
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../assets/img/ChronoCert_logo.svg">
  <?php include '../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../assets/css/bootstrap_custom.css">
  <title>Dashboard - ChronoCert</title>
</head>

<body>
  <?php render_toast(); ?>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../assets/img/ChronoCert_logo_white.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle fw-bold">ChronoCert</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar_content" aria-controls="navbar_content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar_content">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 flex-column flex-lg-row">
          <li class="nav-item mb-2 mb-lg-0 me-lg-3">
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
              <li><a class="dropdown-item spinner-trigger" href="../actions/recover_password/send_email.php">Alterar Senha</a></li>
              <li><a class="dropdown-item" href="javascript:void(0);" onclick="open_delete_modal('<?php echo htmlspecialchars($_SESSION['user_email']); ?>')">Excluir Conta</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid pb-5">

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mt-3">

      <?php

      $user_email = $_SESSION['user_email'];

      $db = new db_connection();
      $conn = $db->get_connection();

      $sql_user_course = "SELECT fk_curso_id FROM usuario WHERE email = ?";
      $user_course_result = $conn->execute_query($sql_user_course, [$user_email]);
      $user_course_id = null;

      if ($user_course_result && $user_course_result->num_rows > 0) {
        $user_data = $user_course_result->fetch_assoc();
        $user_course_id = $user_data['fk_curso_id'];
      }

      if ($user_course_result) $user_course_result->free();

      $sql_categories = "SELECT * FROM categoria WHERE fk_curso_id = ? ORDER BY nome";
      $categories_result = $conn->execute_query($sql_categories, [$user_course_id]);
      
      $categories = [];
      
      if ($categories_result && $categories_result->num_rows > 0) {
        while ($cat = $categories_result->fetch_assoc()) {
          $categories[$cat['id']] = $cat['nome'];
        }
        $categories_result->free();
      }

      $categories_sum = [];
      foreach ($categories as $cat_id => $cat_name) {
        $limit = 40;
        try {
          $sql = "SELECT SUM(carga_horaria) AS total 
                    FROM certificado 
                    WHERE fk_usuario_email = ? 
                    AND fk_categoria_id = ?";

          $result = $conn->execute_query($sql, [$user_email, $cat_id]);
          $row = $result->fetch_assoc();

          $sum = (float)($row['total'] ?? 0);

          if ($sum > $limit) {
            $sum = $limit;
          }

          $categories_sum[$cat_id] = $sum;
        } catch (Exception $e) {
          $categories_sum[$cat_id] = 0;
        }
      }

      foreach ($categories as $cat_id => $cat_name) {
        $limit = 40;
        $sum = $categories_sum[$cat_id];
        $percentage = ($limit > 0) ? floor(($sum / $limit) * 100) : 0;
        if ($percentage > 100) {
          $percentage = 100;
        }

        $progressColor = "bg-primary";
        if ($percentage >= 100) {
          $progressColor = "bg-success";
        } elseif ($percentage >= 75) {
          $progressColor = "bg-info";
        } elseif ($percentage >= 50) {
          $progressColor = "bg-warning";
        } elseif ($percentage > 0) {
          $progressColor = "bg-danger";
        }

        echo "
        <div class='col align-items-stretch'>
          <a href='category.php?category=" . urlencode($cat_id) . "' class='text-decoration-none'>
            <div class='card text-center shadow-lg h-100'>
              <div class='card-body'>
                <h6 class='card-title fw-bold mb-3'>" . htmlspecialchars(str_replace('_', ' ', $categories[$cat_id])) . "</h6>
                <div class='position-relative mb-2'>
                  <div class='fw-bold mb-1'>{$sum}/{$limit}h</div>
                  <div class='progress'>
                    <div class='progress-bar $progressColor progress-bar-striped progress-bar-animated' 
                         role='progressbar' style='width:{$percentage}%'>{$percentage}%</div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        ";
      }
      ?>

    </div>
  </div>

  <div class="modal fade" id="delete_account_modal" tabindex="-1" aria-labelledby="delete_account_modal_label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="delete_account_modal_label">Excluir Conta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="../actions/delete_account.php" method="POST">
            <div class="mb-3">
              <label class="form-label">Email:</label>
              <input type="text" class="form-control" id="delete_email" name="delete_email" readonly>
            </div>
            <div class="mb-3">
              <label for="delete_confirm_email" class="form-label">Digite o e-mail para confirmar:</label>
              <input type="email" class="form-control" name="delete_confirm_email" id="delete_confirm_email" required>
              <div id="email_feedback" class="form-text text-danger d-none">O e-mail não confere.</div>
            </div>
            <div class="text-end">
              <button type="submit" id="delete_account_btn" class="btn btn-danger" name="delete_submit" disabled>Excluir Conta</button>
            </div>
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
                <option selected disabled value="">Selecione a categoria do certificado</option>
                <?php foreach ($categories as $cat_id => $nome): ?>
                  <option value="<?php echo htmlspecialchars($cat_id); ?>">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $nome)); ?>
                  </option>
                <?php endforeach; ?>
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

  <?php include '../includes/spinner.php'?>
  <script src="../assets/js/spinner.js"></script>
  <?php include '../includes/bootstrap_script.php' ?>
  <script src="../assets/js/toast.js"></script>
  <script>
    function open_delete_modal(email) {
      document.getElementById("email_feedback").classList.add("d-none");
      document.getElementById("delete_confirm_email").value = "";
      document.getElementById("delete_email").value = email;
      const modal = new bootstrap.Modal(document.getElementById("delete_account_modal"));
      modal.show();
    }

    document.getElementById("delete_confirm_email").addEventListener("input", function() {
      const typedEmail = this.value;
      const userEmail = document.getElementById("delete_email").value;
      const feedback = document.getElementById("email_feedback");
      const deleteBtn = document.getElementById("delete_account_btn");

      if (typedEmail === "") {
        feedback.classList.add("d-none");
        deleteBtn.disabled = true;
      } else if (typedEmail === userEmail) {
        feedback.classList.add("d-none");
        deleteBtn.disabled = false;
      } else {
        feedback.classList.remove("d-none");
        deleteBtn.disabled = true;
      }
    });

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
  </script>
</body>

</html>