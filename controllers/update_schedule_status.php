<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Apenas usuários logados podem fazer isso
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !in_array($status, ['approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $query = "UPDATE schedules SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o banco de dados.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
