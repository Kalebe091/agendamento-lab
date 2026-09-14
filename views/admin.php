<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();
$current_user = get_app_user();

$database = new Database();
$db = $database->getConnection();

$schedules = [];
if ($db) {
    // JOIN to get user details and lab details
    $query = "SELECT s.*, s.requester_name as requester, l.name as lab_name 
              FROM schedules s 
              JOIN laboratories l ON s.lab_id = l.id";
              
    if ($current_user['role'] === 'tech_eng') {
        $query .= " WHERE type = 'engenharia'";
    } else if ($current_user['role'] === 'tech_info') {
        $query .= " WHERE type = 'informatica'";
    } else if ($current_user['role'] === 'tech_saude') {
        $query .= " WHERE type = 'saude'";
    }

    
    $query .= " ORDER BY s.created_at DESC";
    $stmt = $db->query($query);
    if ($stmt) {
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$page_title = 'Painel Administrativo';
$active_menu = 'admin';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

        <!-- Main Content -->
        <main class="main-content admin-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Gerenciar Solicitações</h1>
                    <p class="current-date">Agendamentos pendentes de aprovação</p>
                </div>
                
                <div class="header-right">
                    <!-- Filtro removido já que agora as seções são fixas -->
                </div>
            </header>
            
            <!-- EXIBIR ERROS JS PARA DEBUG -->
            <script>
            window.addEventListener('error', function(e) {
                alert('JS Error: ' + e.message + ' at ' + e.filename + ':' + e.lineno);
            });
            </script>

            <div class="admin-container">
                <div class="section-title" style="margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
                    <h2>Pendentes</h2>
                </div>
                <div class="requests-grid" id="pending-grid" style="margin-bottom: 3rem;">
                    <?php 
                    $has_pending = false;
                    foreach($schedules as $req): 
                        if ($req['status'] === 'pending'):
                            $has_pending = true;
                    ?>
                    <div class="request-card" data-status="pending">
                        <div class="req-card-header">
                            <span class="status-badge status-pending">Pendente</span>
                            <span class="req-date"><i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y', strtotime($req['start_time'])); ?></span>
                        </div>
                        <div class="req-card-body">
                            <h3><?php echo htmlspecialchars($req['lab_name']); ?></h3>
                            <div class="info-item">
                                <span class="info-label">Solicitante</span>
                                <span class="info-value"><?php echo htmlspecialchars($req['requester']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Horário</span>
                                <span class="info-value"><?php echo date('H:i', strtotime($req['start_time'])); ?> às <?php echo date('H:i', strtotime($req['end_time'])); ?></span>
                            </div>
                        </div>
                        <div class="req-card-actions">
                            <button class="btn btn-secondary" onclick="openRejectModal(<?php echo $req['id']; ?>)"><i class="fa-solid fa-xmark"></i> Recusar</button>
                            <button class="btn btn-primary" onclick="openApproveModal(<?php echo $req['id']; ?>)"><i class="fa-solid fa-check"></i> Aprovar</button>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                
                <?php if (!$has_pending): ?>
                <div id="empty-state" class="empty-state" style="margin-bottom: 3rem;">
                    <i class="fa-regular fa-face-smile-wink"></i>
                    <h3>Tudo limpo por aqui!</h3>
                    <p>Não há novas solicitações pendentes no momento.</p>
                </div>
                <?php endif; ?>

                <div class="section-title" style="margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
                    <h2>Aprovados (Histórico)</h2>
                </div>
                <div class="requests-grid" id="approved-grid">
                    <?php 
                    $has_approved = false;
                    foreach($schedules as $req): 
                        if ($req['status'] === 'approved'):
                            $has_approved = true;
                            $safeTitle = htmlspecialchars($req['subject'] ?? 'Agendamento', ENT_QUOTES); 
                    ?>
                    <div class="request-card" data-status="approved">
                        <div class="req-card-header">
                            <span class="status-badge status-approved">Aprovado</span>
                            <span class="req-date"><i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y', strtotime($req['start_time'])); ?></span>
                        </div>
                        <div class="req-card-body">
                            <h3><?php echo htmlspecialchars($req['lab_name']); ?></h3>
                            <div class="info-item">
                                <span class="info-label">Disciplina / Motivo</span>
                                <span class="info-value"><?php echo htmlspecialchars($req['subject']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Solicitante</span>
                                <span class="info-value"><?php echo htmlspecialchars($req['requester']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Horário</span>
                                <span class="info-value"><?php echo date('H:i', strtotime($req['start_time'])); ?> às <?php echo date('H:i', strtotime($req['end_time'])); ?></span>
                            </div>
                        </div>
                        <div class="req-card-actions">
                            <button class="btn btn-secondary" onclick="deleteEvent(<?php echo $req['id']; ?>)"><i class="fa-solid fa-trash"></i> Excluir</button>
                            <button class="btn btn-primary" onclick="openEditEventModal(<?php echo $req['id']; ?>, '<?php echo $req['start_time']; ?>', '<?php echo $req['end_time']; ?>', '<?php echo $req['lab_id']; ?>', '<?php echo $safeTitle; ?>')"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>

                <?php if (!$has_approved): ?>
                <div id="empty-state-approved" class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>Nenhum agendamento aprovado no histórico.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
<?php require_once '../includes/footer.php'; ?>
