<?php
include('conexao.php');

$plano = isset($_GET['plano']) ? $_GET['plano'] : 'mensal'; // Captura o plano da URL (padrão mensal)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $foto = trim($_POST['foto']);
    $github = trim($_POST['github']);
    $linkedin = trim($_POST['linkedin']);
    $instagram = trim($_POST['instagram']);
    $bio = trim($_POST['bio']);
    $meio_pagamento = $_POST['meio_pagamento'];
    $numero_cartao = $meio_pagamento === 'cartao' ? trim($_POST['numero_cartao']) : NULL;
    $validade_cartao = $meio_pagamento === 'cartao' ? trim($_POST['validade_cartao']) : NULL;
    $cvv = $meio_pagamento === 'cartao' ? trim($_POST['cvv']) : NULL;
    $chave_pix = $meio_pagamento === 'pix' ? trim($_POST['chave_pix']) : NULL;

    // Validação básica
    if (empty($nome) || empty($email) || empty($senha)) {
        echo "<script>alert('Preencha todos os campos obrigatórios!');</script>";
    } elseif ($meio_pagamento === 'cartao' && (empty($numero_cartao) || empty($validade_cartao) || empty($cvv))) {
        echo "<script>alert('Preencha os dados do cartão!');</script>";
    } elseif ($meio_pagamento === 'pix' && empty($chave_pix)) {
        echo "<script>alert('Preencha a chave Pix!');</script>";
    } else {
        // Pagamento simulado: o CVV nunca é gravado e do cartão só ficam os 4 últimos dígitos.
        // Em produção, os dados do cartão iriam direto para um gateway de pagamento.
        if ($numero_cartao !== NULL) {
            $digitos = preg_replace('/\D/', '', $numero_cartao);
            $numero_cartao = '**** **** **** ' . substr($digitos, -4);
        }
        $sql = "INSERT INTO usuarios (nome, email, senha, foto, github, linkedin, instagram, bio, plano, meio_pagamento, numero_cartao, validade_cartao, chave_pix) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssss", $nome, $email, $senha, $foto, $github, $linkedin, $instagram, $bio, $plano, $meio_pagamento, $numero_cartao, $validade_cartao, $chave_pix);

        if ($stmt->execute()) {
            // Simulação de processamento de pagamento (em produção, integre com gateway)
            echo "<script>alert('Cadastro e pagamento processados com sucesso! Bem-vindo ao Cinemax.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar: e-mail já cadastrado ou problema no servidor.');</script>";
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Cadastro - Cinemax</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
  background: radial-gradient(circle at top, #111 0%, #000 80%);
  color: #fff;
  font-family: 'Poppins', sans-serif;
  overflow-x: hidden;
}

.logo {
  text-align: center;
  font-size: 2.5em;
  font-weight: bold;
  color: #ffcc00;
  text-shadow: 0 0 15px #ffcc00;
  margin-bottom: 20px;
}

.cadastro-container {
  max-width: 500px; /* Aumentei para acomodar novos campos */
  margin: 5% auto;
  background: rgba(25, 25, 25, 0.95);
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 0 20px rgba(255, 204, 0, 0.2);
  backdrop-filter: blur(6px);
}

.cadastro-container h2 {
  text-align: center;
  color: #ffcc00;
  font-weight: 700;
  text-shadow: 0 0 10px rgba(255, 204, 0, 0.6);
  margin-bottom: 25px;
}

.form-control {
  background: lightgray; /* Fundo cinza claro quando não focado */
  border: 1px solid #444;
  color: rgba(0, 0, 0, 0.5); /* Texto preto opaco quando não focado */
  border-radius: 10px;
  transition: 0.3s;
}

.form-control:focus {
  border-color: #ffcc00;
  box-shadow: 0 0 10px rgba(255, 204, 0, 0.4);
  background: white; /* Fundo branco quando focado */
  color: black; /* Texto preto forte quando focado */
}

/* Estilos específicos para os selects "Plano escolhido" e "Meio de pagamento" */
.select-especial {
  background: black !important; /* Fundo sempre preto */
  color: white !important; /* Texto sempre branco para visibilidade */
  border: 2px solid #ffcc00 !important; /* Borda dourada */
}

.select-especial:focus {
  background: black !important; /* Mantém fundo preto no foco */
  color: white !important; /* Mantém texto branco no foco */
  border: 2px solid #ffcc00 !important; /* Mantém borda dourada */
  box-shadow: 0 0 10px rgba(255, 204, 0, 0.4) !important; /* Mantém sombra */
}

.btn-cadastrar {
  background: linear-gradient(90deg, #ffcc00, #ffaa00);
  color: #000;
  font-weight: bold;
  width: 100%;
  border: none;
  border-radius: 10px;
  padding: 12px;
  font-size: 1.1em;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-cadastrar::after {
  content: "";
  position: absolute;
  top: 0; left: -100%;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.4);
  transform: skewX(-20deg);
  transition: 0.5s;
}

.btn-cadastrar:hover::after {
  left: 120%;
}

.btn-cadastrar:hover {
  transform: scale(1.05);
  box-shadow: 0 0 15px rgba(255, 204, 0, 0.6);
}

.btn-voltar {
  display: block;
  text-align: center;
  border: 2px solid #ffcc00;
  color: #ffcc00;
  font-weight: 600;
  width: 100%;
  padding: 10px;
  border-radius: 10px;
  margin-top: 15px;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-voltar:hover {
  background: #ffcc00;
  color: #000;
  transform: scale(1.03);
  box-shadow: 0 0 10px rgba(255, 204, 0, 0.7);
}

.pagamento-fields {
  display: none;
}

footer {
  text-align: center;
  margin-top: 40px;
  color: #777;
  font-size: 0.9em;
}
</style>
<script>
function togglePagamento() {
  const meio = document.getElementById('meio_pagamento').value;
  document.getElementById('cartao_fields').style.display = meio === 'cartao' ? 'block' : 'none';
  document.getElementById('pix_fields').style.display = meio === 'pix' ? 'block' : 'none';
}

// Funções de máscara para os campos
function mascaraCartao(input) {
  let value = input.value.replace(/\D/g, '');
  value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
  input.value = value.substring(0, 19); // Limita a 16 dígitos + 3 espaços
}

function mascaraValidade(input) {
  let value = input.value.replace(/\D/g, '');
  if (value.length >= 2) {
    value = value.substring(0, 2) + '/' + value.substring(2, 6);
  }
  input.value = value.substring(0, 7); // MM/YYYY
}

function mascaraCVV(input) {
  let value = input.value.replace(/\D/g, '');
  input.value = value.substring(0, 3);
}

function mascaraCPF(input) {
  let value = input.value.replace(/\D/g, '');
  if (value.length <= 11) {
    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    input.value = value.substring(0, 14);
  }
}

// Aplicar máscaras aos campos específicos
document.addEventListener('DOMContentLoaded', function() {
  const numeroCartao = document.querySelector('input[name="numero_cartao"]');
  const validadeCartao = document.querySelector('input[name="validade_cartao"]');
  const cvv = document.querySelector('input[name="cvv"]');
  const chavePix = document.querySelector('input[name="chave_pix"]');

  if (numeroCartao) {
    numeroCartao.addEventListener('input', function() { mascaraCartao(this); });
  }
  if (validadeCartao) {
    validadeCartao.addEventListener('input', function() { mascaraValidade(this); });
  }
  if (cvv) {
    cvv.addEventListener('input', function() { mascaraCVV(this); });
  }
  if (chavePix) {
    chavePix.addEventListener('input', function() { 
      if (/^\d+$/.test(this.value.replace(/\D/g, ''))) {
        mascaraCPF(this);
      }
    });
  }
});
</script>
</head>
<body>

<div class="logo"><i class="fa-solid fa-film"></i> Cinemax</div>

<div class="cadastro-container">
  <h2><i class="fa-solid fa-user-plus"></i> Crie sua Conta</h2>
  <form method="POST">
    <!-- Plano (pré-selecionado) -->
    <div class="mb-3">
      <label class="form-label">Plano Escolhido</label>
      <select name="plano" class="form-control select-especial" required>
        <option value="mensal" <?= $plano === 'mensal' ? 'selected' : '' ?>>Básico (R$ 19,90/Mês)</option>
        <option value="anual" <?= $plano === 'anual' ? 'selected' : '' ?>>Premium (R$ 39,90/Mês)</option>
      </select>
    </div>

    <!-- Dados Pessoais -->
    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="nome" class="form-control" placeholder="Digite seu nome completo" required>
    </div>
    <div class="mb-3">
      <label class="form-label">E-mail</label>
      <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <input type="password" name="senha" class="form-control" placeholder="Crie uma senha segura" required>
    </div>

    <!-- Meios de Pagamento -->
    <div class="mb-3">
      <label class="form-label">Meio de Pagamento</label>
      <select name="meio_pagamento" id="meio_pagamento" class="form-control select-especial" onchange="togglePagamento()" required>
        <option value="cartao">Cartão de Crédito</option>
        <option value="pix">Pix</option>
      </select>
    </div>

    <!-- Campos do Cartão -->
    <div id="cartao_fields" class="pagamento-fields">
      <div class="mb-3">
        <label class="form-label">Número do Cartão</label>
        <input type="text" name="numero_cartao" class="form-control" placeholder="1234 5678 9012 3456">
      </div>
      <div class="mb-3">
        <label class="form-label">Validade (MM/YYYY)</label>
        <input type="text" name="validade_cartao" class="form-control" placeholder="12/2025">
      </div>
      <div class="mb-3">
        <label class="form-label">CVV</label>
        <input type="text" name="cvv" class="form-control" placeholder="123">
      </div>
    </div>

    <!-- Campos do Pix -->
    <div id="pix_fields" class="pagamento-fields">
      <div class="mb-3">
        <label class="form-label">Chave Pix</label>
        <input type="text" name="chave_pix" class="form-control" placeholder="email@exemplo.com ou CPF">
      </div>
    </div>

    <button type="submit" class="btn-cadastrar"><i class="fa-solid fa-check"></i> Cadastrar e Pagar</button>
    <a href="planos.php" class="btn-voltar"><i class="fa-solid fa-arrow-left"></i> Voltar aos Planos</a>
  </form>
</div>

<footer>
  © 2025 Cinemax: Venha se divertir com os melhores filmes.
</footer>

</body>
</html>