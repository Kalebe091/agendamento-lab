<?php
require_once '../includes/auth_functions.php';

// Processar Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $query = "SELECT id, username, password_hash, role, name, title FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['title'] = $user['title'];
                $_SESSION['user_id'] = $user['id'];
                
                // Redireciona para o dashboard
                header("Location: ../views/dashboard.php");
                exit;
            }
        }
    }
    
    // Redireciona de volta com erro
    header("Location: ../views/login.php?error=invalid");
    exit;
}

// Processar Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../views/login.php");
    exit;
}

// Redirecionamento padrão se acessado diretamente sem ação
header("Location: ../views/login.php");
exit;
?>
