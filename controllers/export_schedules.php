<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();

$current_user = get_app_user();
if (!$current_user || $current_user['role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem exportar relatórios.");
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão com o banco de dados.");
}

// Parâmetros de filtro opcionais
$status_filter = $_GET['status'] ?? 'all';
$lab_filter = $_GET['lab_id'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$where_clauses = [];
$params = [];

if ($status_filter !== 'all') {
    $where_clauses[] = "s.status = :status";
    $params[':status'] = $status_filter;
}

if ($lab_filter !== 'all') {
    $where_clauses[] = "s.lab_id = :lab_id";
    $params[':lab_id'] = $lab_filter;
}

if (!empty($start_date)) {
    $where_clauses[] = "s.start_time >= :start_date";
    $params[':start_date'] = $start_date . ' 00:00:00';
}

if (!empty($end_date)) {
    $where_clauses[] = "s.end_time <= :end_date";
    $params[':end_date'] = $end_date . ' 23:59:59';
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$query = "SELECT s.id, s.requester_name, l.name as lab_name, l.type as lab_type, s.subject, 
                 s.start_time, s.end_time, s.status, s.notes, s.created_at
          FROM schedules s
          LEFT JOIN laboratories l ON s.lab_id = l.id
          $where_sql
          ORDER BY s.start_time DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeçalhos HTTP para download do arquivo CSV/Excel
$filename = "agendamentos_export_" . date('Y-m-d_H-i') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Abrir output stream
$output = fopen('php://output', 'w');

// Escrever BOM UTF-8 para garantir acentuação correta no Microsoft Excel
fwrite($output, "\xEF\xBB\xBF");

// Escrever cabeçalho das colunas (separado por ponto e vírgula ';')
fputcsv($output, [
    'ID',
    'Solicitante',
    'Laboratório',
    'Categoria do Lab',
    'Disciplina / Motivo',
    'Data de Início',
    'Hora de Início',
    'Data de Término',
    'Hora de Término',
    'Status',
    'Observações',
    'Data da Solicitação'
], ';');

// Traduzir categorias e status
$status_map = [
    'approved' => 'Aprovado',
    'pending' => 'Pendente',
    'rejected' => 'Rejeitado'
];

$type_map = [
    'saude' => 'Saúde',
    'engenharia' => 'Engenharia',
    'informatica' => 'Informática / TI',
    'geral' => 'Geral'
];

// Escrever linhas de dados
foreach ($schedules as $row) {
    $start_ts = strtotime($row['start_time']);
    $end_ts = strtotime($row['end_time']);
    $created_ts = strtotime($row['created_at']);

    fputcsv($output, [
        $row['id'],
        $row['requester_name'],
        $row['lab_name'] ?? 'Não especificado',
        $type_map[$row['lab_type']] ?? $row['lab_type'],
        $row['subject'],
        date('d/m/Y', $start_ts),
        date('H:i', $start_ts),
        date('d/m/Y', $end_ts),
        date('H:i', $end_ts),
        $status_map[$row['status']] ?? $row['status'],
        $row['notes'] ?? '',
        $created_ts ? date('d/m/Y H:i', $created_ts) : ''
    ], ';');
}

fclose($output);
exit;
