<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requester_name = $_POST['requester_name'] ?? '';
    $lab_id = $_POST['lab_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time_start = $_POST['time_start'] ?? '';
    $time_end = $_POST['time_end'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validate
    if (empty($requester_name) || empty($lab_id) || empty($date) || empty($time_start) || empty($time_end) || empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios.']);
        exit;
    }

    $start_datetime = $date . ' ' . $time_start . ':00';
    $end_datetime = $date . ' ' . $time_end . ':00';

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // Verifica se há conflito de horários com agendamentos já aprovados
        $checkQuery = "SELECT COUNT(*) FROM schedules 
                       WHERE lab_id = :lab 
                       AND status = 'approved' 
                       AND start_time < :end 
                       AND end_time > :start";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':lab', $lab_id);
        $checkStmt->bindParam(':start', $start_datetime);
        $checkStmt->bindParam(':end', $end_datetime);
        $checkStmt->execute();
        
        $conflictCount = $checkStmt->fetchColumn();
        if ($conflictCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Este laboratório já possui um agendamento aprovado para este horário.']);
            exit;
        }

        $query = "INSERT INTO schedules (requester_name, lab_id, start_time, end_time, subject, notes, status) 
                  VALUES (:requester, :lab, :start, :end, :subject, :notes, 'pending')";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':requester', $requester_name);
        $stmt->bindParam(':lab', $lab_id);
        $stmt->bindParam(':start', $start_datetime);
        $stmt->bindParam(':end', $end_datetime);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Agendamento solicitado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
