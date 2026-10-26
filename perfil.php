<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include('conexao.php');

// Dados do usuário logado (ajustado para o banco atual)
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT id, nome, email, foto, github, linkedin, instagram, bio, tipo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("Erro: Usuário não encontrado!");
$usuario = $result->fetch_assoc();
$stmt->close();

$isAdmin = ($usuario['tipo'] === 'mestre'); // Painel restrito a usuários do tipo mestre

if (!$isAdmin) {
    header("Location: Home_filme.php"); // Redireciona se não for admin
    exit();
}

// ====================== PAINEL DO ADMIN ======================

// --- AÇÕES: FILMES ---
if (isset($_POST['criar_filme'])) {
    $titulo = $_POST['titulo'];
    $genero = $_POST['genero'];
    $ano = $_POST['ano'];
    $descricao = $_POST['descricao'];
    $capa = $_POST['capa'];
    $preco = $_POST['preco'];

    $insert = $conn->prepare("INSERT INTO filmes (titulo, genero, ano, descricao, capa, preco) VALUES (?,?,?,?,?,?)");
    $insert->bind_param("ssisss", $titulo, $genero, $ano, $descricao, $capa, $preco);
    $insert->execute();
    $insert->close();
    $msg = "🎬 Filme criado com sucesso!";
}

