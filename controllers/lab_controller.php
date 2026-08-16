<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$action = $_POST['action'] ?? '';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
    exit;
}

try {
    if ($action === 'create') {
        $name = $_POST['name'] ?? '';
        $capacity = $_POST['capacity'] ?? 0;
        $type = $_POST['type'] ?? 'geral';

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO laboratories (name, capacity, type) VALUES (:name, :capacity, :type)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':type', $type);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Laboratório criado com sucesso']);
    } 
    elseif ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $capacity = $_POST['capacity'] ?? 0;
        $type = $_POST['type'] ?? 'geral';

        if (!$id || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'ID e Nome são obrigatórios']);
            exit;
        }

        $stmt = $db->prepare("UPDATE laboratories SET name = :name, capacity = :capacity, type = :type WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Laboratório atualizado com sucesso']);
    } 
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID obrigatório']);
            exit;
        }

        // Antes de deletar, idealmente verificamos se não há agendamentos. 
        // Mas o MySQL lidaria com a restrição de chave estrangeira, então vamos capturar a exceção
        $stmt = $db->prepare("DELETE FROM laboratories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Laboratório excluído com sucesso']);
    } 
    else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir este laboratório pois existem agendamentos vinculados a ele.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    }
}
