<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';

require_auth();
$current_user = get_app_user();

$database = new Database();
$db = $database->getConnection();

$total_schedules = 0;
$total_labs = 0;
$total_users = 0;
$total_hours = 0;
$recent_requests = [];

if ($db) {
    $role = $current_user['role'] ?? 'admin';
    $lab_filter_sql = "";
    $schedules_filter_sql = "";

    if ($role === 'tech_saude') {
        $lab_filter_sql = "WHERE type = 'saude'";
        $schedules_filter_sql = "WHERE lab_id IN (SELECT id FROM laboratories WHERE type = 'saude')";
    } elseif ($role === 'tech_eng') {
        $lab_filter_sql = "WHERE type IN ('engenharia', 'informatica')";
        $schedules_filter_sql = "WHERE lab_id IN (SELECT id FROM laboratories WHERE type IN ('engenharia', 'informatica'))";
    }

    // 1. Agendamentos
    $schedules_where = $schedules_filter_sql ? $schedules_filter_sql : "";
    $stmt = $db->query("SELECT COUNT(*) FROM schedules $schedules_where");
    $total_schedules = $stmt->fetchColumn();

    // 2. Laboratórios
    $labs_where = $lab_filter_sql ? $lab_filter_sql : "";
    $stmt = $db->query("SELECT COUNT(*) FROM laboratories $labs_where");
    $total_labs = $stmt->fetchColumn();

    // 3. Técnicos/Usuários (Mostrado para todos como info global)
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();

    // 4. Horas Utilizadas (Aprovados)
    $hours_where = $schedules_filter_sql ? $schedules_filter_sql . " AND status = 'approved'" : "WHERE status = 'approved'";
    $stmt = $db->query("SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)), 0) FROM schedules $hours_where");
    $total_hours = $stmt->fetchColumn();

    // 5. Solicitações Recentes Pendentes
    $recent_where = $schedules_filter_sql ? $schedules_filter_sql . " AND s.status = 'pending'" : "WHERE s.status = 'pending'";
    $recent_query = "SELECT s.*, l.name as lab_name FROM schedules s 
                     JOIN laboratories l ON s.lab_id = l.id 
                     $recent_where 
                     ORDER BY s.created_at ASC LIMIT 5";
    $recent_requests = $db->query($recent_query)->fetchAll(PDO::FETCH_ASSOC);

    // 6. Dados para o Gráfico de Status
    $status_query = "SELECT status, COUNT(*) as count FROM schedules $schedules_where GROUP BY status";
    $status_data = $db->query($status_query)->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. Dados para o Gráfico de Laboratórios
    $lab_chart_query = "SELECT l.name, COUNT(s.id) as count 
                        FROM schedules s 
                        JOIN laboratories l ON s.lab_id = l.id 
                        $schedules_where 
                        GROUP BY l.id";
    $lab_chart_data = $db->query($lab_chart_query)->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Dashboard';
$active_menu = 'dashboard';

$extra_css = '
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: var(--surface-color);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .icon-blue { background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .icon-green { background-color: rgba(16, 185, 129, 0.1); color: #10b981; }
        .icon-purple { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        .icon-orange { background-color: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        
        .stat-info h4 {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        .stat-info span {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .recent-requests {
            background-color: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .recent-requests h3 {
            color: var(--text-main);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .requests-table {
            width: 100%;
            border-collapse: collapse;
        }
        .requests-table th, .requests-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }
        .requests-table th {
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .requests-table tr:last-child td {
            border-bottom: none;
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .chart-card {
            background-color: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .chart-card h3 {
            color: var(--text-main);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

        <!-- Main Content -->
        <main class="main-content admin-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Visão Geral <?php echo $current_user['role'] === 'tech_saude' ? '- Área da Saúde' : ($current_user['role'] === 'tech_eng' ? '- Eng/Info' : ''); ?></h1>
                    <p class="current-date">Resumo e estatísticas das atividades dos laboratórios</p>
                </div>
            </header>

            <div class="admin-container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Agendamentos</h4>
                            <span><?php echo $total_schedules; ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Laboratórios Ativos</h4>
                            <span><?php echo $total_labs; ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-purple">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Técnicos</h4>
                            <span><?php echo $total_users; ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-orange">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Horas Utilizadas</h4>
                            <span><?php echo $total_hours; ?>h</span>
                        </div>
                    </div>
                </div>

                <div class="charts-container">
                    <div class="chart-card">
                        <h3><i class="fa-solid fa-chart-pie"></i> Status das Solicitações</h3>
                        <div style="position: relative; height: 280px; width: 100%;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3><i class="fa-solid fa-chart-bar"></i> Agendamentos por Laboratório</h3>
                        <div style="position: relative; height: 280px; width: 100%;">
                            <canvas id="labChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="recent-requests">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> Solicitações Pendentes (Atalho)</h3>
                    
                    <?php if (count($recent_requests) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table class="requests-table">
                                <thead>
                                    <tr>
                                        <th>Solicitante / Disciplina</th>
                                        <th>Laboratório</th>
                                        <th>Data / Horário</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_requests as $req): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($req['requester_name']); ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($req['subject']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($req['lab_name']); ?></td>
                                        <td>
                                            <div><?php echo date('d/m/Y', strtotime($req['start_time'])); ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                                <?php echo date('H:i', strtotime($req['start_time'])); ?> às <?php echo date('H:i', strtotime($req['end_time'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="btn btn-secondary btn-sm" onclick="openRejectModal(<?php echo $req['id']; ?>)" title="Recusar"><i class="fa-solid fa-xmark"></i></button>
                                                <button class="btn btn-primary btn-sm" onclick="openApproveModal(<?php echo $req['id']; ?>)" title="Aprovar"><i class="fa-solid fa-check"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 1rem; text-align: right;">
                            <a href="admin.php" class="btn btn-secondary" style="font-size: 0.9rem;">Ver todas as solicitações</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="height: 200px; border: 1px dashed var(--border-color); border-radius: 8px; margin-top: 1rem;">
                            <i class="fa-regular fa-face-smile-wink" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h4 style="color: var(--text-main);">Tudo limpo por aqui!</h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Nenhuma solicitação pendente para a sua área.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Dados recebidos do PHP
        const statusDataRaw = <?php echo json_encode($status_data ?? []); ?>;
        const labDataRaw = <?php echo json_encode($lab_chart_data ?? []); ?>;
        
        // Cores do Tema
        const themeColors = {
            approved: '#10b981', // Verde
            pending: '#f59e0b', // Laranja
            rejected: '#ef4444', // Vermelho
            labs: ['#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b', '#6366f1']
        };

        // Preparar Dados do Gráfico de Status
        const statusMap = { 'approved': 0, 'pending': 0, 'rejected': 0 };
        statusDataRaw.forEach(row => {
            statusMap[row.status] = parseInt(row.count);
        });

        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Aprovados', 'Pendentes', 'Recusados'],
                datasets: [{
                    data: [statusMap['approved'], statusMap['pending'], statusMap['rejected']],
                    backgroundColor: [themeColors.approved, themeColors.pending, themeColors.rejected],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#9ca3af' } }
                },
                cutout: '70%'
            }
        });

        // Preparar Dados do Gráfico de Laboratórios
        const labLabels = labDataRaw.map(row => row.name);
        const labCounts = labDataRaw.map(row => parseInt(row.count));

        const ctxLab = document.getElementById('labChart').getContext('2d');
        new Chart(ctxLab, {
            type: 'bar',
            data: {
                labels: labLabels,
                datasets: [{
                    label: 'Número de Agendamentos',
                    data: labCounts,
                    backgroundColor: themeColors.labs,
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, color: '#9ca3af' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
