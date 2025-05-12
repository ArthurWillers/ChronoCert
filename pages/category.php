<?php
require_once '../includes/session_start.php';
require_once '../includes/toast.php';
require_once '../private/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  session_unset();
  redirect_with_toast('../index.php', "Você não está logado. Faça login para deletar a conta.");
}

$categories = [
  'Bolsa_Projetos_de_Ensino_e_Extensoes' => 'Bolsa, Projetos de Ensino e Extensões',
  'Ouvinte_em_Eventos_relacionados_ao_Curso' => 'Ouvinte em Eventos relacionados ao Curso',
  'Organizador_em_Eventos_relacionados_ao_Curso' => 'Organizador em Eventos relacionados ao Curso',
  'Voluntario_em_Areas_do_Curso' => 'Voluntário em Áreas do Curso',
  'Estagio_Nao_Obrigatorio' => 'Estágio Não Obrigatório',
  'Publicacao_Apresentacao_e_Premiacao_de_Trabalhos' => 'Publicação, Apresentação e Premiação de Trabalhos',
  'Visitas_e_Viagens_de_Estudo_relacionadas_ao_Curso' => 'Visitas e Viagens de Estudo relacionadas ao Curso',
  'Curso_de_Formacao_na_Area_Especifica' => 'Curso de Formação na Área Específica',
  'Ouvinte_em_apresentacao_de_trabalhos' => 'Ouvinte em apresentação de trabalhos',
  'Curso_de_Linguas' => 'Curso de Línguas',
  'Monitor_em_Areas_do_Curso' => 'Monitor em Áreas do Curso',
  'Participacoes_Artisticas_e_Institucionais' => 'Participações Artísticas e Institucionais',
  'Atividades_Colegiais_Representativas' => 'Atividades Colegiais Representativas',
];

$category_limit = [
  'Bolsa_Projetos_de_Ensino_e_Extensoes' => 40,
  'Ouvinte_em_Eventos_relacionados_ao_Curso' => 60,
  'Organizador_em_Eventos_relacionados_ao_Curso' => 20,
  'Voluntario_em_Areas_do_Curso' => 20,
  'Estagio_Nao_Obrigatorio' => 40,
  'Publicacao_Apresentacao_e_Premiacao_de_Trabalhos' => 20,
  'Visitas_e_Viagens_de_Estudo_relacionadas_ao_Curso' => 30,
  'Curso_de_Formacao_na_Area_Especifica' => 40,
  'Ouvinte_em_apresentacao_de_trabalhos' => 10,
  'Curso_de_Linguas' => 30,
  'Monitor_em_Areas_do_Curso' => 30,
  'Participacoes_Artisticas_e_Institucionais' => 20,
  'Atividades_Colegiais_Representativas' => 20
];

$category = $_GET['category'] ?? null;
if ($category === null) {
  redirect_with_toast('./dashboard.php', "Categoria não especificada.");
}

if (!array_key_exists($category, $categories)) {
  redirect_with_toast('./dashboard.php', "Categoria inválida.");
}
$category_name = $categories[$category];
$category_limit = $category_limit[$category];

$user_email = $_SESSION['user_email'];
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
        <span class="ms-2 align-middle">ChronoCert</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar_content" aria-controls="navbar_content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar_content">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 flex-column flex-lg-row">
          <li class="nav-item mb-2 mb-lg-0 me-lg-3">
            <a class="btn btn-outline-light mb-2 mb-lg-0" href="#" data-bs-toggle="modal" data-bs-target="#add_certificate_modal">
              Adicionar Certificado
            </a>
            <a class="btn btn-outline-light mb-2 mb-lg-0 ms-lg-2" href="../actions/download_certificates.php">
              Baixar Certificados
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

    $db = new db_connection();
    $conn = $db->get_connection();

    $sql = "SELECT SUM(carga_horaria) AS total 
            FROM certificado 
            WHERE fk_usuario_email = ? 
            AND FIND_IN_SET(?, categoria) > 0";

    try {
      $result = $conn->execute_query($sql, [$user_email, $category]);
      $row = $result->fetch_assoc();
      $total_hours = (float)($row['total'] ?? 0);


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
              <h5 class='card-title fw-bold mb-3'>" . htmlspecialchars($category_name) . "</h5>
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
      $db = new db_connection();
      $conn = $db->get_connection();


      $sql = "SELECT nome_do_arquivo, nome_pessoal, carga_horaria 
              FROM certificado 
              WHERE fk_usuario_email = ? 
              AND FIND_IN_SET(?, categoria) > 0
              ORDER BY nome_pessoal";

      try {
        $result = $conn->execute_query($sql, [$user_email, $category]);

        if ($result->num_rows > 0) {
          while ($cert = $result->fetch_assoc()) {
            $file_name = htmlspecialchars($cert['nome_do_arquivo']);
            $display_name = htmlspecialchars($cert['nome_pessoal']);
            $hours = number_format($cert['carga_horaria'], 1);

            echo "
            <div class='col-md-6 col-lg-4 col-xl-3 mb-4'>
              <div class='card h-100 shadow-lg'>
                <div class='card-body text-center'>
                  <h5 class='card-title text-truncate' title='$display_name'>$display_name</h5>
                  <p class='card-text mb-2'>Carga horária: $hours h</p>
                  <div class='d-flex justify-content-center gap-5'>
                    <a href='../actions/download_certificate.php?filename=$file_name' class='btn btn-sm btn-primary'>
                      <i class='bi bi-cloud-download'></i> Download
                    </a>
                    <button class='btn btn-sm btn-danger' data-bs-toggle='modal' 
                            data-bs-target='#deleteModal' 
                            data-file='$file_name' 
                            data-name='$display_name'>
                      <i class='bi bi-trash'></i> Excluir
                    </button>
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
            <input type="hidden" name="redirect" value="category.php?category=<?php echo urlencode($category); ?>">
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
                <option selected disabled value="">Selecione a categoria do certificado</option>
                <?php foreach ($categories as $valor => $nome): ?>
                  <option value="<?php echo htmlspecialchars($valor); ?>">
                    <?php echo htmlspecialchars($nome); ?>
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