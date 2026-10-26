<?php
session_start();

include('conexao.php'); 

// Criar tabela de avaliações se não existir
$conn->query("CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    filme_id INT NOT NULL,
    estrelas INT NOT NULL CHECK (estrelas BETWEEN 1 AND 5),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (filme_id) REFERENCES filmes(id),
    UNIQUE KEY unique_avaliacao (usuario_id, filme_id)
)");

// Handle AJAX avaliação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'avaliar' && isset($_SESSION['usuario_id'])) {
    $titulo = $_POST['titulo'];
    $estrelas = intval($_POST['estrelas']);
    if ($estrelas < 1 || $estrelas > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Avaliação inválida']);
        exit();
    }
    // Obter filme_id
    $stmt = $conn->prepare("SELECT id FROM filmes WHERE titulo = ?");
    $stmt->bind_param("s", $titulo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Filme não encontrado']);
        exit();
    }
    $filme = $result->fetch_assoc();
    $filme_id = $filme['id'];
    $usuario_id = $_SESSION['usuario_id'];
    // Inserir ou atualizar avaliação
    $stmt = $conn->prepare("INSERT INTO avaliacoes (usuario_id, filme_id, estrelas) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE estrelas = ?");
    $stmt->bind_param("iiii", $usuario_id, $filme_id, $estrelas, $estrelas);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar avaliação']);
    }
    exit();
}

$filmesPadrao = [
    ['titulo'=>'Os incríveis 2','genero'=>'Aventura / Família','ano'=>'2024','descricao'=>'Uma jornada épica para salvar o futuro da vida na Terra, cheia de fé e amizade.','capa'=>'https://cdn.jornaldaparaiba.com.br/wp-content/uploads/2018/04/cartaz-os-incr%C3%ADveis-2.jpg?xid=694901'],
    ['titulo'=>'Raya e o Último Dragão','genero'=>'Animação / Fantasia','ano'=>'2021','descricao'=>'Raya embarca em uma jornada para restaurar a harmonia entre os povos de Kumandra.','capa'=>'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgVpxKA0E4l_wh8eJASgldhvfkjxUkefttjRWoGgufrntNoJpk46o1mgA1of_5N3J6xJxmVQQ8OfqvN6E14W03zVPp3GTFRXyaeZam3ZGZtIVTNu2ejWCqh0XOuS-NO_f-DQwJA8jtQn0GwGaHOd73rkErpTfmEijHY86ieTUTaiY1HiIKEfJZbY_3t9g/s1024/Raya_e_o_3Fo_29.webp'],
    ['titulo'=>'Escape from Planet Earth','genero'=>'Animação / Comédia','ano'=>'2013','descricao'=>'Dois irmãos alienígenas precisam escapar da Terra em uma aventura cheia de humor e ação.','capa'=>'https://cdn.ome.lt/legacy/images/galerias/Escape-From-Planet-Earth/Escape-From-Planet-Earth-poster.jpg'],
    ['titulo'=>'Os Croods','genero'=>'Aventura / Família','ano'=>'2013','descricao'=>'Uma família pré-histórica embarca em uma jornada em busca de um novo lar.','capa'=>'https://http2.mlstatic.com/D_NQ_NP_951581-MLB43170980494_082020-O.webp'],
    ['titulo'=>'Divertidamente','genero'=>'Animação / Comédia / Drama','ano'=>'2015','descricao'=>'As emoções de uma garotinha ganham vida dentro de sua mente em uma comovente aventura.','capa'=>'https://buritama.sp.gov.br/wp-content/uploads/2018/08/WhatsApp-Image-2018-08-06-at-14.35.43.jpeg'],
    ['titulo'=>'Bob Esponja: O Incrível Resgate','genero'=>'Animação / Aventura / Comédia','ano'=>'2020','descricao'=>'Bob e Patrick partem em uma missão heroica para salvar Gary, o caracol desaparecido.','capa'=>'https://cinepop.com.br/wp-content/uploads/2019/11/ParamountKids-1194367142348673030-20191112_183131-img1.jpg'],
    ['titulo'=>'Detona Ralph','genero'=>'Animação / Comédia / Aventura','ano'=>'2012','descricao'=>'Um vilão de jogo decide ser herói e embarca em uma jornada épica entre mundos digitais.','capa'=>'https://cinema10.com.br/upload/filmes/filmes_2219_Detona-Ralph-Poster.jpg']
];

