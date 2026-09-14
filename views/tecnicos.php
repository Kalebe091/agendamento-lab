<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();
$current_user = get_app_user();

$database = new Database();
$db = $database->getConnection();

$tecnicos = [];
if ($db) {
    $stmt = $db->query("SELECT * FROM users ORDER BY role ASC, name ASC");
    if ($stmt) {
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$page_title = 'Técnicos';
$active_menu = 'tecnicos';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

        <!-- Main Content -->
        <main class="main-content admin-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Corpo Técnico</h1>
                    <p class="current-date">Gerenciamento de técnicos cadastrados e seus laboratórios</p>
                </div>
                <div class="header-right">
                    <?php if ($current_user['role'] === 'admin'): ?>
                    <button class="btn btn-primary" onclick="document.getElementById('add-tech-modal').classList.add('active')">
                        <i class="fa-solid fa-user-plus"></i> Novo Técnico
                    </button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-container">
                <div class="requests-grid">
                    <?php if (empty($tecnicos)): ?>
                        <div class="empty-state" style="grid-column: 1 / -1; padding: 3rem; text-align: center; background: var(--surface-color); border-radius: 12px; border: 1px dashed var(--border-color);">
                            <i class="fa-solid fa-users-slash" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <h3>Nenhum técnico encontrado</h3>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tecnicos as $tec): 
                            $bg_color = '0D8ABC';
                            if ($tec['role'] === 'tech_saude') $bg_color = '10b981';
                            elseif ($tec['role'] === 'tech_eng') $bg_color = '8b5cf6';
                            elseif ($tec['role'] === 'tech_info') $bg_color = '3b82f6';
                        ?>
                        <div class="request-card">
                            <div class="req-card-header">
                                <div style="display:flex; align-items:center; gap: 1rem;">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($tec['name']); ?>&background=<?php echo $bg_color; ?>&color=fff" style="width:40px; border-radius:50%;" alt="Tech">
                                    <div>
                                        <h3 style="margin-bottom:0;"><?php echo htmlspecialchars($tec['name']); ?></h3>
                                        <span style="font-size:0.8rem; color:var(--text-muted)"><?php echo htmlspecialchars($tec['title']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="req-card-actions" style="margin-top: 1rem;">
                                <button class="btn btn-secondary" style="flex:1;" onclick='openProfileModal(<?php echo json_encode($tec); ?>, "<?php echo $bg_color; ?>")'><i class="fa-solid fa-address-card"></i> Ver Perfil</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>


                </div>
            </div>
        </main>
    </div>

    <?php if ($current_user['role'] === 'admin'): ?>
    <!-- Modal Adicionar Técnico -->
    <div class="modal-overlay" id="add-tech-modal">
        <div class="modal-content" style="max-width: 500px; border-radius: 20px; overflow: hidden;">
            <div class="modal-header" style="background-color: var(--surface-hover); padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color);">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-user-plus" style="color: var(--primary-color);"></i> Novo Técnico</h2>
                <button class="close-modal" onclick="document.getElementById('add-tech-modal').classList.remove('active')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <form id="add-tech-form" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div class="form-group">
                        <label for="tech-name">Nome Completo</label>
                        <input type="text" id="tech-name" name="name" class="form-control" required placeholder="Ex: Roberto Alves">
                    </div>
                    <div class="form-group">
                        <label for="tech-username">Usuário (Login)</label>
                        <input type="text" id="tech-username" name="username" class="form-control" required placeholder="Ex: roberto.alves">
                    </div>
                    <div class="form-group">
                        <label for="tech-password">Senha</label>
                        <input type="password" id="tech-password" name="password" class="form-control" required placeholder="Digite uma senha">
                    </div>
                    <div class="form-group">
                        <label for="tech-area">Área de Atuação</label>
                        <select id="tech-area" name="role" class="form-control" required>
                            <option value="saude">Saúde</option>
                            <option value="eng">Engenharia</option>
                            <option value="info">Informática (TI)</option>
                        </select>
                    </div>
                    <div class="modal-actions" style="margin-top: 1rem; display: flex; justify-content: flex-end; gap: 1rem;">
                        <button type="button" class="btn btn-secondary cancel-modal" onclick="document.getElementById('add-tech-modal').classList.remove('active')" style="padding: 0.75rem 1.5rem; font-weight: 600;">Cancelar</button>
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-weight: 600;"><i class="fa-solid fa-save"></i> Salvar Técnico</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Perfil do Técnico -->
    <div class="modal-overlay" id="profile-modal">
        <div class="modal-content" style="max-width: 450px; border-radius: 20px; overflow: hidden; text-align: center;">
            <div class="modal-header" style="background-color: var(--surface-hover); padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                <button class="close-modal" onclick="document.getElementById('profile-modal').classList.remove('active')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding: 0 2rem 2.5rem 2rem; margin-top: -30px;">
                <img id="profile-img" src="" style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid var(--surface-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 1rem;">
                <h2 id="profile-name" style="color: var(--text-main); font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Nome</h2>
                <p id="profile-role-title" style="color: var(--primary-color); font-size: 0.95rem; font-weight: 600; margin-bottom: 1.5rem;">Função</p>
                
                <div style="background: var(--background-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; text-align: left;">
                    <div style="margin-bottom: 1rem;">
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 0.25rem;">Nome de Usuário (Login)</span>
                        <div style="color: var(--text-main); font-weight: 500; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-circle-user" style="color: var(--text-muted);"></i> <span id="profile-username"></span></div>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 0.25rem;">Privilégios de Acesso</span>
                        <div style="color: var(--text-main); font-weight: 500; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-key" style="color: var(--text-muted);"></i> <span id="profile-role-badge"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('add-tech-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('tech-name').value;
            const username = document.getElementById('tech-username').value;
            const password = document.getElementById('tech-password').value;
            let role = 'tech_eng';
            if (areaValue === 'saude') role = 'tech_saude';
            else if (areaValue === 'info') role = 'tech_info';
            else role = 'tech_eng';
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('name', name);
            formData.append('username', username);
            formData.append('password', password);
            formData.append('role', role);

            fetch('../controllers/create_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Erro: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Ocorreu um erro ao salvar o técnico.', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        window.openProfileModal = function(user, bgColor) {
            document.getElementById('profile-img').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=${bgColor}&color=fff&size=128`;
            document.getElementById('profile-name').textContent = user.name;
            document.getElementById('profile-role-title').textContent = user.title;
            document.getElementById('profile-username').textContent = user.username;
            
            let badgeStr = '';
            if (user.role === 'tech_saude') badgeStr = 'Técnico de Saúde';
            else if (user.role === 'tech_eng') badgeStr = 'Técnico de Engenharia';
            else if (user.role === 'tech_info') badgeStr = 'Técnico de Informática (TI)';
            else if (user.role === 'admin') badgeStr = 'Administrador do Sistema';
            document.getElementById('profile-role-badge').textContent = badgeStr;

            document.getElementById('profile-modal').classList.add('active');
        };
    </script>
    <?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
