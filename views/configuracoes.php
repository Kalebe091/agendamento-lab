<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();
$current_user = get_app_user();

if ($current_user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$laboratories = [];
if ($db) {
    $stmt = $db->query("SELECT id, name FROM laboratories ORDER BY name ASC");
    if ($stmt) {
        $laboratories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$page_title = 'Configurações';
$active_menu = 'configuracoes';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

        <!-- Main Content -->
        <main class="main-content admin-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Configurações</h1>
                    <p class="current-date">Ajustes gerais e exportação de dados do sistema</p>
                </div>
            </header>

            <div class="admin-container">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; align-items: start;">
                    
                    <!-- Card 1: Parâmetros do Sistema -->
                    <div class="request-card">
                        <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-sliders" style="color: var(--primary-color);"></i> Parâmetros de Agendamento
                        </h3>
                        
                        <form onsubmit="event.preventDefault(); showToast('Configurações salvas com sucesso!', 'success');">
                            <div class="form-group">
                                <label>Horário de Abertura Padrão</label>
                                <input type="time" value="08:00" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Horário de Fechamento Padrão</label>
                                <input type="time" value="22:00" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Notificações por Email</label>
                                <select class="form-control">
                                    <option>Ativadas</option>
                                    <option>Desativadas</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" style="margin-top: 1rem; width: 100%; justify-content: center;">
                                <i class="fa-solid fa-save"></i> Salvar Alterações
                            </button>
                        </form>
                    </div>

                    <!-- Card 2: Exportação de Agendamentos (Excel / CSV) -->
                    <div class="request-card">
                        <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Exportar Relatório de Agendamentos
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                            Gere uma planilha compatível com Microsoft Excel contendo os registros do sistema.
                        </p>
                        
                        <form id="export-form" action="../controllers/export_schedules.php" method="GET" target="_blank">
                            <div class="form-group">
                                <label for="exp-status"><i class="fa-solid fa-filter"></i> Status do Agendamento</label>
                                <select id="exp-status" name="status" class="form-control">
                                    <option value="all">Todos os Status (Aprovados, Pendentes, Rejeitados)</option>
                                    <option value="approved" selected>Apenas Aprovados</option>
                                    <option value="pending">Apenas Pendentes</option>
                                    <option value="rejected">Apenas Rejeitados</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="exp-lab"><i class="fa-solid fa-flask"></i> Laboratório</label>
                                <select id="exp-lab" name="lab_id" class="form-control">
                                    <option value="all">Todos os Laboratórios</option>
                                    <?php foreach ($laboratories as $lab): ?>
                                        <option value="<?php echo $lab['id']; ?>"><?php echo htmlspecialchars($lab['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="exp-start">Data Inicial (Opcional)</label>
                                    <input type="date" id="exp-start" name="start_date" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="exp-end">Data Final (Opcional)</label>
                                    <input type="date" id="exp-end" name="end_date" class="form-control">
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary" style="background-color: #10b981; border-color: #10b981; width: 100%; justify-content: center; padding: 0.75rem;">
                                    <i class="fa-solid fa-file-csv"></i> Baixar Planilha Excel (.csv)
                                </button>
                                <a href="../controllers/export_schedules.php" class="btn btn-secondary" style="width: 100%; justify-content: center; text-decoration: none; padding: 0.65rem;">
                                    <i class="fa-solid fa-download"></i> Exportação Rápida (Todos os Dados)
                                </a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </main>
<?php require_once '../includes/footer.php'; ?>