foreach ($filmesPadrao as $f) {
    $check = $conn->prepare("SELECT id FROM filmes WHERE titulo = ?");
    $check->bind_param("s", $f['titulo']);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO filmes (titulo, genero, ano, descricao, capa) VALUES (?,?,?,?,?)");
        $ins->bind_param("ssiss", $f['titulo'], $f['genero'], $f['ano'], $f['descricao'], $f['capa']);
        $ins->execute();
        $ins->close();
    }
    $check->close();
}

// Obter gêneros únicos para o filtro
$generos = [];
$result_generos = $conn->query("SELECT DISTINCT genero FROM filmes");
if ($result_generos) {
    while ($row = $result_generos->fetch_assoc()) {
        $generos = array_merge($generos, explode(' / ', $row['genero']));
    }
    $generos = array_unique(array_map('trim', $generos));
    sort($generos);
}

// Filtros
$busca_nome = isset($_GET['busca_nome']) ? trim($_GET['busca_nome']) : '';
$busca_genero = isset($_GET['busca_genero']) ? trim($_GET['busca_genero']) : '';
$busca_ano = isset($_GET['busca_ano']) ? trim($_GET['busca_ano']) : '';
$apenas_favoritos = isset($_GET['favoritos']) && $_GET['favoritos'] == '1';

$sql = "SELECT * FROM filmes WHERE 1=1";
$params = [];
$types = '';

if ($busca_nome !== '') {
    $sql .= " AND titulo LIKE ?";
    $params[] = "%$busca_nome%";
    $types .= 's';
}

if ($busca_genero !== '') {
    $sql .= " AND genero LIKE ?";
    $params[] = "%$busca_genero%";
    $types .= 's';
}

if ($busca_ano !== '') {
    $sql .= " AND ano = ?";
    $params[] = $busca_ano;
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$filmes = [];
if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) $filmes[] = $r;
} else {
    // Em vez de voltar para padrão, mostrar mensagem de nenhum resultado
    $filmes = [];
}

// Filtrar apenas favoritos se solicitado
if ($apenas_favoritos && isset($_SESSION['favoritos'])) {
    $filmes = array_filter($filmes, function($f) {
        return in_array($f['titulo'], $_SESSION['favoritos']);
    });
}

if (!isset($_SESSION['favoritos'])) $_SESSION['favoritos'] = [];

function gerarCards($filmes, $isLoggedIn) {
    $html = '';
    if (empty($filmes)) {
        $html = '<div class="col-12 text-center"><p class="text-muted">Nenhum filme encontrado. Tente ajustar os filtros.</p></div>';
        return $html;
    }
    foreach ($filmes as $f) {
        $img = htmlspecialchars($f['capa'] ?? 'https://via.placeholder.com/300x450?text=Sem+Imagem');
        $titulo = htmlspecialchars($f['titulo']);
        $genero = htmlspecialchars($f['genero'] ?? '');
        $ano = htmlspecialchars($f['ano'] ?? '');
        $desc = htmlspecialchars($f['descricao'] ?? '');
        $favClass = in_array($f['titulo'], $_SESSION['favoritos']) ? 'fav-on' : 'fav-off';
        $favDisabled = !$isLoggedIn ? 'disabled' : '';

        $html .= "
        <div class='col-lg-3 col-md-4 col-sm-6 mb-4 film-entry'>
          <div class='card filme-card h-100'>
            <div class='poster' style=\"background-image:url('$img')\"></div>
            <div class='card-body d-flex flex-column'>
              <h5 class='card-title mb-1'>$titulo</h5>
              <p class='card-text small text-muted mb-2'>$genero • $ano</p>
              <p class='text-truncate mb-2' title=\"$desc\">$desc</p>
              <div class='mt-auto d-flex justify-content-between align-items-center'>
                <div class='d-flex gap-2 align-items-center'>
                  <button class='btn btn-ghost btn-sm' onclick=\"abrirModalDetalhes('".addslashes($f['titulo'])."','".addslashes($f['genero'])."','".addslashes($f['ano'])."','$img','".addslashes($f['descricao'])."')\">
                    <i class='fas fa-info-circle'></i>
                  </button>
                  <button class='btn btn-primary btn-sm assistir-btn' data-titulo='".addslashes($f['titulo'])."' data-logged='$isLoggedIn'>
                    <i class='fas fa-play me-1'></i> Assistir
                  </button>
                </div>
                <div class='estrelas' data-titulo='".addslashes($f['titulo'])."' data-logged='$isLoggedIn'>
                  " . gerarEstrelas($f['titulo'], $isLoggedIn) . "
                </div>
              </div>
            </div>
          </div>
        </div>
        ";
    }
    return $html;
}

