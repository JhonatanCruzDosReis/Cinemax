<?php
include('conexao.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            // Redireciona direto para o Home
            header("Location: Home_filme.php");
            exit();
        } else {
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Login - Cinemax</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(to bottom, rgba(0,0,0,0.9), rgba(20,20,20,0.95)),
                  url('https://img.freepik.com/fotos-gratis/variedade-de-elementos-de-cinema-em-fundo-vermelho-com-espaco-de-copia_23-2148457848.jpg') center/cover no-repeat;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
      font-family: 'Poppins', sans-serif;
    }

    .login-card {
      background: rgba(15, 15, 15, 0.85);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 20px rgba(0,0,0,0.6);
      backdrop-filter: blur(8px);
      position: relative;
      overflow: hidden;
    }

    .login-card h2 {
      text-align: center;
      margin-bottom: 25px;
      font-weight: 600;
      color: #ffcc00;
      letter-spacing: 1px;
    }

    .form-control {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
    }
    .form-control:focus {
      box-shadow: 0 0 8px #ffcc00;
      background: rgba(255,255,255,0.15);
      color: #fff;
    }

    label {
      font-weight: 500;
      margin-bottom: 5px;
    }

    .btn-login {
      background: #ffcc00;
      color: #000;
      font-weight: 600;
      border: none;
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      transition: 0.3s;
    }
    .btn-login:hover {
      background: #ffd633;
      transform: scale(1.02);
      box-shadow: 0 0 10px rgba(255, 204, 0, 0.6);
    }

    .btn-link {
      display: block;
      position: relative;
      overflow: hidden;
      text-align: center;
      background: none;
      border: 2px solid #ffcc00;
      color: #ffcc00;
      font-weight: 600;
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      margin-top: 10px;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-link::before {
      content: "";
      position: absolute;
      top: 0;
      left: -75%;
      width: 50%;
      height: 100%;
      background: linear-gradient(120deg, transparent, rgba(255, 230, 100, 0.4), transparent);
      transform: skewX(-20deg);
    }

    .btn-link:hover::before {
      animation: brilho 1.5s ease-in-out;
    }

    @keyframes brilho {
      0% { left: -75%; }
      100% { left: 125%; }
    }

    .btn-link:hover {
      background: #ffcc00;
      color: #000;
      transform: scale(1.03);
      box-shadow: 0 0 10px rgba(255, 204, 0, 0.7);
    }

    .logo {
      display: block;
      margin: 0 auto 20px auto;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: url('https://elements-resized.envatousercontent.com/elements-cover-images/ef381742-b3ef-418d-a11e-4ca9be51ad4e?w=433&cf_fit=scale-down&q=85&format=auto&s=a7dd250900ead50f92fc72f59adc1c1dcfe9320d5fb2c10f461b81bf850cc63a') center/cover;
      box-shadow: 0 0 15px rgba(255, 204, 0, 0.6);
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
  <div class="login-card">
    <div class="logo"></div>
    <h2><i class="fa-solid fa-film me-2"></i>Entrar</h2>
    <form method="POST">
      <div class="mb-3">
        <label><i class="fa-solid fa-envelope me-1"></i>Email</label>
        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
      </div>
      <div class="mb-3">
        <label><i class="fa-solid fa-lock me-1"></i>Senha</label>
        <input type="password" name="senha" class="form-control" placeholder="********" required>
      </div>
      <button type="submit" class="btn-login mt-3">
        <i class="fa-solid fa-right-to-bracket me-1"></i>Entrar
      </button>
      <a href="planos.php" class="btn-link">
        <i class="fa-solid fa-user-plus me-1"></i>Não tem conta?
      </a>
      <a href="esqueci_senha.php" class="btn-link">
        <i class="fa-solid fa-key me-1"></i>Esqueceu senha?
      </a>
    </form>
  </div>

  <footer>
    <p>&copy; <?= date('Y') ?> Cinemax; O seu pequeno local de lazer</p>
  </footer>
</body>
</html>