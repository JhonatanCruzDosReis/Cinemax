<?php
session_start();
include('conexao.php'); // $conn - mysqli

// ----- Obter título do filme -----
$titulo = isset($_GET['filme']) ? trim($_GET['filme']) : '';
if (empty($titulo)) {
    die('Filme não especificado.');
}

// ----- Buscar filme no banco -----
$stmt = $conn->prepare("SELECT * FROM filmes WHERE titulo = ?");
$stmt->bind_param("s", $titulo);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Filme não encontrado.');
}
$filme = $result->fetch_assoc();
$filme_id = intval($filme['id']);

// ----- Encontrar arquivo de vídeo na pasta filmes -----
$videoPath = null;
$files = scandir('filmes');
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    if (pathinfo($file, PATHINFO_FILENAME) === $titulo) {
        $videoPath = 'filmes/' . $file;
        break;
    }
}
if (!$videoPath) {

    echo '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
    <meta charset="UTF-8">
    <title>Erro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body{
            background: radial-gradient(circle at top, #1a1a1a 0%, #0d0d0d 70%);
            font-family: "Poppins", sans-serif;
            color: #eee;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin:0;
        }

        .popup-error{
            position: relative;
            background: #0d0d0d;
            border-radius: 22px;
            padding: 45px 55px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0,0,0,0.6);
            animation: popupShow .55s ease-out;
            max-width: 440px;
            overflow: hidden;
        }

        /* FAIXA DEGRADÊ IGUAL AO SEU PRINT */
        .popup-error::before{
            content: "";
            position: absolute;
            top: -12px;
            left: -12px;
            right: -12px;
            height: 70px;
            background: linear-gradient(90deg, #7f00ff, #00c3ff);
            border-top-left-radius: 22px;
            border-top-right-radius: 22px;
            z-index: 0;
        }

        @keyframes popupShow{
            from{
                opacity:0;
                transform: scale(.7) translateY(20px);
            }
            to{
                opacity:1;
                transform: scale(1) translateY(0);
            }
        }

        .popup-content {
            position: relative;
            z-index: 2;
            margin-top: 45px;
        }

        .popup-icon{
            font-size:64px;
            margin-bottom:18px;
            color:#fff;
            text-shadow:0 0 12px rgba(255,255,255,0.45);
        }

        .popup-error h2{
            margin-top:10px;
            font-size:26px;
            color:#ffffff;
            font-weight:600;
        }

        .popup-error p{
            margin-top:12px;
            font-size:17px;
            color:#dcdcdc;
        }

        .popup-error small{
            display:block;
            margin-top:18px;
            color:#c7a8ff;
            font-size:14px;
        }
    </style>
    </head>

    <body>
        <div class="popup-error">
            <div class="popup-content">
                <i class="fa-solid fa-circle-exclamation popup-icon"></i>
                <h2>Vídeo não encontrado</h2>
                <p>Filme fora de hora, tente novamente em outra hora.</p>
                <small>Você será redirecionado em instantes...</small>
            </div>
        </div>

        <script>
            setTimeout(() => {
                window.location.href = "home_filme.php";
            }, 3000);
        </script>
    </body>
    </html>';

    exit;
}


// ----- Estado do usuário -----
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;
$isLoggedIn = $usuario_id !== null;

// ----- Processar requisição AJAX de avaliação -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'avaliar') {
    header('Content-Type: application/json; charset=utf-8');

    if (!$isLoggedIn) {
        echo json_encode(['status' => 'error', 'message' => 'Você precisa estar logado para avaliar.']);
        exit;
    }

    $estrelas = intval($_POST['estrelas'] ?? 0);
    if ($estrelas < 1 || $estrelas > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Valor de avaliação inválido.']);
        exit;
    }

    // Inserir ou atualizar (garante 1 avaliação por usuário/filme)
    // Usaremos INSERT ... ON DUPLICATE KEY UPDATE; precisa da UNIQUE(usuario_id, filme_id) no SQL da tabela
    $stmtUp = $conn->prepare("
        INSERT INTO avaliacao (usuario_id, filme_id, estrelas)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE estrelas = VALUES(estrelas), data_avaliacao = CURRENT_TIMESTAMP
    ");
    $stmtUp->bind_param("iii", $usuario_id, $filme_id, $estrelas);

    if ($stmtUp->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Avaliação salva.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar avaliação.']);
    }
    $stmtUp->close();
    exit;
}

// ----- Buscar nota média atual (opcional, para exibir) -----
$avg_stars = 0;
$stmtAvg = $conn->prepare("SELECT AVG(estrelas) as media, COUNT(*) as total FROM avaliacao WHERE filme_id = ?");
$stmtAvg->bind_param("i", $filme_id);
$stmtAvg->execute();
$resAvg = $stmtAvg->get_result();
if ($rowAvg = $resAvg->fetch_assoc()) {
    $avg_stars = round(floatval($rowAvg['media']), 2);
    $total_av = intval($rowAvg['total']);
}
$stmtAvg->close();

?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Assistir - <?= htmlspecialchars($titulo) ?> - Cinemax</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root{
  --bg-1:#080808; --bg-2:#0d0d0d;
  --card-bg: rgba(255,255,255,0.04);
  --accent-1: #7f00ff;
  --accent-2: #00d4ff;
  --muted: #a8a8a8;
}

/* Body / layout */
body{
  font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
  background: radial-gradient(circle at 10% 0%, #071026 0%, var(--bg-2) 45%, #000 100%);
  color: #eaeaea;
  -webkit-font-smoothing:antialiased;
  margin:0;
  padding-top:72px;
}
.navbar { background: rgba(8,8,8,0.6) !important; backdrop-filter: blur(8px); border-bottom: 1px solid rgba(255,255,255,0.03); }
.brand-logo { width:44px; height:44px; border-radius:8px; object-fit:cover; }

/* ---------- VIDEO CONTAINER (YouTube-like) ---------- */
.video-container {
  position: relative;
  width: 100%;
  max-width: 1120px;
  margin: 28px auto;
  border-radius: 12px;
  overflow: hidden;
  background: #000;
}

/* Remove controles nativos, usaremos custom controls */
video#player {
  width: 100%;
  height: auto;
  display: block;
  background:#000;
}

/* Overlay (controls) escondidos até mousemove */
.player-overlay {
  position: absolute;
  left: 0; right: 0; bottom: 0;
  padding: 12px 14px;
  background: linear-gradient(to top, rgba(0,0,0,0.65), rgba(0,0,0,0));
  display: flex;
  flex-direction: column;
  gap: 10px;
  opacity: 0;
  transform: translateY(6px);
  transition: opacity .25s ease, transform .25s ease;
  pointer-events: none;
}
.video-container.show-controls .player-overlay {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

/* Top overlay small (title/close optionally) */
.top-overlay {
  position: absolute;
  top: 8px;
  left: 12px;
  right: 12px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  z-index:5;
  color:#fff;
  pointer-events:none;
}

/* Control row like YouTube */
.controls-row {
  display:flex;
  align-items:center;
  gap:12px;
  justify-content:space-between;
}

/* Left controls (play, time, progress) */
.controls-left {
  display:flex;
  align-items:center;
  gap:10px;
}

/* Buttons style (integrated, not superficial) */
.ctrl-btn {
  background: rgba(0,0,0,0.25);
  border: 1px solid rgba(255,255,255,0.06);
  color: #fff;
  padding: 8px 10px;
  border-radius: 8px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  cursor:pointer;
  transition: background .15s, transform .08s;
  backdrop-filter: blur(4px);
}
.ctrl-btn:hover { transform: translateY(-2px); background: rgba(255,255,255,0.03); }

/* Progress bar */
.progress-wrap {
  display:flex;
  align-items:center;
  gap:10px;
  width: 100%;
}
.time {
  min-width:56px;
  font-size:13px;
  color:#ddd;
}
.progress {
  -webkit-appearance:none;
  appearance:none;
  height:6px;
  width:100%;
  background: rgba(255,255,255,0.08);
  border-radius:6px;
  overflow:hidden;
  cursor:pointer;
}
.progress::-webkit-slider-thumb { -webkit-appearance:none; width:12px; height:12px; background:var(--accent-1); border-radius:50%; box-shadow:0 0 8px rgba(127,0,255,0.25); cursor:pointer; }

/* Right controls */
.controls-right { display:flex; align-items:center; gap:8px; }

/* Volume slider */
.volume { width:110px; }

/* Speed select */
.speed-select {
  background: rgba(0,0,0,0.25);
  color:#fff;
  border: 1px solid rgba(255,255,255,0.06);
  padding:6px 8px;
  border-radius:8px;
}

/* Fullscreen */
.fullscreen-btn { font-size:16px; }

/* Star rating (overlay, centered bottom) */
.star-rating {
  display:flex;
  gap:8px;
  justify-content:center;
  align-items:center;
  margin-top:6px;
}
.star {
  font-size:26px;
  color:#6d6d6d;
  cursor:pointer;
  transition: transform .12s, color .12s;
}
.star:hover { transform: scale(1.16); color: gold; }
.star.active { color: gold; transform: scale(1.05); }

/* Film description */
.desc {
  max-width: 1000px;
  margin: 22px auto;
  color: #e8e8e8;
  text-align:center;
  font-size:16px;
}
.meta {
  color: var(--muted);
  margin-top:8px;
  font-size:14px;
}

/* responsive */
@media (max-width:768px) {
  .volume { width:70px; }
  .ctrl-btn { padding:6px 8px; border-radius:7px; }
  .star { font-size:22px; }
  .time { display:none; }
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="Home_filme.php">
      <img src="https://cdn-icons-png.flaticon.com/512/2298/2298620.png" class="brand-logo me-2" alt="logo">
      <div>
        <div style="font-weight:700">Cinemax - Cinema em casa</div>
        <small style="color:var(--muted)"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Assista do conforto da sua casa.'); ?></small>
      </div>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <button id="zoomOut" class="btn btn-sm btn-outline-light" title="Zoom -"><i class="fas fa-search-minus"></i></button>
      <button id="zoomIn"  class="btn btn-sm btn-outline-light" title="Zoom +"><i class="fas fa-search-plus"></i></button>

      <button id="toggleTheme" class="btn btn-sm btn-outline-light" title="Modo claro/escuro">
        <i id="themeIcon" class="fas fa-moon"></i>
      </button>

      <?php if ($isLoggedIn): ?>
      <div class="dropdown">
        <a class="btn btn-sm btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-user-circle"></i>
          <span class="d-none d-sm-inline ms-1"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Perfil') ?></span>
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

<section class="hero container mt-4 text-center">
  <div class="bubble">Assistindo: <?= htmlspecialchars($titulo) ?></div>
</section>

<main class="container my-4">

  <div class="text-center mb-4">
    <div class="section-title">Reprodução de Filme</div>
  </div>

  <!-- VIDEO CONTAINER -->
  <div id="videoWrap" class="video-container">

    <!-- Top small overlay (optional) -->
    <div class="top-overlay">
      <div style="font-weight:600"><?= htmlspecialchars($titulo) ?></div>
      <div style="opacity:.8; font-size:13px"><?= $avg_stars > 0 ? "Avaliação média: $avg_stars ($total_av)" : "Sem avaliações ainda" ?></div>
    </div>

    <!-- HTML5 video (sem atributo controls) -->
    <video id="player" preload="metadata">
      <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
      Seu navegador não suporta vídeo.
    </video>

    <!-- Player overlay com controles integrados -->
    <div id="overlay" class="player-overlay">

      <!-- Progress + time -->
      <div class="controls-row">
        <div class="controls-left" style="flex:1">
          <button id="playBtn" class="ctrl-btn" title="Play/Pause"><i id="playIcon" class="fa fa-play"></i></button>

          <div class="time" id="currentTime">0:00</div>

          <div style="flex:1" class="progress-wrap">
            <input id="progress" class="progress" type="range" min="0" max="100" value="0" step="0.1">
          </div>

          <div class="time" id="durationTime">0:00</div>
        </div>

        <div class="controls-right">
          <input id="volume" class="volume" type="range" min="0" max="1" step="0.01" value="1" title="Volume">
          <select id="speed" class="speed-select" title="Velocidade">
            <option value="0.5">0.5x</option>
            <option value="0.75">0.75x</option>
            <option value="1" selected>1x</option>
            <option value="1.25">1.25x</option>
            <option value="1.5">1.5x</option>
            <option value="2">2x</option>
          </select>

          <button id="fsBtn" class="ctrl-btn fullscreen-btn" title="Tela cheia"><i class="fa fa-expand"></i></button>
        </div>
      </div>

      <!-- Star rating centered -->
      <div style="display:flex; justify-content:center;">
        <div id="stars" class="star-rating" title="Avalie este filme">
          <i class="fa-solid fa-star star" data-star="1"></i>
          <i class="fa-solid fa-star star" data-star="2"></i>
          <i class="fa-solid fa-star star" data-star="3"></i>
          <i class="fa-solid fa-star star" data-star="4"></i>
          <i class="fa-solid fa-star star" data-star="5"></i>
        </div>
      </div>

    </div>
  </div>

  <!-- Descrição -->
  <div class="desc">
    <p><strong>Gênero:</strong> <?= htmlspecialchars($filme['genero']) ?> • <strong>Ano:</strong> <?= htmlspecialchars($filme['ano']) ?></p>
    <p><?= nl2br(htmlspecialchars($filme['descricao'])) ?></p>
  </div>

</main>

<footer class="text-center mt-5 py-4">
  <small>&copy; <?= date('Y') ?> Cinemax — Todos os direitos reservados.</small>
</footer>

<script>
// ========================= PLAYER LOGIC =========================
const videoWrap = document.getElementById('videoWrap');
const player = document.getElementById('player');
const overlay = document.getElementById('overlay');
const playBtn = document.getElementById('playBtn');
const playIcon = document.getElementById('playIcon');
const progress = document.getElementById('progress');
const currentTimeEl = document.getElementById('currentTime');
const durationTimeEl = document.getElementById('durationTime');
const volume = document.getElementById('volume');
const speed = document.getElementById('speed');
const fsBtn = document.getElementById('fsBtn');
const stars = document.querySelectorAll('.star');

let controlsTimeout = null;
let isControlsVisible = false;
let isDraggingProgress = false;

// Mostrar controles por X segundos ao mover o mouse
function showControlsTemporarily() {
  videoWrap.classList.add('show-controls');
  isControlsVisible = true;
  if (controlsTimeout) clearTimeout(controlsTimeout);
  controlsTimeout = setTimeout(() => {
    videoWrap.classList.remove('show-controls');
    isControlsVisible = false;
  }, 2500);
}

// Eventos para mousemove/enter
videoWrap.addEventListener('mousemove', showControlsTemporarily);
videoWrap.addEventListener('mouseenter', showControlsTemporarily);

// Play/Pause toggle
function updatePlayIcon(){
  if(player.paused) {
    playIcon.className = 'fa fa-play';
  } else {
    playIcon.className = 'fa fa-pause';
  }
}
playBtn.addEventListener('click', () => {
  if (player.paused) player.play(); else player.pause();
  updatePlayIcon();
});

// Atualizar tempo e progress
player.addEventListener('timeupdate', () => {
  if (!isDraggingProgress) {
    const percent = (player.currentTime / Math.max(1, player.duration)) * 100;
    progress.value = percent;
  }
  currentTimeEl.textContent = formatTime(player.currentTime);
});

player.addEventListener('loadedmetadata', () => {
  durationTimeEl.textContent = formatTime(player.duration);
});

// Progresso interações
progress.addEventListener('input', (e) => {
  isDraggingProgress = true;
});
progress.addEventListener('change', (e) => {
  const pct = parseFloat(progress.value);
  const time = (pct / 100) * player.duration;
  player.currentTime = time;
  isDraggingProgress = false;
});

// Volume
volume.addEventListener('input', () => {
  player.volume = parseFloat(volume.value);
});

// Speed
speed.addEventListener('change', () => {
  player.playbackRate = parseFloat(speed.value);
});

// Fullscreen
fsBtn.addEventListener('click', () => {
  if (!document.fullscreenElement) {
    videoWrap.requestFullscreen().catch(err => console.error(err));
  } else {
    document.exitFullscreen();
  }
});

// Format time mm:ss
function formatTime(sec) {
  if (!isFinite(sec)) return '0:00';
  const s = Math.floor(sec % 60);
  const m = Math.floor(sec / 60);
  return `${m}:${s.toString().padStart(2,'0')}`;
}

// Espaço para play/pause com clique no vídeo
player.addEventListener('click', () => {
  if (player.paused) player.play(); else player.pause();
  updatePlayIcon();
});

// Atualiza ícone quando pausa/roda
player.addEventListener('play', updatePlayIcon);
player.addEventListener('pause', updatePlayIcon);

// Inicia com controles escondidos, mas visíveis momentaneamente
showControlsTemporarily();

// ========================= AVALIAÇÃO (AJAX) =========================
const filmId = <?= json_encode($filme_id) ?>;
const isLogged = <?= $isLoggedIn ? 'true' : 'false' ?>;

// Inicializar estrelas (marcar média aproximada na UI - aqui marcamos none para evitar confusão)
function clearStarsUI(){
  stars.forEach(s => s.classList.remove('active'));
}
clearStarsUI();

// Carregar avaliação atual do usuário (opcional) - podemos buscar via AJAX: /get_user_rating.php?filme=ID
// Para simplicidade, fazemos uma requisição quando a página carrega para saber se o usuário já avaliou (melhora UX)
<?php if ($isLoggedIn): ?>
// Buscar avaliação do usuário atual (AJAX)
fetch('<?= basename(__FILE__) ?>?action=get_user_rating&filme=<?= $filme_id ?>', { method: 'GET' })
.then(r => r.json())
.then(data => {
  if(data.status === 'ok' && data.estrelas) {
    const userStars = data.estrelas;
    stars.forEach(s => {
      if (parseInt(s.dataset.star) <= userStars) s.classList.add('active');
    });
  }
})
.catch(()=>{});
<?php endif; ?>

// Click nas estrelas: envia avaliação e substitui se já existir
stars.forEach(s => {
  s.addEventListener('click', () => {
    const value = s.dataset.star;

    if(!isLogged) {
      alert('Faça login para avaliar este filme.');
      return;
    }

    // Update UI locally
    stars.forEach(x => {
      if (parseInt(x.dataset.star) <= parseInt(value)) x.classList.add('active');
      else x.classList.remove('active');
    });

    // Enviar AJAX POST
    const form = new URLSearchParams();
    form.append('action', 'avaliar');
    form.append('estrelas', value);

    fetch(location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: form.toString()
    })
    .then(r => r.json())
    .then(json => {
      if (json.status === 'ok') {
        // exibimos um aviso discreto
        console.log('Avaliação salva');
      } else {
        alert(json.message || 'Erro ao avaliar.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Erro ao enviar avaliação.');
    });
  });
});

(function handleGetRatingEndpoint(){
  const url = new URL(location.href);
  const params = new URLSearchParams(url.search);
  if (params.get('action') === 'get_user_rating' && params.get('filme')) {
  }
})();
</script>

</body>
</html>
