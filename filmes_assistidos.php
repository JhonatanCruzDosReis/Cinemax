<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include('conexao.php');

// Dados do usuário
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT id, nome, email, foto, tipo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$isAdmin = ($usuario['tipo'] == 'mestre');
if (!$isAdmin) {
    header("Location: Home_filme.php");
    exit();
}

/* ============================================================
   PEGAR FILMES ASSISTIDOS + AVALIAÇÃO DO USUÁRIO
   ============================================================ */
$sql_assistidos = "
SELECT 
    fa.id_assistido,
    f.id AS filme_id,
    f.titulo,
    f.genero,
    f.ano,
    f.capa,
    fa.data_assistido,
    av.estrelas,
    av.comentario
FROM filmes_assistidos fa
INNER JOIN filmes f ON f.id = fa.id_filme
LEFT JOIN avaliacao av 
       ON av.filme_id = f.id 
      AND av.usuario_id = fa.id_usuario
WHERE fa.id_usuario = ?
ORDER BY fa.data_assistido DESC
";

$stmt = $conn->prepare($sql_assistidos);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$assistidos = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Filmes Assistidos</title>

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
    padding: 1.5rem 1rem;
    box-shadow: 2px 0 10px rgba(0,0,0,0.4);
}

.sidebar a {
    display: block;
    padding: .6rem 1rem;
    text-decoration: none;
    margin: .5rem 0;
    border-radius: 8px;
    color: #ccc;
    transition: 0.3s;
}

.sidebar a:hover, .sidebar a.active {
    background-color: #0dcaf0;
    color: #111;
    font-weight: 600;
}

.sidebar .profile img {
    width: 100px; height: 100px;
    border-radius: 50%;
    border: 3px solid #0dcaf0;
}

main {
    margin-left: 270px;
    padding: 2rem;
}

.card-dark {
    background-color: #181818;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 2rem;
}

.movie-card {
    background: #151515;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.movie-card img {
    width: 120px;
    border-radius: 12px;
    object-fit: cover;
}

.star-filled {
    color: gold;
}
.star-empty {
    color: #555;
}
</style>
</head>
<body>

<!-- ==================== SIDEBAR ==================== -->
<div class="sidebar">
    <div>
        <div class="profile text-center">
            <img src="<?= $usuario['foto'] ?: 'https://i.imgur.com/ZKZC4Qp.png' ?>">
            <h4 class="text-info mt-2"><?= htmlspecialchars($usuario['nome']) ?> (Mestre)</h4>
            <small><?= htmlspecialchars($usuario['email']) ?></small>
        </div>

        <hr style="border-color:#333;">

        <a href="Home_filme.php"><i class="fas fa-home me-2"></i> Início</a>

        <!-- 🔥 EXATAMENTE COMO VOCÊ PEDIU, NADA MUDADO -->
        <a href="perfil.php?aba=filmes"><i class="fas fa-film me-2"></i> Filmes</a>
        <a href="perfil.php?aba=usuarios"><i class="fas fa-users me-2"></i> Usuários</a>

        <a href="filmes_assistidos.php" class="active">
            <i class="fas fa-check-circle me-2"></i> Filmes Assistidos
        </a>

        <!-- 🔥 ESTE LINK JÁ LEVA DIRETO PARA lista_desejos.php -->
        <a href="lista_desejos.php">
            <i class="fas fa-heart me-2"></i> Lista de Desejos
        </a>
        <!-- 🔥 FIM -->
    </div>

    <div>
        <hr style="border-color:#333;">
        <a href="logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt me-2"></i> Sair
        </a>
    </div>
</div>

<!-- ==================== CONTEÚDO ==================== -->
<main>
<h2 class="mb-4">
    <i class="fas fa-check-circle text-success me-2"></i>
    Filmes Assistidos
</h2>

<div class="card-dark">

<?php if ($assistidos->num_rows == 0): ?>

    <p class="text-center text-secondary">Nenhum filme assistido ainda.</p>

<?php else: ?>

    <?php while ($f = $assistidos->fetch_assoc()): ?>

        <div class="movie-card">

            <img src="<?= htmlspecialchars($f['capa']) ?>" alt="Capa">

            <div>
                <h4 class="text-info mb-1"><?= htmlspecialchars($f['titulo']) ?></h4>

                <p class="mb-1">🎭 <?= htmlspecialchars($f['genero']) ?> • 📅 <?= htmlspecialchars($f['ano']) ?></p>

                <small class="text-secondary d-block mb-2">
                    Assistido em: <?= date("d/m/Y", strtotime($f['data_assistido'])) ?>
                </small>

                <!-- ⭐ ESTRELAS DA AVALIAÇÃO -->
                <div>
                    <?php if ($f['estrelas']): ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= $f['estrelas'] ? 'fas star-filled fa-star' : 'far star-empty fa-star' ?>"></i>
                        <?php endfor; ?>
                    <?php else: ?>
                        <span class="text-secondary">Não avaliado ainda</span>
                    <?php endif; ?>
                </div>

                <?php if ($f['comentario']): ?>
                    <p class="mt-2 text-muted"><i><?= htmlspecialchars($f['comentario']) ?></i></p>
                <?php endif; ?>
            </div>

        </div>

    <?php endwhile; ?>

<?php endif; ?>

</div>

</main>
</body>
</html>
