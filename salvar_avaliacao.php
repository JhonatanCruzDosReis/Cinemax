<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    die("ERRO: usuário não logado.");
}

$usuario = $_SESSION['usuario_id'];
$filme   = intval($_POST['filme'] ?? 0);
$nota    = intval($_POST['nota'] ?? 0);

if ($nota < 1 || $nota > 5) die("Nota inválida.");

$stmt = $conn->prepare("
    INSERT INTO avaliacao (usuario_id, filme_id, nota, data_hora)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE nota = VALUES(nota), data_hora = NOW()
");

$stmt->bind_param("iii", $usuario, $filme, $nota);
$stmt->execute();

echo "OK";
