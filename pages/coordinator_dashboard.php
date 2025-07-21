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

$_SESSION['email_recover_password'] = $_SESSION['user_email'] ?? null;

$db = new db_connection();
$conn = $db->get_connection();

$coordinator_email = $_SESSION['user_email'];
$sql_coordinator = "SELECT fk_curso_id FROM usuario WHERE email = ?";
$coordinator_result = $conn->execute_query($sql_coordinator, [$coordinator_email]);
$coordinator_course_id = null;

if ($coordinator_result && $coordinator_result->num_rows > 0) {
  $coordinator_data = $coordinator_result->fetch_assoc();
  $coordinator_course_id = $coordinator_data['fk_curso_id'];
}

if ($coordinator_result) $coordinator_result->free();

$sql_students = "SELECT 
    nome_de_usuario,
    email,
    (SELECT COALESCE(SUM(carga_horaria), 0) 
     FROM certificado 
     WHERE fk_usuario_email = usuario.email) AS total_horas
FROM usuario
WHERE tipo_de_conta = 'aluno' AND fk_curso_id = ?
ORDER BY nome_de_usuario";

$students_result = $conn->execute_query($sql_students, [$coordinator_course_id]);

$sql_categories = "SELECT * FROM categoria WHERE fk_curso_id = ? ORDER BY nome";
$categories_result = $conn->execute_query($sql_categories, [$coordinator_course_id]);
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../assets/img/ChronoCert_logo.svg">
  <?php include '../includes/bootstrap_styles.php' ?>
  <link rel="stylesheet" href="../assets/css/bootstrap_custom.css">
  <title>Dashboard Coordenador - ChronoCert</title>
</head>

