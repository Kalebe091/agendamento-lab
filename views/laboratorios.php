<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();
$current_user = get_app_user();

$database = new Database();
$db = $database->getConnection();

$laboratories = [];
if ($db) {
    $query = "SELECT * FROM laboratories";
   if ($current_user['role'] === 'tech_eng') {
    $query .= " WHERE type = 'engenharia'";
} else if ($current_user['role'] === 'tech_info') {
    $query .= " WHERE type = 'informatica'";
} else if ($current_user['role'] === 'tech_saude') {
    $query .= " WHERE type = 'saude'";
}

    
    $stmt = $db->query($query);
    if ($stmt) {
        $laboratories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$page_title = 'Laboratórios';
$active_menu = 'laboratorios';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

        <!-- Main Content -->
        <main class="main-content admin-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Gerenciar Laboratórios</h1>
                    <p class="current-date">Cadastro e configuração de espaços</p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="openLabModal()">
                        <i class="fa-solid fa-plus"></i> Novo Laboratório
                    </button>
                </div>
            </header>

            <div class="admin-container">
                <div class="requests-grid">
                    <?php if (empty($laboratories)): ?>
                        <div class="empty-state" style="grid-column: 1 / -1; padding: 3rem; text-align: center; background: var(--surface-color); border-radius: 12px; border: 1px dashed var(--border-color);">
                            <i class="fa-solid fa-building-circle-xmark" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <h3>Nenhum laboratório encontrado</h3>
                        </div>
                    <?php else: ?>
                        <?php foreach ($laboratories as $lab): 
                            $border_color = 'var(--primary-color)';
                            if ($lab['type'] === 'informatica') $border_color = 'var(--lab1-color)';
                            elseif ($lab['type'] === 'saude') $border_color = 'var(--lab3-color)';
                            elseif ($lab['type'] === 'engenharia') $border_color = 'var(--lab4-color)';
                        ?>
                        <div class="request-card" style="border-left: 4px solid <?php echo $border_color; ?>;">
                            <div class="req-card-body">
                                <h3><?php echo htmlspecialchars($lab['name']); ?></h3>
                                <p>Capacidade: <?php echo htmlspecialchars($lab['capacity']); ?> pessoas</p>
                                <div class="info-item">
                                    <span class="info-label">Tipo</span>
                                    <span class="info-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($lab['type']); ?></span>
                                </div>
                            </div>
                            <div class="req-card-actions" style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button class="btn btn-secondary" style="color: #ef4444; border-color: #fca5a5;" onclick="deleteLab(<?php echo $lab['id']; ?>)">Excluir</button>
                                <button class="btn btn-primary" onclick='openLabModal(<?php echo json_encode($lab); ?>)'>Editar</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Lab Modal -->
        <div id="lab-modal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px; border-radius: 20px; overflow: hidden;">
                <div class="modal-header" style="background-color: var(--surface-hover); padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color);">
                    <h2 id="lab-modal-title" style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-flask" style="color: var(--primary-color);"></i> <span>Novo Laboratório</span>
                    </h2>
                    <button class="close-modal" id="close-lab-btn" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body" style="padding: 2rem;">
                    <form id="lab-form" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <input type="hidden" id="lab-id" name="id">
                        <input type="hidden" id="lab-action" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Nome do Laboratório</label>
                            <input type="text" id="lab-name" name="name" class="form-control" required placeholder="Ex: Lab. de Informática 1">
                        </div>
                        <div class="form-group">
                            <label>Capacidade (pessoas)</label>
                            <input type="number" id="lab-capacity" name="capacity" class="form-control" required min="1" value="30">
                        </div>
                        <div class="form-group">
                            <label>Tipo</label>
                            <select id="lab-type" name="type" class="form-control" required>
                                <option value="informatica">Informática</option>
                                <option value="saude">Saúde</option>
                                <option value="engenharia">Engenharia</option>
                                <option value="geral">Geral</option>
                            </select>
                        </div>
                        <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" id="save-lab-btn" style="padding: 0.75rem 1.5rem; font-weight: 600;"><i class="fa-solid fa-save"></i> Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php
        $extra_js = "
        <script>
            const labModal = document.getElementById('lab-modal');
            const labForm = document.getElementById('lab-form');

            document.getElementById('close-lab-btn').addEventListener('click', () => {
                labModal.classList.remove('active');
            });

            labModal.addEventListener('click', (e) => {
                if (e.target === labModal) labModal.classList.remove('active');
            });

            window.openLabModal = function(lab = null) {
                if (lab) {
                    document.querySelector('#lab-modal-title span').textContent = 'Editar Laboratório';
                    document.getElementById('lab-action').value = 'update';
                    document.getElementById('lab-id').value = lab.id;
                    document.getElementById('lab-name').value = lab.name;
                    document.getElementById('lab-capacity').value = lab.capacity;
                    document.getElementById('lab-type').value = lab.type;
                } else {
                    document.querySelector('#lab-modal-title span').textContent = 'Novo Laboratório';
                    document.getElementById('lab-action').value = 'create';
                    document.getElementById('lab-id').value = '';
                    document.getElementById('lab-name').value = '';
                    document.getElementById('lab-capacity').value = '30';
                    document.getElementById('lab-type').value = 'informatica';
                }
                labModal.classList.add('active');
            };

            labForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(labForm);
                try {
                    const response = await fetch('../controllers/lab_controller.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        showToast(data.message, 'success');
                        labModal.classList.remove('active');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Erro ao salvar laboratório', 'error');
                    }
                } catch(error) {
                    showToast('Erro de conexão ao salvar', 'error');
                }
            });

            window.deleteLab = function(id) {
                if (typeof showConfirm !== 'function') {
                    alert('Erro: showConfirm não encontrado');
                    return;
                }
                showConfirm('Tem certeza que deseja excluir este laboratório permanentemente?', async () => {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('../controllers/lab_controller.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();
                        
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(data.message || 'Erro ao excluir laboratório', 'error');
                        }
                    } catch(error) {
                        showToast('Erro de conexão ao excluir', 'error');
                    }
                });
            };
        </script>
        ";
        ?>

<?php require_once '../includes/footer.php'; ?>
