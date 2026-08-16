<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Apenas administradores podem editar/excluir
$current_user = get_app_user();
if (!$current_user || $current_user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem realizar esta ação.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    
    if (!$id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM schedules WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Agendamento excluído com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir agendamento.']);
        }
        exit;
    }
    
    if ($action === 'edit') {
        $lab_id = $_POST['lab_id'] ?? null;
        $start_time = $_POST['start_time'] ?? null;
        $end_time = $_POST['end_time'] ?? null;
        
        if (!$lab_id || !$start_time || !$end_time) {
            echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
            exit;
        }

        // Conforme pedido, o admin pode editar sem verificar bloqueio/duplicidade de horários
        $query = "UPDATE schedules SET lab_id = :lab_id, start_time = :start_time, end_time = :end_time WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':lab_id', $lab_id);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Agendamento atualizado com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar agendamento.']);
        }
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Ação desconhecida.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
?>