<body>
  <?php render_toast(); ?>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../assets/img/ChronoCert_logo_white.png" alt="Logo" height="35" class="d-inline-block">
        <span class="ms-2 align-middle fw-bold">ChronoCert - Coordenador</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar_content" aria-controls="navbar_content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar_content">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <h6 class="dropdown-header"><?= htmlspecialchars($_SESSION['username']) ?></h6>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="../pages/recover_password/recover_password.php">
                  <i class="bi bi-key me-2"></i>Alterar Senha
                </a></li>
              <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#delete_account_modal">
                  <i class="bi bi-trash me-2"></i>Deletar Conta
                </a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="../actions/logout.php">
                  <i class="bi bi-box-arrow-right me-2"></i>Sair
                </a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-4 px-4">
    <!-- Header Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="text-dark fw-bold mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i>
            Dashboard do Coordenador
          </h2>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#add_student_modal">
              <i class="bi bi-person-plus me-1"></i>
              Cadastrar Aluno
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manage_categories_modal">
              <i class="bi bi-tags me-1"></i>
              Gerenciar Categorias
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <h5 class="card-title mb-1">Total de Alunos</h5>
                <h3 class="mb-0"><?= $students_result ? $students_result->num_rows : 0 ?></h3>
              </div>
              <div class="fs-1 opacity-50">
                <i class="bi bi-people"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <h5 class="card-title mb-1">Categorias Ativas</h5>
                <h3 class="mb-0"><?= $categories_result ? $categories_result->num_rows : 0 ?></h3>
              </div>
              <div class="fs-1 opacity-50">
                <i class="bi bi-tags"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Students Table -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0 text-dark fw-semibold">
                <i class="bi bi-list-ul text-primary me-2"></i>
                Lista de Alunos e Horas Cumpridas
              </h5>
              <span class="badge bg-primary fs-6"><?= $students_result ? $students_result->num_rows : 0 ?> alunos</span>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="border-0 text-primary fw-semibold">
                      <i class="bi bi-person me-1"></i>Nome de Usuário
                    </th>
                    <th class="border-0 text-primary fw-semibold">
                      <i class="bi bi-envelope me-1"></i>Email
                    </th>
                    <th class="border-0 text-primary fw-semibold text-center">
                      <i class="bi bi-clock me-1"></i>Total de Horas
                    </th>
                    <th class="border-0 text-primary fw-semibold text-center">
                      <i class="bi bi-gear me-1"></i>Ações
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($students_result && $students_result->num_rows > 0): ?>
                    <?php while ($student = $students_result->fetch_assoc()): ?>
                      <tr>
                        <td class="align-middle">
                          <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                              <i class="bi bi-person text-white"></i>
                            </div>
                            <strong><?= htmlspecialchars($student['nome_de_usuario']) ?></strong>
                          </div>
                        </td>
                        <td class="align-middle text-muted">
                          <i class="bi bi-envelope me-1"></i>
                          <?= htmlspecialchars($student['email']) ?>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge bg-info fs-6 px-3 py-2">
                            <?= number_format($student['total_horas'], 1) ?> horas
                          </span>
                        </td>
                        <td class="align-middle text-center">
                          <div class="btn-group" role="group">
                            <a href="student_details.php?email=<?= urlencode($student['email']) ?>"
                              class="btn btn-sm btn-outline-primary" title="Ver Detalhes">
                              <i class="bi bi-eye me-1"></i>Detalhes
                            </a>
                            <button class="btn btn-sm btn-outline-danger"
                              onclick="deleteStudent('<?= htmlspecialchars($student['email']) ?>')"
                              title="Excluir Aluno">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="text-center py-5">
                        <div class="text-muted">
                          <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                          <h5>Nenhum aluno cadastrado</h5>
                          <p class="mb-3">Comece cadastrando o primeiro aluno do seu curso.</p>
                          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_student_modal">
                            <i class="bi bi-person-plus me-1"></i>
                            Cadastrar Primeiro Aluno
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Student Modal -->
  <div class="modal fade" id="add_student_modal" tabindex="-1" aria-labelledby="add_student_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="add_student_modal_label">
            <i class="bi bi-person-plus me-2"></i>Cadastrar Novo Aluno
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="../actions/register_student.php">
          <div class="modal-body p-4">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="username_register" class="form-label fw-semibold">
                  <i class="bi bi-person text-primary me-1"></i>Nome de Usuário
                </label>
                <input type="text" name="username_register" class="form-control form-control-lg"
                  placeholder="Digite o nome de usuário" maxlength="255" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label fw-semibold">
                  <i class="bi bi-envelope text-primary me-1"></i>Email
                </label>
                <input type="email" name="email" class="form-control form-control-lg"
                  placeholder="Digite o email" maxlength="255" required>
              </div>
            </div>
            <input type="hidden" name="curso_id" value="<?= $coordinator_course_id ?>">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="password_register" class="form-label fw-semibold">
                  <i class="bi bi-lock text-primary me-1"></i>Senha
                </label>
                <div class="input-group input-group-lg">
                  <input id="password_register" type="password" name="password_register"
                    class="form-control" placeholder="Digite a senha" required>
                  <button class="btn btn-outline-secondary" type="button"
                    onclick="toggle_password_visibility('password_register', this)">
                    <i class="bi bi-eye-slash"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="confirm_password_register" class="form-label fw-semibold">
                  <i class="bi bi-lock-fill text-primary me-1"></i>Confirmar Senha
                </label>
                <div class="input-group input-group-lg">
                  <input id="confirm_password_register" type="password" name="confirm_password_register"
                    class="form-control" placeholder="Confirme a senha" required>
                  <button class="btn btn-outline-secondary" type="button"
                    onclick="toggle_password_visibility('confirm_password_register', this)">
                    <i class="bi bi-eye-slash"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="alert alert-info d-flex align-items-center" role="alert">
              <i class="bi bi-info-circle-fill me-2"></i>
              <div>
                O aluno receberá essas credenciais para acessar o sistema e gerenciar seus certificados.
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i>Cancelar
            </button>
            <button type="submit" name="submit_register" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i>Cadastrar Aluno
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Manage Categories Modal -->
  <div class="modal fade" id="manage_categories_modal" tabindex="-1" aria-labelledby="manage_categories_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="manage_categories_modal_label">
            <i class="bi bi-tags me-2"></i>Gerenciar Categorias
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <!-- Add Category Section -->
          <div class="mb-4">
            <h6 class="mb-3">
              <i class="bi bi-plus-circle text-success me-2"></i>Adicionar Nova Categoria
            </h6>
            <form method="POST" action="../actions/add_category.php" class="row g-3">
              <div class="col-md-8">
                <input type="text" name="category_name" class="form-control form-control-lg"
                  placeholder="Digite o nome da nova categoria" required>
                <div class="form-text">
                  <i class="bi bi-info-circle me-1"></i>
                  Use underscores para separar palavras (ex: atividade_complementar)
                </div>
              </div>
              <div class="col-md-4 d-flex align-items-start">
                <button type="submit" name="add_category" class="btn btn-success btn-lg w-100">
                  <i class="bi bi-plus-circle me-1"></i>Adicionar
                </button>
              </div>
            </form>
          </div>

          <hr>

          <!-- Existing Categories Section -->
          <div class="card">
            <div class="card-header bg-light">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-dark">
                  <i class="bi bi-list-ul me-2"></i>Categorias Existentes
                </h6>
                <span class="badge bg-primary fs-6">
                  <?= $categories_result ? $categories_result->num_rows : 0 ?> categorias
                </span>
              </div>
            </div>
            <div class="card-body p-0">
              <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th class="border-0 text-primary fw-semibold" style="width: 80px;">
                          <i class="bi bi-hash me-1"></i>ID
                        </th>
                        <th class="border-0 text-primary fw-semibold">
                          <i class="bi bi-tag me-1"></i>Nome da Categoria
                        </th>
                        <th class="border-0 text-primary fw-semibold text-center" style="width: 200px;">
                          <i class="bi bi-gear me-1"></i>Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $categories_result->data_seek(0);
                      while ($category = $categories_result->fetch_assoc()):
                      ?>
                        <tr>
                          <td class="align-middle">
                            <span class="badge bg-light text-dark fs-6"><?= $category['id'] ?></span>
                          </td>
                          <td class="align-middle">
                            <span id="category_name_<?= $category['id'] ?>" class="fw-semibold">
                              <i class="bi bi-tag-fill text-primary me-2"></i>
                              <?= htmlspecialchars(str_replace('_', ' ', $category['nome'])) ?>
                            </span>
                            <input type="text" id="category_edit_<?= $category['id'] ?>"
                              value="<?= htmlspecialchars($category['nome']) ?>"
                              class="form-control d-none">
                          </td>
                          <td class="align-middle text-center">
                            <button class="btn btn-sm btn-outline-warning rounded-pill me-1" onclick="editCategory(<?= $category['id'] ?>)"
                              title="Editar categoria">
                              <i class="bi bi-pencil me-1"></i>Editar
                            </button>
                            <button class="btn btn-sm btn-success rounded-pill me-1 d-none" onclick="saveCategory(<?= $category['id'] ?>)"
                              title="Salvar alterações">
                              <i class="bi bi-check me-1"></i>Salvar
                            </button>
                            <button class="btn btn-sm btn-secondary rounded-pill me-1 d-none" onclick="cancelEdit(<?= $category['id'] ?>)"
                              title="Cancelar edição">
                              <i class="bi bi-x me-1"></i>Cancelar
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteCategory(<?= $category['id'] ?>)"
                              title="Excluir categoria">
                              <i class="bi bi-trash"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="text-center py-5">
                  <div class="text-muted">
                    <i class="bi bi-tags display-4 d-block mb-3 opacity-50"></i>
                    <h5>Nenhuma categoria cadastrada</h5>
                    <p class="mb-0">Adicione a primeira categoria usando o formulário acima.</p>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Fechar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Account Modal -->
  <div class="modal fade" id="delete_account_modal" tabindex="-1" aria-labelledby="delete_account_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="delete_account_modal_label">
            <i class="bi bi-exclamation-triangle me-2"></i>Deletar Conta
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="alert alert-warning d-flex align-items-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 me-3 mt-1"></i>
            <div>
              <h5 class="alert-heading mb-2">⚠️ Atenção! Esta ação é irreversível.</h5>
              <p class="mb-0">
                Ao deletar sua conta, apenas seus dados pessoais serão <strong>permanentemente removidos</strong> do sistema.
                Os alunos e dados do curso permanecerão no sistema.
              </p>
            </div>
          </div>

          <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
              <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>O que será removido:</h6>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0">
                <li class="mb-2"><i class="bi bi-check2 text-warning me-2"></i>Sua conta de coordenador</li>
                <li class="mb-2"><i class="bi bi-check2 text-warning me-2"></i>Seus dados pessoais (nome, email, senha)</li>
              </ul>
            </div>
          </div>

          <div class="card border-success mt-3">
            <div class="card-header bg-success text-white">
              <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>O que será preservado:</h6>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0">
                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Todos os alunos cadastrados</li>
                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Todas as categorias do curso</li>
                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Certificados dos alunos</li>
                <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i>Histórico completo do curso</li>
              </ul>
            </div>
          </div>

          <div class="mt-4 text-center">
            <p class="fw-semibold">Tem certeza de que deseja deletar apenas sua conta?</p>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancelar
          </button>
          <form method="POST" action="../actions/delete_account.php" class="d-inline">
            <button type="submit" name="delete_submit" class="btn btn-warning">
              <i class="bi bi-person-x me-1"></i>Deletar Minha Conta
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
  <script src="../assets/js/toggle_password_visibility.js"></script>

  <script>
    function deleteStudent(email) {
      if (confirm('Tem certeza de que deseja excluir este aluno? Esta ação não pode ser desfeita.')) {
        window.location.href = '../actions/delete_student.php?email=' + encodeURIComponent(email);
      }
    }

    function editCategory(id) {
      document.getElementById('category_name_' + id).classList.add('d-none');
      document.getElementById('category_edit_' + id).classList.remove('d-none');

      const row = document.getElementById('category_name_' + id).closest('tr');
      const buttons = row.querySelectorAll('button');
      buttons[0].classList.add('d-none'); // Edit button
      buttons[1].classList.remove('d-none'); // Save button
      buttons[2].classList.remove('d-none'); // Cancel button
      buttons[3].classList.add('d-none'); // Delete button
    }

    function cancelEdit(id) {
      document.getElementById('category_name_' + id).classList.remove('d-none');
      document.getElementById('category_edit_' + id).classList.add('d-none');

      const row = document.getElementById('category_name_' + id).closest('tr');
      const buttons = row.querySelectorAll('button');
      buttons[0].classList.remove('d-none'); // Edit button
      buttons[1].classList.add('d-none'); // Save button
      buttons[2].classList.add('d-none'); // Cancel button
      buttons[3].classList.remove('d-none'); // Delete button
    }

    function saveCategory(id) {
      const newName = document.getElementById('category_edit_' + id).value;

      const formData = new FormData();
      formData.append('category_id', id);
      formData.append('category_name', newName);
      formData.append('update_category', 'true');

      fetch('../actions/update_category.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('category_name_' + id).textContent = newName.replace(/_/g, ' ');
            cancelEdit(id);
            location.reload(); // Reload to show success message
          } else {
            alert('Erro ao atualizar categoria: ' + data.message);
          }
        })
        .catch(error => {
          alert('Erro ao atualizar categoria');
          console.error('Error:', error);
        });
    }

    function deleteCategory(id) {
      if (confirm('Tem certeza de que deseja excluir esta categoria? Esta ação não pode ser desfeita.')) {
        window.location.href = '../actions/delete_category.php?id=' + id;
      }
    }
  </script>

</body>

</html>

<?php
if ($students_result) $students_result->free();
if ($categories_result) $categories_result->free();
$db->close_connection();
?>