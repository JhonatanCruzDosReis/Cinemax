<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Alugador de Filmes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1">🎬 Alugador de Filmes</span>
    <div>
      <span class="text-light me-3">Olá, <?= $_SESSION['usuario_nome'] ?>!</span>
      <a href="logout.php" class="btn btn-danger">Sair</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3>Bem-vindo à plataforma de aluguel de filmes!</h3>
  <p>Aqui você pode explorar, alugar e curtir seus filmes favoritos.</p>
</div>
</body>
</html>
