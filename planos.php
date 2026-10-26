<?php
session_start();
// Se o usuário já estiver logado, redirecionar para o home
if (isset($_SESSION['usuario_id'])) {
    header("Location: Home_filme.php");
    exit();
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Planos - Cinemax</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(to bottom, rgba(0,0,0,0.9), rgba(20,20,20,0.95)),
                  url('https://img.freepik.com/fotos-gratis/variedade-de-elementos-de-cinema-em-fundo-vermelho-com-espaco-de-copia_23-2148457848.jpg') center/cover no-repeat;
      min-height: 100vh;
      color: #fff;
      font-family: 'Poppins', sans-serif;
      padding: 20px;
    }

    .planos-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 30px;
    }

    .plano-card {
      background: rgba(15, 15, 15, 0.85);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 30px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 20px rgba(0,0,0,0.6);
      backdrop-filter: blur(8px);
      text-align: center;
      transition: transform 0.3s ease;
    }

    .plano-card:hover {
      transform: scale(1.05);
    }

    .plano-card h3 {
      font-weight: 600;
      color: #ffcc00;
      margin-bottom: 15px;
    }

    .preco {
      font-size: 2rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 20px;
    }

    .beneficios {
      list-style: none;
      padding: 0;
      margin-bottom: 30px;
    }

    .beneficios li {
      margin-bottom: 10px;
      font-size: 0.9rem;
    }

    .beneficios li i {
      color: #ffcc00;
      margin-right: 8px;
    }

    .btn-plano {
      background: #ffcc00;
      color: #000;
      font-weight: 600;
      border: none;
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      transition: 0.3s;
      text-decoration: none;
      display: inline-block;
    }

    .btn-plano:hover {
      background: #ffd633;
      transform: scale(1.02);
      box-shadow: 0 0 10px rgba(255, 204, 0, 0.6);
    }

    .plano-anual {
      border: 2px solid #ffcc00;
      position: relative;
    }

    .plano-anual::before {
      content: "Mais Popular";
      position: absolute;
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      background: #ffcc00;
      color: #000;
      padding: 5px 15px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .logo {
      display: block;
      margin: 0 auto 30px auto;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: url('https://elements-resized.envatousercontent.com/elements-cover-images/ef381742-b3ef-418d-a11e-4ca9be51ad4e?w=433&cf_fit=scale-down&q=85&format=auto&s=a7dd250900ead50f92fc72f59adc1c1dcfe9320d5fb2c10f461b81bf850cc63a') center/cover;
      box-shadow: 0 0 15px rgba(255, 204, 0, 0.6);
    }

    h1 {
      text-align: center;
      margin-bottom: 40px;
      font-weight: 600;
      color: #ffcc00;
      letter-spacing: 1px;
    }

    footer {
      position: absolute;
      bottom: 10px;
      text-align: center;
      width: 100%;
      font-size: 0.9rem;
      color: #ccc;
    }
  </style>
</head>

<body>
  <div class="logo"></div>
  <h1><i class="fa-solid fa-film me-2"></i>Escolha seu Plano e faça parte dessa família</h1>
  <div class="planos-container">
    <div class="plano-card">
      <h3>Plano Mensal Básico</h3>
      <div class="preco">R$ 19,90/mês</div>
      <ul class="beneficios">
        <li><i class="fa-solid fa-check"></i>Acesso a filmes e séries em HD</li>
        <li><i class="fa-solid fa-check"></i>1 dispositivo simultâneo</li>
        <li><i class="fa-solid fa-check"></i>Sem anúncios</li>
        <li><i class="fa-solid fa-check"></i>Recomendações personalizadas</li>
        <li><i class="fa-solid fa-times"></i>Sem downloads offline</li>
        <li><i class="fa-solid fa-times"></i>Sem acesso a lançamentos exclusivos</li>
      </ul>
      <a href="cadastro.php?plano=mensal" class="btn-plano">
        <i class="fa-solid fa-credit-card me-1"></i>Escolher Plano Básico
      </a>
    </div>

    <div class="plano-card plano-anual">
      <h3>Plano Mensal Premium</h3>
      <div class="preco">R$ 39,90/mês</div>
      <ul class="beneficios">
        <li><i class="fa-solid fa-check"></i>Acesso a filmes e séries em 4K</li>
        <li><i class="fa-solid fa-check"></i>Até 4 dispositivos simultâneos</li>
        <li><i class="fa-solid fa-check"></i>Sem anúncios</li>
        <li><i class="fa-solid fa-check"></i>Recomendações personalizadas</li>
        <li><i class="fa-solid fa-check"></i>Downloads offline ilimitados</li>
        <li><i class="fa-solid fa-check"></i>Acesso antecipado a lançamentos exclusivos</li>
        <li><i class="fa-solid fa-check"></i>Suporte prioritário</li>
        <li><i class="fa-solid fa-check"></i>Perfil infantil</li>
      </ul>
      <a href="cadastro.php?plano=anual" class="btn-plano">
        <i class="fa-solid fa-credit-card me-1"></i>Escolher Plano Premium
      </a>
    </div>
  </div>

  <footer>
    <p>&copy; <?= date('Y') ?> Cinemax; O seu pequeno local de lazer</p>
  </footer>
</body>
</html>