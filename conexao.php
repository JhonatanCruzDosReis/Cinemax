<?php
$host = 'localhost';
$user = 'root';
$password = ''; // sua senha do MySQL, se houver
$dbname = 'cinemax';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
