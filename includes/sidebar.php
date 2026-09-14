<?php
// Calcula dinamicamente o número de solicitações pendentes
$pending_count = 0;
if (isset($db)) { // Se a conexão $db já existe no arquivo que incluiu o sidebar
    $role = $current_user['role'] ?? 'admin';
    $schedules_filter_sql = "";

    if ($role === 'tech_saude') {
        $schedules_filter_sql = "AND lab_id IN (SELECT id FROM laboratories WHERE type = 'saude')";
    } elseif ($role === 'tech_eng') {
        $schedules_filter_sql = "AND lab_id IN (SELECT id FROM laboratories WHERE type = 'engenharia')";
    } elseif ($role === 'tech_info') {
        $schedules_filter_sql = "AND lab_id IN (SELECT id FROM laboratories WHERE type = 'informatica')";
    }

    $stmt_pending = $db->query("SELECT COUNT(*) FROM schedules WHERE status = 'pending' $schedules_filter_sql");
    $pending_count = $stmt_pending->fetchColumn();
}
?>
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-microscope"></i>
                <h2>LabSchedule</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?php echo (isset($active_menu) && $active_menu === 'dashboard') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a>
                <a href="admin.php" class="nav-item <?php echo (isset($active_menu) && $active_menu === 'admin') ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-list"></i> <span>Solicitações <?php if($pending_count > 0): ?><span class="badge"><?php echo $pending_count; ?></span><?php endif; ?></span></a>
                <a href="agenda-mensal.php" class="nav-item <?php echo (isset($active_menu) && $active_menu === 'agenda-mensal') ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days"></i> <span>Agenda Mensal</span></a>
                <a href="laboratorios.php" class="nav-item <?php echo (isset($active_menu) && $active_menu === 'laboratorios') ? 'active' : ''; ?>"><i class="fa-solid fa-building"></i> <span>Laboratórios</span></a>
                <a href="tecnicos.php" class="nav-item <?php echo (isset($active_menu) && $active_menu === 'tecnicos') ? 'active' : ''; ?>"><i class="fa-solid fa-user-gear"></i> <span>Técnicos</span></a>
                <?php if ($current_user['role'] === 'admin'): ?>
                <a href="configuracoes.php" class="nav-item <?php echo (isset($active_menu) && $active_menu === 'configuracoes') ? 'active' : ''; ?>"><i class="fa-solid fa-gear"></i> <span>Configurações</span></a>
                <?php endif; ?>
                <a href="../controllers/auth_controller.php?action=logout" class="nav-item" style="color: #ef4444; margin-top: auto;"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Sair</span></a>
            </nav>
            
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($current_user['name']); ?>&background=0D8ABC&color=fff" alt="User Profile">
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($current_user['name']); ?></strong>
                    <span style="font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; max-width: 130px;" title="<?php echo htmlspecialchars($current_user['title']); ?>">
                        <?php echo htmlspecialchars($current_user['title']); ?>
                    </span>
                </div>
            </div>
        </aside>
