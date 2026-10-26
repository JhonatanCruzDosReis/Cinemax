<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include('conexao.php');

$usuario_id = $_SESSION['usuario_id'];

$sql = "
    SELECT f.titulo, fa.nota
    FROM filmes_assistidos fa
    INNER JOIN filmes f ON f.id = fa.id_filme
    WHERE fa.id_usuario = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$notas  = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['titulo'];
    $notas[]  = intval($row['nota']);
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gráfico</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    background: radial-gradient(circle at top, #1a1a1a 0%, #0d0d0d 70%);
    color: #eee;
    font-family: "Inter", sans-serif;
    margin: 0;
}

/* CAIXA MAIOR E MAIS ELEGANTE */
.container-box {
    position: relative;
    width: 90%;
    max-width: 1100px; /* antes era 900px */
    min-height: 70vh;  /* ocupa melhor a tela */
    margin: 60px auto;
    background: #181818;
    padding: 45px 50px; /* mais espaçamento */
    border-radius: 20px;
    box-shadow: 0 0 35px rgba(80,0,150,0.4);
    animation: pulseGlow 4s ease-in-out infinite alternate;
}

/* BRILHO MELHOR DISTRIBUÍDO */
@keyframes pulseGlow {
    from { box-shadow: 0 0 20px rgba(120,0,255,0.35); }
    to   { box-shadow: 0 0 55px rgba(180,80,255,0.8); }
}

/* TÍTULO MAIOR E ELEGANTE */
h2 {
    text-align: center;
    color: #d7b5ff;
    letter-spacing: 1px;
    font-size: 2.1rem;
    margin-bottom: 30px;
    text-shadow: 0 0 12px #a56bff;
}

/* BOTÃO DE VOLTAR (SEM ALTERAR FUNÇÃO) */
.btn-voltar {
    position: absolute;
    top: -25px;
    left: -25px;
    background: linear-gradient(135deg, #00cfff, #ffd700);
    border: none;
    padding: 14px 20px;
    border-radius: 50px;
    font-size: 17px;
    font-weight: bold;
    color: #000;
    cursor: pointer;
    box-shadow: 0 0 20px rgba(0,255,255,0.7), 0 0 30px rgba(255,215,0,0.7);
    transition: 0.3s;
    animation: shinePulse 1.8s infinite alternate ease-in-out;
}

.btn-voltar:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 0 30px rgba(0,255,255,1), 0 0 50px rgba(255,215,0,1);
}

@keyframes shinePulse {
    from { filter: brightness(1); }
    to   { filter: brightness(1.45); }
}

/* PAINEL LATERAL – AGORA MAIOR E MAIS VISÍVEL */
.ordenacao-box {
    position: absolute;
    left: -80px;
    top: 25%;
    display: flex;
    flex-direction: column;
    gap: 26px;
    animation: slideIn 0.8s ease-out forwards;
}

@keyframes slideIn {
    from { transform: translateX(-40px); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

.ordenacao-btn {
    background: #9b59ff;
    padding: 14px 16px;
    color: #fff;
    border-radius: 12px;
    font-size: 22px;
    cursor: pointer;
    border: none;
    text-align: center;
    transition: 0.3s;
    box-shadow: 0 0 15px rgba(155,89,255,0.6);
}

.ordenacao-btn:hover {
    transform: scale(1.18);
    background: #b06cff;
}

/* GRÁFICO MAIOR E MELHOR DISTRIBUÍDO */
canvas {
    margin-top: 25px;
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 100% !important;
    max-width: 950px;
    height: 450px !important; /* deixa proporcional e elegante */
    animation: fadeIn 1s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to   { opacity: 1; transform: scale(1); }
}
</style>
</head>
<body>

<div class="container-box">

    <a href="perfil.php" class="btn-voltar">
        <i class="fas fa-arrow-left"></i> Retornar
    </a>

    <div class="ordenacao-box">
        <button class="ordenacao-btn" onclick="ordenarMaiorMenor()" title="Maior para menor">
            <i class="fas fa-arrow-down"></i>
        </button>

        <button class="ordenacao-btn" onclick="ordenarMenorMaior()" title="Menor para maior">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <h2><i class="fas fa-chart-bar"></i> Gráfico de Notas dos Filmes Assistidos</h2>

    <canvas id="graficoNotas"></canvas>
</div>

<script>
let labels = <?= json_encode($labels) ?>;
let notas  = <?= json_encode($notas) ?>;

const ctx = document.getElementById("graficoNotas").getContext("2d");

const grafico = new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [{
            label: "Nota",
            data: notas,
            backgroundColor: "rgba(155, 89, 255, 0.85)",
            borderColor: "#e3b9ff",
            borderWidth: 4,
            borderRadius: 12,
            hoverBackgroundColor: "rgba(200, 129, 255, 1)",
            hoverBorderWidth: 5,
            hoverBorderColor: "#ffffff"
        }]
    },
    options: {
        responsive: true,
        animation: {
            duration: 1200,
            easing: "easeOutQuart"
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: "#ccc", font: { size: 14 } },
                grid: { color: "rgba(255,255,255,0.05)" }
            },
            x: {
                ticks: { color: "#ccc", font: { size: 13 } },
                grid: { display: false }
            }
        },
        plugins: {
            legend: {
                labels: { color: "#fff", font: { size: 16 } }
            }
        }
    }
});

function ordenarMenorMaior() {
    let combinado = labels.map((t, i) => ({ titulo: t, nota: notas[i] }));
    combinado.sort((a, b) => a.nota - b.nota);

    labels = combinado.map(x => x.titulo);
    notas  = combinado.map(x => x.nota);

    atualizar();
}

function ordenarMaiorMenor() {
    let combinado = labels.map((t, i) => ({ titulo: t, nota: notas[i] }));
    combinado.sort((a, b) => b.nota - a.nota);

    labels = combinado.map(x => x.titulo);
    notas  = combinado.map(x => x.nota);

    atualizar();
}

function atualizar() {
    grafico.data.labels = labels;
    grafico.data.datasets[0].data = notas;
    grafico.update();
}
</script>

</body>
</html>
