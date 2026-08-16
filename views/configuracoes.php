<?php
require_once '../includes/auth_functions.php';
require_auth();
$current_user = get_app_user();

if ($current_user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
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
                    <p class="current-date">Ajustes gerais do sistema</p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </header>

            <div class="admin-container">
                <div class="request-card" style="max-width: 600px;">
                    <h3 style="margin-bottom: 1rem;">Parâmetros de Agendamento</h3>
                    
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
                </div>
            </div>
        </main>
<?php require_once '../includes/footer.php'; ?>