if (isset($_POST['editar_filme'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $genero = $_POST['genero'];
    $ano = $_POST['ano'];
    $descricao = $_POST['descricao'];
    $capa = $_POST['capa'];
    $preco = $_POST['preco'];

    $update = $conn->prepare("UPDATE filmes SET titulo=?, genero=?, ano=?, descricao=?, capa=?, preco=? WHERE id=?");
    $update->bind_param("ssisssi", $titulo, $genero, $ano, $descricao, $capa, $preco, $id);
    $update->execute();
    $update->close();
    $msg = "✅ Filme atualizado com sucesso!";
}

if (isset($_GET['excluir_filme'])) {
    $id = intval($_GET['excluir_filme']);
    $del = $conn->prepare("DELETE FROM filmes WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    $msg = "🗑️ Filme removido com sucesso!";
}

// --- AÇÕES: USUÁRIOS ---
if (isset($_GET['excluir_user'])) {
    $id = intval($_GET['excluir_user']);
    if ($id != $usuario['id']) { // impede o mestre de excluir a própria conta
        $delU = $conn->prepare("DELETE FROM usuarios WHERE id=?");
        $delU->bind_param("i", $id);
        $delU->execute();
        $delU->close();
        $msg = "🚫 Usuário removido com sucesso!";
    } else {
        $msg = "❌ Você não pode excluir o Mestre.";
    }
}

// --- ORDENAÇÃO ---
$order_filmes = isset($_GET['order_filmes']) ? $_GET['order_filmes'] : 'id_desc';
switch ($order_filmes) {
    case 'id_asc':
        $order_by_filmes = 'id ASC';
        break;
    case 'nome_asc':
        $order_by_filmes = 'titulo ASC';
        break;
    default:
        $order_by_filmes = 'id DESC';
}

$order_usuarios = isset($_GET['order_usuarios']) ? $_GET['order_usuarios'] : 'id_asc';
switch ($order_usuarios) {
    case 'id_desc':
        $order_by_usuarios = 'id DESC';
        break;
    case 'nome_asc':
        $order_by_usuarios = 'nome ASC';
        break;
    default:
        $order_by_usuarios = 'id ASC';
}

// --- LISTAS (com verificação de erro) ---
$filmes = $conn->query("SELECT id, titulo, descricao, genero, ano, preco, capa, criado_em FROM filmes ORDER BY $order_by_filmes");
if (!$filmes) {
    die("Erro na query de filmes: " . $conn->error . ". Verifique se a tabela 'filmes' existe e tem os campos corretos (remova duplicatas de 'descricao').");
}

$usuarios = $conn->query("SELECT id, nome, email, criado_em, plano, meio_pagamento, tipo FROM usuarios ORDER BY $order_by_usuarios");
if (!$usuarios) {
    die("Erro na query de usuarios: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Administrativo - Mestre</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
  font-family: "Inter", sans-serif;
  background-color: #0e0e0e;
  color: #e9ecef;
  overflow-x: hidden;
}
.sidebar {
  background: #141414;
  height: 100vh;
  position: fixed;
  width: 270px;
  top: 0; left: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 2px 0 10px rgba(0,0,0,0.4);
  padding: 1.5rem 1rem;
}
.sidebar .profile {
  text-align: center;
}
.sidebar .profile img {
  width: 100px; height: 100px;
  border-radius: 50%; object-fit: cover;
  border: 3px solid #0dcaf0;
  margin-bottom: .8rem;
}
.sidebar h4 { color: #0dcaf0; margin-bottom: 0.2rem; }
.sidebar small { color: #aaa; }
.sidebar a {
  display: block;
  color: #ccc;
  text-decoration: none;
  margin: .6rem 0;
  padding: .6rem 1rem;
  border-radius: 8px;
  transition: all .3s;
}
.sidebar a:hover, .sidebar a.active {
  background-color: #0dcaf0;
  color: #111;
  font-weight: 600;
}
main {
  margin-left: 270px;
  padding: 2rem;
  min-height: 100vh;
}
.card-dark {
  background-color: #181818;
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.5);
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.btn-custom {
  background-color: #0dcaf0;
  color: #111;
  border: none;
  transition: 0.3s;
}
.btn-custom:hover { background-color: #09b8de; }
.table-dark td, .table-dark th { vertical-align: middle; }
footer {
  color: #777;
  text-align: center;
  margin-top: 2rem;
  padding: 1rem 0;
  border-top: 1px solid #333;
}
.badge-mestre {
  background: linear-gradient(90deg, #ffcc00, #ff6600);
  color: #111;
  font-weight: bold;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div>
    <div class="profile">
      <img src="<?= $usuario['foto'] ?: 'https://i.imgur.com/ZKZC4Qp.png' ?>" alt="Foto de perfil">
      <h4><?= htmlspecialchars($usuario['nome']) ?> (Mestre)</h4>
      <small><?= htmlspecialchars($usuario['email']) ?></small>
    </div>
    <hr style="border-color:#333;">
    <a href="Home_filme.php"><i class="fas fa-home me-2"></i> Início</a>
    <a href="#" class="active"><i class="fas fa-film me-2"></i> Filmes</a>
    <a href="#usuarios"><i class="fas fa-users me-2"></i> Usuários</a>
  </div>
  <div>
    <hr style="border-color:#333;">
    <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Sair</a>
  </div>
</div>

<!-- Conteúdo -->
<main>
  <h2 class="mb-4"><i class="fas fa-crown text-warning me-2"></i>Painel Administrativo - Mestre</h2>

  <?php if (isset($msg)): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Criar filme -->
  <div class="card-dark">
    <h5 class="mb-3"><i class="fas fa-plus-circle me-2 text-info"></i>Adicionar Novo Filme</h5>
    <form method="post" class="row g-3">
      <input type="hidden" name="criar_filme" value="1">
      <div class="col-md-4">
        <input type="text" name="titulo" class="form-control" placeholder="Título" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="genero" class="form-control" placeholder="Gênero" required>
      </div>
      <div class="col-md-2">
        <input type="number" name="ano" class="form-control" placeholder="Ano" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="preco" class="form-control" placeholder="Preço (ex: 19.90)" required>
      </div>
      <div class="col-12">
        <input type="url" name="capa" class="form-control" placeholder="URL da capa" required>
      </div>
      <div class="col-12">
        <textarea name="descricao" class="form-control" placeholder="Descrição do filme" required></textarea>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-custom"><i class="fas fa-plus"></i> Criar Filme</button>
      </div>
    </form>
  </div>

  <!-- Lista de filmes -->
  <div class="card-dark">
    <h5 class="mb-3"><i class="fas fa-list me-2 text-info"></i>Filmes Cadastrados</h5>
    <div class="mb-3">
      <a href="?order_filmes=id_desc" class="btn btn-sm btn-outline-primary">ID Desc</a>
      <a href="?order_filmes=id_asc" class="btn btn-sm btn-outline-primary">ID Asc</a>
      <a href="?order_filmes=nome_asc" class="btn btn-sm btn-outline-primary">Nome A-Z</a>
    </div>
    <div class="table-responsive">
      <table class="table table-dark table-hover">
        <thead class="table-light text-dark">
          <tr><th>ID</th><th>Título</th><th>Gênero</th><th>Ano</th><th>Preço</th><th>Ações</th></tr>
        </thead>
        <tbody>
        <?php while ($f = $filmes->fetch_assoc()): ?>
          <tr>
            <td><?= $f['id'] ?></td>
            <td><?= htmlspecialchars($f['titulo']) ?></td>
            <td><?= htmlspecialchars($f['genero']) ?></td>
            <td><?= $f['ano'] ?></td>
            <td>R$ <?= number_format($f['preco'], 2, ',', '.') ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $f['id'] ?>"><i class="fas fa-edit"></i></button>
              <a href="?excluir_filme=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover este filme?')"><i class="fas fa-trash"></i></a>
            </td>
          </tr>

          <!-- Modal Edição -->
          <div class="modal fade" id="editModal<?= $f['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content bg-dark text-light">
                <form method="post">
                  <div class="modal-header border-0">
                    <h5 class="modal-title text-info"><i class="fas fa-edit me-2"></i>Editar Filme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body row g-3">
                    <input type="hidden" name="editar_filme" value="1">
                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                    <div class="col-md-6"><input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($f['titulo']) ?>"></div>
                    <div class="col-md-3"><input type="text" name="genero" class="form-control" value="<?= htmlspecialchars($f['genero']) ?>"></div>
                    <div class="col-md-3"><input type="number" name="ano" class="form-control" value="<?= $f['ano'] ?>"></div>
                    <div class="col-12"><textarea name="descricao" class="form-control"><?= htmlspecialchars($f['descricao']) ?></textarea></div>
                    <div class="col-md-6"><input type="url" name="capa" class="form-control" value="<?= htmlspecialchars($f['capa']) ?>"></div>
                    <div class="col-md-3"><input type="text" name="preco" class="form-control" value="<?= $f['preco'] ?>"></div>
                  </div>
                  <div class="modal-footer border-0">
                    <button class="btn btn-custom"><i class="fas fa-save"></i> Salvar</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Usuários -->
  <div id="usuarios" class="card-dark">
    <h5 class="mb-3"><i class="fas fa-users me-2 text-info"></i>Usuários Registrados</h5>
    <div class="mb-3">
      <a href="?order_usuarios=id_asc" class="btn btn-sm btn-outline-primary">ID Asc</a>
      <a href="?order_usuarios=id_desc" class="btn btn-sm btn-outline-primary">ID Desc</a>
      <a href="?order_usuarios=nome_asc" class="btn btn-sm btn-outline-primary">Nome A-Z</a>
    </div>
    <div class="table-responsive">
      <table class="table table-dark table-hover">
        <thead class="table-light text-dark">
          <tr><th>ID</th><th>Nome</th><th>Email</th><th>Data Criação</th><th>Plano</th><th>Pagamento</th><th>Tipo</th><th>Ações</th></tr>
        </thead>
        <tbody>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nome']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['criado_em'] ? date('d/m/Y H:i', strtotime($u['criado_em'])) : 'N/A' ?></td>
            <td>
              <?php if ($u['plano'] === 'mensal'): ?>
                <span class="badge bg-primary">Mensal</span>
              <?php elseif ($u['plano'] === 'anual'): ?>
                <span class="badge bg-success">Anual</span>
              <?php else: ?>
                <span class="badge bg-secondary">N/A</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($u['meio_pagamento'] === 'cartao'): ?>
                <span class="badge bg-warning">Cartão</span>
              <?php elseif ($u['meio_pagamento'] === 'pix'): ?>
                <span class="badge bg-info">Pix</span>
              <?php else: ?>
                <span class="badge bg-secondary">N/A</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($u['tipo'] === 'mestre'): ?>
                <span class="badge badge-mestre">MESTRE</span>
              <?php else: ?>
                <span class="badge bg-secondary">Usuário</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($u['id'] != $usuario['id']): ?>
                <a href="?excluir_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este usuário?')">
                  <i class="fas fa-trash"></i>
                </a>
              <?php else: ?>
                <button class="btn btn-sm btn-warning" disabled><i class="fas fa-crown"></i></button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

<!-- Sidebar -->
<div class="sidebar">
  <div>
    <div class="profile">
      <img src="<?= $usuario['foto'] ?: 'https://i.imgur.com/ZKZC4Qp.png' ?>" alt="Foto de perfil">
      <h4><?= htmlspecialchars($usuario['nome']) ?> (Mestre)</h4>
      <small><?= htmlspecialchars($usuario['email']) ?></small>
    </div>

    <hr style="border-color:#333;">

    <a href="Home_filme.php"><i class="fas fa-home me-2"></i> Início</a>
    <a href="#" class="active"><i class="fas fa-film me-2"></i> Filmes</a>
    <a href="#usuarios"><i class="fas fa-users me-2"></i> Usuários</a>
    <a href="grafico.php"><i class="fas fa-users me-2"></i> Gráficos</a>

    <!-- ======== ABA ADICIONADA: FILMES ASSISTIDOS ======== -->
    <a href="filmes_assistidos.php">
      <i class="fas fa-check-circle me-2"></i> Filmes Assistidos
    </a>

    <!-- ======== ABA ADICIONADA: LISTA DE DESEJOS ======== -->
    <a href="lista_desejos.php">
      <i class="fas fa-heart me-2"></i> Lista de Desejos
    </a>

  </div>

  <div>
    <hr style="border-color:#333;">
    <a href="logout.php" class="text-danger">
      <i class="fas fa-sign-out-alt me-2"></i> Sair
    </a>
  </div>
</div>


</main>

<footer>
  <small>&copy; <?= date('Y') ?> Painel Administrativo - Mestre</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">

</script>
</body>
</html>