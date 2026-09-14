<?php
require_once '../includes/auth_functions.php';

// Se já estiver logado, vai direto pro painel
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$login_error = '';
if (isset($_GET['error']) && $_GET['error'] === 'invalid') {
    $login_error = "Credenciais inválidas. Tente novamente.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LabSchedule</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-image: linear-gradient(135deg, var(--bg-color) 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 4px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-header {
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .login-header h2 {
            color: var(--text-main);
            font-size: 1.5rem;
            font-weight: 700;
        }
        .login-form {
            text-align: left;
        }
        .login-form .form-group {
            margin-bottom: 1.25rem;
        }
        .error-msg {
            background: #fef2f2;
            color: #ef4444;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            text-align: center;
            border: 1px solid #f87171;
        }
        .login-info {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            background: rgba(0,0,0,0.03);
            padding: 1rem;
            border-radius: 8px;
            text-align: left;
        }
        .login-info p { margin-bottom: 0.25rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fa-solid fa-microscope"></i>
            <h2>LabSchedule</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Acesso Administrativo</p>
        </div>
        
        <?php if (!empty($login_error)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../controllers/auth_controller.php" class="login-form">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Digite seu usuário">
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Digite sua senha">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem; margin-top: 0.5rem;">
                Entrar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
        
        <div style="margin-top: 1.5rem;">
            <a href="../index.php" style="color: var(--primary-color); font-size: 0.85rem; text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Voltar para página inicial
            </a>
        </div>
    </div>
</body>
</html>
