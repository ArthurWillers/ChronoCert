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
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 flex-column flex-lg-row">
          <li class="nav-item mb-2 mb-lg-0 me-lg-3">
            <a class="btn btn-outline-light mb-2 mb-lg-0" href="#" data-bs-toggle="modal" data-bs-target="#add_student_modal">
              <i class="bi bi-person-plus"></i> Cadastrar Aluno
            </a>
            <a class="btn btn-outline-light mb-2 mb-lg-0 ms-lg-2" href="#" data-bs-toggle="modal" data-bs-target="#manage_categories_modal">
              <i class="bi bi-tags"></i> Gerenciar Categorias
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><h6 class="dropdown-header"><?= htmlspecialchars($_SESSION['username']) ?></h6></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="../pages/recover_password/recover_password.php">Alterar Senha</a></li>
              <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#delete_account_modal">Deletar Conta</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="../actions/logout.php">Sair</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="row">
      <div class="col-12">
        <h2>Lista de Alunos e Horas Cumpridas</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>Nome de Usuário</th>
                <th>Email</th>
                <th>Total de Horas</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($students_result && $students_result->num_rows > 0): ?>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($student['nome_de_usuario']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td><?= number_format($student['total_horas'], 1) ?> horas</td>
                    <td>
                      <a href="student_details.php?email=<?= urlencode($student['email']) ?>" class="btn btn-sm btn-primary">Ver Detalhes</a>
                      <button class="btn btn-sm btn-danger" onclick="deleteStudent('<?= htmlspecialchars($student['email']) ?>')">Excluir</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center">Nenhum aluno cadastrado</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Student Modal -->
  <div class="modal fade" id="add_student_modal" tabindex="-1" aria-labelledby="add_student_modal_label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="add_student_modal_label">Cadastrar Novo Aluno</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="../actions/register_student.php">
          <div class="modal-body">
            <div class="mb-3">
              <label for="username_register" class="form-label">Nome de Usuário</label>
              <input type="text" name="username_register" class="form-control" placeholder="Digite o nome de usuário" maxlength="255" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="Digite o email" maxlength="255" required>
            </div>
            <input type="hidden" name="curso_id" value="<?= $coordinator_course_id ?>">
            <div class="mb-3">
              <label for="password_register" class="form-label">Senha</label>
              <div class="input-group">
                <input id="password_register" type="password" name="password_register" class="form-control" placeholder="Digite a senha" required>
                <button class="btn btn-outline-secondary" type="button" onclick="toggle_password_visibility('password_register', this)">
                  <i class="bi bi-eye-slash"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label for="confirm_password_register" class="form-label">Confirmar Senha</label>
              <div class="input-group">
                <input id="confirm_password_register" type="password" name="confirm_password_register" class="form-control" placeholder="Confirme a senha" required>
                <button class="btn btn-outline-secondary" type="button" onclick="toggle_password_visibility('confirm_password_register', this)">
                  <i class="bi bi-eye-slash"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" name="submit_register" class="btn btn-primary">Cadastrar Aluno</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Manage Categories Modal -->
  <div class="modal fade" id="manage_categories_modal" tabindex="-1" aria-labelledby="manage_categories_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="manage_categories_modal_label">Gerenciar Categorias</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <h6>Adicionar Nova Categoria</h6>
            <form method="POST" action="../actions/add_category.php" class="row g-2">
              <div class="col-8">
                <input type="text" name="category_name" class="form-control" placeholder="Nome da categoria" required>
              </div>
              <div class="col-4">
                <button type="submit" name="add_category" class="btn btn-success w-100">Adicionar</button>
              </div>
            </form>
          </div>
          <hr>
          <h6>Categorias Existentes</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nome</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                  <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $category['id'] ?></td>
                      <td>
                        <span id="category_name_<?= $category['id'] ?>"><?= htmlspecialchars($category['nome']) ?></span>
                        <input type="text" id="category_edit_<?= $category['id'] ?>" value="<?= htmlspecialchars($category['nome']) ?>" class="form-control d-none">
                      </td>
                      <td>
                        <button class="btn btn-sm btn-warning" onclick="editCategory(<?= $category['id'] ?>)">Editar</button>
                        <button class="btn btn-sm btn-success d-none" onclick="saveCategory(<?= $category['id'] ?>)">Salvar</button>
                        <button class="btn btn-sm btn-secondary d-none" onclick="cancelEdit(<?= $category['id'] ?>)">Cancelar</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $category['id'] ?>)">Excluir</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Account Modal -->
  <div class="modal fade" id="delete_account_modal" tabindex="-1" aria-labelledby="delete_account_modal_label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="delete_account_modal_label">Deletar Conta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-danger">⚠️ <strong>Atenção!</strong> Esta ação é irreversível.</p>
          <p>Ao deletar sua conta, todos os seus dados serão permanentemente removidos do sistema.</p>
          <p>Tem certeza de que deseja continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <a href="../actions/delete_account.php" class="btn btn-danger">Confirmar Exclusão</a>
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
          document.getElementById('category_name_' + id).textContent = newName;
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