function gerarEstrelas($titulo, $isLoggedIn) {
    if (!$isLoggedIn) {
        return '<span class="text-muted">Faça login para avaliar</span>';
    }
    // Obter avaliação atual do usuário
    global $conn;
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT estrelas FROM avaliacoes a JOIN filmes f ON a.filme_id = f.id WHERE f.titulo = ? AND a.usuario_id = ?");
    $stmt->bind_param("si", $titulo, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $avaliacao = $result->fetch_assoc()['estrelas'] ?? 0;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= $avaliacao ? 'fas fa-star' : 'far fa-star';
        $html .= "<i class='estrela $class' data-estrela='$i' style='cursor:pointer; color:#ffd700;'></i>";
    }
    return $html;
}

$isLoggedIn = isset($_SESSION['usuario_id']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cinemax </title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root{
  --bg-1:#080808; --bg-2:#0d0d0d;
  --card-bg: rgba(255,255,255,0.04);
  --accent-1: #7f00ff;
  --accent-2: #00d4ff;
  --muted: #a8a8a8;
}

body{
  font-family:'Poppins',system-ui,Segoe UI,Roboto,Helvetica,Arial;
  background: radial-gradient(circle at 10% 0%, #071026 0%, var(--bg-2) 45%, #000 100%);
  color: #eaeaea;
  -webkit-font-smoothing:antialiased;
  transition: background .4s ease, color .3s ease, filter .2s ease;
  padding-top: 72px;
}

body.light-mode{
  background: linear-gradient(180deg, #f2f4f8 0%, #e9eef6 100%);
  color: #111;
}

.navbar {
  background: rgba(8,8,8,0.6)!important;
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255,255,255,0.03);
}
body.light-mode .navbar { background: rgba(255,255,255,0.90)!important; }

.brand-logo { width:44px; height:44px; border-radius:8px; object-fit:cover; }

.hero { text-align:center; margin-top: 6px; }
.hero .bubble {
  display:inline-block; padding: 14px 34px; border-radius: 999px;
  background: linear-gradient(90deg, rgba(127,0,255,0.14), rgba(0,212,255,0.06));
  color: #fff; border: 1px solid rgba(255,255,255,0.04); font-weight:600;
}
body.light-mode .hero .bubble { color:#111; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }

.banner { max-width:1000px; margin: 42px auto; border-radius:16px; overflow:hidden; }
.banner .inner { padding:0; height:220px; background:center/cover no-repeat url('https://img.freepik.com/vetores-gratis/cartaz-de-tempo-de-filme-de-cinema-horizontal-realista-com-carretel-de-badalo-de-bilhetes-de-lanches-de-oculos-3d-em-fundo-azul-com-ilustracao-vetorial-bokeh_1284-77013.jpg'); display:flex; align-items:center; justify-content:center; }

.section-title {
  display:inline-block; padding:10px 26px; border-radius:999px;
  background: linear-gradient(90deg, var(--accent-1), var(--accent-2));
  color:#fff; font-weight:700; margin-bottom:28px;
}

.filme-card {
  background: var(--card-bg);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  overflow: hidden;
  transition: transform .36s ease, box-shadow .36s ease;
  display:flex; flex-direction:column;
}

.filme-card .card-body { color: #eaeaea; }

body.light-mode .filme-card .card-body { color: #222; background: transparent; }

.filme-card .poster { height: 260px; background-size: cover; background-position:center; transition: transform .6s cubic-bezier(.2,.9,.2,1), filter .3s; }
.filme-card .card-body { padding: 14px; }

.filme-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 30px 60px rgba(127,0,255,0.08); }
.filme-card:hover .poster { transform: scale(1.06); filter:brightness(1.03); }

.btn-ghost { background: transparent; border: 1px solid rgba(255,255,255,0.06); color: inherit; }
.fav-btn { transition: transform .2s; color: rgba(255,255,255,0.6); background: none; border: none; }
.fav-btn:hover:not(:disabled) { transform: scale(1.08); color: var(--accent-1) !important; }
.fav-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.fav-on { color: #ff6b6b !important; }
.fav-off { color: rgba(255,255,255,0.6); }

.modal-content { background: linear-gradient(180deg, rgba(10,10,12,0.95), rgba(10,10,12,0.9)); color: #fff; border-radius:12px; border:none; }
body.light-mode .modal-content { background: #fff; color:#111; }

body.light-mode .card, body.light-mode .filme-card { background: #fff; border: 1px solid rgba(0,0,0,0.08); }
body.light-mode .text-muted { color: #666 !important; }
body.light-mode .btn-outline-light { color: #333; border-color: #bbb; }
body.light-mode .btn-outline-light:hover { background: #f3f3f3; }
body.light-mode .btn-primary { background-color: #007bff; border-color: #007bff; }
body.light-mode .btn-primary:hover { background-color: #0069d9; }

@keyframes appearUp {
    from { opacity:0; transform: translateY(28px) scale(.98); }
    to   { opacity:1; transform: translateY(0) scale(1); }
}
.film-entry {
    animation: appearUp .7s ease both;
}

@media (max-width:768px) {
    .banner .inner {
        height:160px;
        background-position:center 10%;
    }
    .poster { height:200px; }
}

/* Modal de assinatura */
.subscription-modal .modal-dialog {
    max-width: 600px;
}
.plan-card {
    background: var(--card-bg);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    text-align: center;
}
.plan-card h5 { color: var(--accent-1); }
.plan-card .price {
    font-size: 2rem;
    font-weight: bold;
}
.plan-card ul {
    list-style: none;
    padding: 0;
}
.plan-card li { margin-bottom: 5px; }

/* Filtros */
.filtros {
    background: var(--card-bg);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.filtros .form-control,
.filtros .form-select {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #eaeaea;
}
.filtros .form-control::placeholder { color: #aaa; }

body.light-mode .filtros .form-control,
body.light-mode .filtros .form-select {
    background: #fff;
    color: #111;
    border-color: #ccc;
}
body.light-mode .filtros .form-control::placeholder {
    color: #666;
}

/* Botão filtros */
.btn-custom {
    background: linear-gradient(90deg, var(--accent-1), var(--accent-2));
    border: none;
    color: #fff;
}
.btn-custom:hover {
    background: linear-gradient(90deg, var(--accent-2), var(--accent-1));
}
</style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="Home_filme.php">
            <img src="https://cdn-icons-png.flaticon.com/512/2298/2298620.png"
                 class="brand-logo me-2" alt="logo">
            <div>
                <div style="font-weight:700">Cinemax - Cinema em casa</div>
                <small style="color:var(--muted)">
                    <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Assista do conforto da sua casa.'); ?>
                </small>
            </div>
        </a>

        <!-- Ações -->
        <div class="ms-auto d-flex align-items-center gap-2">

            <button id="zoomOut" class="btn btn-sm btn-outline-light" title="Zoom -">
                <i class="fas fa-search-minus"></i>
            </button>

            <button id="zoomIn" class="btn btn-sm btn-outline-light" title="Zoom +">
                <i class="fas fa-search-plus"></i>
            </button>

            <button id="toggleTheme" class="btn btn-sm btn-outline-light" title="Modo claro/escuro">
                <i id="themeIcon" class="fas fa-moon"></i>
            </button>

            <?php if($isLoggedIn): ?>
            <div class="dropdown">
                <a class="btn btn-sm btn-outline-light dropdown-toggle"
                   href="#" role="button" data-bs-toggle="dropdown">

                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-sm-inline ms-1">
                        <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Perfil') ?>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-id-badge me-2"></i>Meu perfil</a></li>
                    <li><a class="dropdown-item" href="?favoritos=1"><i class="fas fa-heart me-2"></i>Favoritos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                </ul>
            </div>

            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-primary">Entrar</a>
            <?php endif; ?>

        </div>
    </div>
</nav>

<section class="hero container hero mt-4">
    <div class="bubble">Assista aos melhores filmes no conforto da sua casa!</div>
    <div class="mt-3" style="color:var(--muted)">
        Escolha, clique e aproveite sem sair do sofá
        <img src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/1f37f.svg"
             width="18" alt="pipoca">
    </div>
</section>

<section class="banner"><div class="inner"></div></section>

<main class="container my-4">

    <div class="text-center mb-4">
        <div class="section-title">Catálogo de Filmes</div>
    </div>

    <!-- Filtros -->
    <div class="filtros">
        <form method="get" class="row g-3">

            <div class="col-md-3">
                <input type="text" name="busca_nome" class="form-control"
                       placeholder="Buscar por nome"
                       value="<?= htmlspecialchars($busca_nome) ?>">
            </div>

            <div class="col-md-3">
                <select name="busca_genero" class="form-select">
                    <option value="">Todos os gêneros</option>

                    <?php foreach ($generos as $genero): ?>
                        <option value="<?= htmlspecialchars($genero) ?>"
                                <?= $busca_genero === $genero ? 'selected' : '' ?>>
                            <?= htmlspecialchars($genero) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <input type="number" name="busca_ano" class="form-control"
                       placeholder="Ano" value="<?= htmlspecialchars($busca_ano) ?>">
            </div>

            <div class="col-md-2 d-flex align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="favoritos" value="1" id="favoritosCheck"
                           <?= $apenas_favoritos ? 'checked' : '' ?>>
                    <label class="form-check-label" for="favoritosCheck">
                        Apenas favoritos
                    </label>
                </div>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-custom w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>

        </form>
    </div>

    <div class="row">
        <?= gerarCards($filmes, $isLoggedIn) ?>
    </div>

</main>

<!-- Modal Filme -->
<div class="modal fade" id="movieModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body p-3">
                <div class="row g-3">

                    <div class="col-md-5">
                        <img id="modalPoster" class="img-fluid rounded" src="" alt="">
                    </div>

                    <div class="col-md-7">
                        <h4 id="modalTitle"></h4>
                        <p class="text-muted mb-1" id="modalMeta"></p>
                        <p id="modalDesc"></p>

                        <div class="mt-3 d-flex gap-2">
                            <button id="modalFav" class="btn btn-outline-danger">
                                <i class="fas fa-heart"></i> Favoritar
                            </button>
                            <button id="modalAssistir" class="btn btn-primary">
                                <i class="fas fa-play"></i> Assistir
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Assinatura -->
<div class="modal fade subscription-modal" id="subscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Escolha seu Plano de Assinatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="plan-card">
                    <h5>Básico</h5>
                    <div class="price">R$ 19,90/mês</div>
                    <ul>
                        <li>Assista em 1 dispositivo</li>
                        <li>Qualidade SD</li>
                        <li>Downloads ilimitados</li>
                    </ul>
                    <button class="btn btn-primary" onclick="assinar('basico')">Assinar Básico</button>
                </div>

                <div class="plan-card">
                    <h5>Padrão</h5>
                    <div class="price">R$ 39,90/mês</div>
                    <ul>
                        <li>Assista em 2 dispositivos</li>
                        <li>Qualidade HD</li>
                        <li>Downloads ilimitados</li>
                    </ul>
                    <button class="btn btn-primary" onclick="assinar('padrao')">Assinar Padrão</button>
                </div>

                <div class="plan-card">
                    <h5>Premium</h5>
                    <div class="price">R$ 55,90/mês</div>
                    <ul>
                        <li>Assista em 4 dispositivos</li>
                        <li>Qualidade Ultra HD</li>
                        <li>Downloads ilimitados</li>
                    </ul>
                    <button class="btn btn-primary" onclick="assinar('premium')">Assinar Premium</button>
                </div>

            </div>

        </div>
    </div>
</div>

<footer class="text-center mt-5 py-4">
    <small>&copy; <?= date('Y') ?> Cinemax — Todos os direitos reservados.</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* ================= THEME ================= */
const body = document.body;
const themeBtn = document.getElementById('toggleTheme');
const themeIcon = document.getElementById('themeIcon');

function setTheme(isLight){
    if(isLight){
        body.classList.add('light-mode');
        themeIcon.className = 'fas fa-sun';
    } else {
        body.classList.remove('light-mode');
        themeIcon.className = 'fas fa-moon';
    }
}

const saved = localStorage.getItem('site_theme_light');
setTheme(saved === '1');

themeBtn.addEventListener('click', ()=>{
    const isLight = !body.classList.contains('light-mode');
    setTheme(isLight);
    localStorage.setItem('site_theme_light', isLight ? '1' : '0');
});

/* ================= ZOOM ================= */
let zoom = parseFloat(localStorage.getItem('site_zoom') || '1');
function applyZoom() {
    document.documentElement.style.zoom = zoom;
    localStorage.setItem('site_zoom', zoom.toFixed(2));
}

document.getElementById('zoomIn').addEventListener('click', ()=>{
    zoom = Math.min(1.4, zoom + 0.1);
    applyZoom();
});
document.getElementById('zoomOut').addEventListener('click', ()=>{
    zoom = Math.max(0.7, zoom - 0.1);
    applyZoom();
});
applyZoom();

/* ============ MODAL DETALHES ============= */
function abrirModalDetalhes(titulo, genero, ano, imagem, descricao){
    const modal = new bootstrap.Modal(document.getElementById('movieModal'));

    document.getElementById('modalPoster').src = imagem;
    document.getElementById('modalTitle').textContent = titulo;
    document.getElementById('modalMeta').textContent = genero + ' • ' + ano;
    document.getElementById('modalDesc').textContent = descricao;

    document.getElementById('modalAssistir').onclick = ()=> assistirFilme(titulo);
    document.getElementById('modalFav').onclick = ()=> favoritarFilme(titulo);

    modal.show();
}

function assistirFilme(titulo){
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    if(isLoggedIn){
        window.location.href = 'video.php?filme=' + encodeURIComponent(titulo);
    } else {
        new bootstrap.Modal(document.getElementById('subscriptionModal')).show();
    }
}

function favoritarFilme(titulo){
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    if(!isLoggedIn){
        alert('Você precisa estar logado para favoritar filmes.');
        return;
    }

    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=favoritar&titulo=' + encodeURIComponent(titulo)
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'added') alert('Filme adicionado aos favoritos!');
        if(data.status === 'removed') alert('Filme removido dos favoritos!');
        location.reload();
    });
}

function assinar(plano){
    alert('Redirecionando para pagamento do plano ' + plano + '...');
}

/* ============ EVENTOS DAS ESTRELAS ============= */
document.addEventListener('DOMContentLoaded', ()=>{

    document.querySelectorAll('.film-entry')
        .forEach((e,i)=> e.style.animationDelay = (i * 60) + 'ms');

    document.querySelectorAll('.assistir-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const titulo  = btn.dataset.titulo;
            const isLogged = btn.dataset.logged === '1';

            if(isLogged){
                window.location.href = 'video.php?filme=' + encodeURIComponent(titulo);
            } else {
                new bootstrap.Modal(document.getElementById('subscriptionModal')).show();
            }
        });
    });

    document.querySelectorAll('.fav-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            favoritarFilme(btn.dataset.titulo);
        });
    });

    document.querySelectorAll('.estrelas').forEach(container => {

        const titulo = container.dataset.titulo;
        const isLogged = container.dataset.logged === '1';
        if(!isLogged) return;

        container.querySelectorAll('.estrela').forEach(star => {

            star.addEventListener('click', () => {

                const estrelas = star.dataset.estrela;

                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=avaliar&titulo=' + encodeURIComponent(titulo) +
                          '&estrelas=' + estrelas
                })
                .then(r => r.json())
                .then(data => {
                    if(data.status === 'success') location.reload();
                    else alert(data.message);
                });

            });

        });

    });

});

</script>

</body>
</html>
