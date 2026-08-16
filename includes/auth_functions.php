<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// Função para proteger as páginas (exigir login)
function require_auth() {
    if (!isset($_SESSION['user'])) {
        // Redireciona para a tela de login
        $path_to_login = strpos($_SERVER['PHP_SELF'], '/views/') !== false ? 'login.php' : 'views/login.php';
        header("Location: " . $path_to_login);
        exit;
    }
}

// Obter dados do usuário atual
function get_app_user() {
    if (!isset($_SESSION['user'])) return null;
    return [
        'username' => $_SESSION['user'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'],
        'title' => $_SESSION['title']
    ];
}
?>
