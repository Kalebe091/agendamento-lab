<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$laboratories = [];
$schedules = [];
if ($db) {
    // Busca Laboratórios
    $stmt = $db->query("SELECT * FROM laboratories ORDER BY name ASC");
    if ($stmt) {
        $laboratories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Busca Agendamentos Aprovados
    $query = "SELECT s.id, s.requester_name, s.subject, s.start_time, s.end_time, l.name as lab_name, l.id as lab_id 
              FROM schedules s 
              JOIN laboratories l ON s.lab_id = l.id 
              WHERE s.status = 'approved'";
    $stmt_sch = $db->query($query);
    if ($stmt_sch) {
        $schedules = $stmt_sch->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Laboratórios</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Public Navigation Bar -->
    <nav class="public-navbar">
        <div class="navbar-container">
            <div class="logo">
                <i class="fa-solid fa-microscope logo-icon"></i>
                <h2>LabSchedule</h2>
            </div>
            <div class="navbar-actions">
                <a href="views/login.php" class="btn btn-primary">
                    <i class="fa-solid fa-user-lock"></i> Acesso Restrito
                </a>
            </div>
        </div>
    </nav>

    <div class="app-container public-view">
        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Agenda dos Laboratórios</h1>
                    <p id="current-date-display" class="current-date">17 - 22 de Agosto, 2026</p>
                </div>
                
                <div class="header-right">
                    <button id="btn-request-schedule" class="btn btn-primary" style="margin-right: 1.5rem;">
                        <i class="fa-solid fa-calendar-plus"></i> Solicitar Agendamento
                    </button>
                    <div class="filter-group">
                        <label for="lab-filter"><i class="fa-solid fa-filter"></i> Filtrar:</label>
                        <select id="lab-filter" class="custom-select">
                            <option value="all">Todos os Laboratórios</option>
                            <?php foreach($laboratories as $lab): ?>
                            <option value="<?php echo htmlspecialchars($lab['id']); ?>"><?php echo htmlspecialchars($lab['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </header>

            <div class="calendar-container">
                <!-- Navigation -->
                <div class="calendar-nav">
                    <button id="prev-week-btn" class="nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <h3 id="week-display">Semana Atual</h3>
                    <button id="next-week-btn" class="nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-grid">
                    <!-- Time Column -->
                    <div class="time-col">
                        <div class="time-header timezone-spacer">GMT-03</div>
                        <div class="time-slots">
                            <!-- JS will populate times like 08:00, 09:00 -->
                        </div>
                    </div>
                    
                    <!-- Days Columns -->
                    <div class="days-container" id="days-container">
                        <!-- JS will populate days and events -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Formulário de Solicitação -->
    <div id="request-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Solicitar Agendamento</h2>
                <button class="close-modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="schedule-request-form">
                    <div class="form-group">
                        <label for="req-name">Seu Nome / Professor</label>
                        <input type="text" id="req-name" class="form-control" placeholder="Ex: Prof. João Silva" required>
                    </div>
                    <div class="form-group">
                        <label for="req-lab">Laboratório</label>
                        <select id="req-lab" class="form-control" name="lab_id" required>
                            <option value="" disabled selected>Selecione um laboratório</option>
                            <?php foreach($laboratories as $lab): ?>
                            <option value="<?php echo htmlspecialchars($lab['id']); ?>"><?php echo htmlspecialchars($lab['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="lab-responsible-info" class="lab-responsible-text" style="display: none; margin-top: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                            <i class="fa-solid fa-user-shield"></i> Responsável: <strong id="lab-responsible-name"></strong>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="req-date">Data</label>
                            <input type="date" id="req-date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="req-time-start">Início</label>
                            <input type="time" id="req-time-start" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="req-time-end">Término</label>
                            <input type="time" id="req-time-end" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="req-subject">Disciplina / Motivo</label>
                        <input type="text" id="req-subject" class="form-control" placeholder="Ex: Aula de Banco de Dados" required>
                    </div>
                    <div class="form-group">
                        <label for="req-notes">Observações adicionais (opcional)</label>
                        <textarea id="req-notes" class="form-control" rows="3" placeholder="Softwares específicos, equipamentos, etc."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary cancel-modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Injeta os agendamentos aprovados do banco de dados na aplicação JS
        window.DB_EVENTS = <?php echo json_encode($schedules); ?>;
        // Injeta a lista de laboratórios com suas categorias
        window.DB_LABS = <?php echo json_encode($laboratories); ?>;
    </script>
    <script src="assets/js/app_global.js?v=3"></script>
    <script src="assets/js/app.js?v=3"></script>
</body>
</html>