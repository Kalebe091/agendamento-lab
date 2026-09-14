<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Apenas usuários logados podem fazer isso
require_auth();
$current_user = get_app_user();

// Apenas o admin pode adicionar técnicos
if ($current_user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem realizar esta ação.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($username) || empty($password) || !in_array($role, ['tech_saude', 'tech_eng', 'tech_info'])) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos ou incompletos. Preencha nome, usuário, senha e função.']);
        exit;
    }

    // Define o título de acordo com a área
    if ($role === 'tech_saude') {
        $title = 'Responsável: Saúde';
    } elseif ($role === 'tech_eng') {
        $title = 'Responsável: Engenharia';
    } else {
        $title = 'Responsável: Informática (TI)';
    }
    
    // Hash da senha definida pelo admin
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // Verifica se username já existe
        $check = $db->prepare("SELECT id FROM users WHERE username = :username");
        $check->bindParam(':username', $username);
        $check->execute();
        if ($check->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Este nome de usuário (login) já está em uso. Escolha outro.']);
            exit;
        }

        $query = "INSERT INTO users (username, password_hash, role, name, title) VALUES (:username, :hash, :role, :name, :title)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':hash', $password_hash);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':title', $title);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Técnico adicionado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
