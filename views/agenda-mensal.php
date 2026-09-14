<?php
require_once '../includes/auth_functions.php';
require_once '../config/database.php';
require_auth();
$current_user = get_app_user();

$database = new Database();
$db = $database->getConnection();

$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

// Validar mês e ano
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
    $year = (int)date('Y');
}

$today_day = (int)date('d');
$today_month = (int)date('m');
$today_year = (int)date('Y');

$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Filtro de laboratório baseado no cargo (igual ao dashboard)
$role = $current_user['role'] ?? 'admin';
$schedules_filter_sql = "";
if ($role === 'tech_saude') {
    $schedules_filter_sql = "AND l.type = 'saude'";
} elseif ($role === 'tech_eng') {
    $schedules_filter_sql = "AND l.type = 'engenharia'";
} elseif ($role === 'tech_info') {
    $schedules_filter_sql = "AND l.type = 'informatica'";
}

// Buscar eventos do mês atual
$start_date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01 00:00:00";
$end_date = date('Y-m-t 23:59:59', strtotime($start_date));

$query = "SELECT s.id, s.requester_name as prof, s.subject as title, s.start_time, s.end_time, s.lab_id, l.name as labName 
          FROM schedules s 
          JOIN laboratories l ON s.lab_id = l.id 
          WHERE s.status = 'approved' 
          AND s.start_time >= :start_date 
          AND s.start_time <= :end_date 
          $schedules_filter_sql";

$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar eventos por dia
$events_by_day = [];
foreach ($events as $ev) {
    $day = (int)date('d', strtotime($ev['start_time']));
    if (!isset($events_by_day[$day])) {
        $events_by_day[$day] = [];
    }
    
    // Mapeamento circular de 1 a 6 para garantir cor válida mesmo para lab_id > 4
    $lab_color_idx = (($ev['lab_id'] - 1) % 6) + 1;

    // Preparar objeto para JS
    $eventObj = [
        'id' => $ev['id'],
        'title' => $ev['title'],
        'prof' => $ev['prof'],
        'startTimeStr' => date('H:i', strtotime($ev['start_time'])),
        'endTimeStr' => date('H:i', strtotime($ev['end_time'])),
        'labName' => $ev['labName'],
        'labId' => $ev['lab_id'],
        'rawStart' => $ev['start_time'],
        'rawEnd' => $ev['end_time'],
        'isAdmin' => ($role === 'admin')
    ];
    
    $events_by_day[$day][] = [
        'display' => date('H:i', strtotime($ev['start_time'])) . " - " . $ev['title'],
        'lab_class' => 'event-lab' . $lab_color_idx,
        'obj' => $eventObj
    ];
}

$page_title = 'Agenda Mensal';
$active_menu = 'agenda-mensal';

$meses = ["", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
$mes_nome = $meses[$month];

// Configurar grade do calendário
$first_day_timestamp = mktime(0,0,0, $month, 1, $year);
$days_in_month = date('t', $first_day_timestamp);
$first_day_of_week = date('w', $first_day_timestamp); // 0 (Dom) a 6 (Sáb)

// Mês anterior (para os dias de preenchimento)
$days_in_prev_month = date('t', mktime(0,0,0, $prev_month, 1, $prev_year));

$extra_css = '
    <style>
        .calendar-header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            background: var(--surface-color);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
        }
        .calendar-header-controls h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }
        .calendar-nav-btn {
            background: none;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            color: var(--text-main);
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .calendar-nav-btn:hover {
            background: var(--surface-hover);
        }
        .monthly-grid-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--border-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .monthly-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(110px, 1fr));
            min-width: 770px;
            gap: 0;
            background: var(--border-color);
            overflow: hidden;
        }
        .monthly-day-header {
            background: var(--surface-hover);
            padding: 0.75rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--text-muted);
        }
        .monthly-day {
            background: var(--surface-color);
            min-height: 120px;
            padding: 0.5rem;
            border-right: 1px solid var(--border-color);
            border-top: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        .monthly-day.other-month {
            background: var(--bg-color);
            color: var(--text-muted);
            opacity: 0.6;
        }
        .monthly-day.today {
            background-color: #f0fdf4;
        }
        .monthly-day.today .day-number {
            background-color: var(--primary-color);
            color: #ffffff;
            font-weight: 700;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        .monthly-day:nth-child(7n) { border-right: none; }
        .day-number {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .event-badge {
            font-size: 0.72rem;
            font-weight: 500;
            padding: 0.3rem 0.5rem;
            border-radius: 6px;
            margin-bottom: 0.35rem;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: transform 0.15s, filter 0.15s;
        }
        .event-badge:hover {
            transform: translateY(-1px);
            filter: brightness(0.92);
        }
        .event-lab1 { background-color: #3b82f6; }
        .event-lab2 { background-color: #8b5cf6; }
        .event-lab3 { background-color: #ec4899; }
        .event-lab4 { background-color: #f59e0b; }
        .event-lab5 { background-color: #10b981; }
        .event-lab6 { background-color: #06b6d4; }
    </style>
';

// Obter laboratórios para injetar no JS do Modal de Edição global
$stmt_labs = $db->query("SELECT * FROM laboratories ORDER BY name ASC");
$laboratories = $stmt_labs ? $stmt_labs->fetchAll(PDO::FETCH_ASSOC) : [];
$extra_js = '<script>window.DB_LABS = ' . json_encode($laboratories) . '; window.CURRENT_USER_ROLE = ' . json_encode($role) . ';</script>';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

        <!-- Main Content -->
        <main class="main-content admin-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Agenda Mensal</h1>
                    <p class="current-date">Visão global das reservas aprovadas</p>
                </div>
            </header>

            <div class="admin-container">
                <div class="calendar-header-controls">
                    <a href="?m=<?php echo $prev_month; ?>&y=<?php echo $prev_year; ?>" class="calendar-nav-btn"><i class="fa-solid fa-chevron-left"></i> Anterior</a>
                    <h2><?php echo $mes_nome . " " . $year; ?></h2>
                    <a href="?m=<?php echo $next_month; ?>&y=<?php echo $next_year; ?>" class="calendar-nav-btn">Próximo <i class="fa-solid fa-chevron-right"></i></a>
                </div>

                <div class="monthly-grid-wrapper">
                    <div class="monthly-grid">
                        <div class="monthly-day-header">Dom</div>
                        <div class="monthly-day-header">Seg</div>
                        <div class="monthly-day-header">Ter</div>
                        <div class="monthly-day-header">Qua</div>
                        <div class="monthly-day-header">Qui</div>
                        <div class="monthly-day-header">Sex</div>
                        <div class="monthly-day-header">Sáb</div>

                        <?php
                        // Preenchimento dias mês anterior
                        for ($i = 0; $i < $first_day_of_week; $i++) {
                            $prev_day = $days_in_prev_month - ($first_day_of_week - 1 - $i);
                            echo '<div class="monthly-day other-month"><span class="day-number">'.$prev_day.'</span></div>';
                        }

                        // Dias do mês atual
                        $current_dow = $first_day_of_week;
                        for ($day = 1; $day <= $days_in_month; $day++) {
                            $is_today = ($day === $today_day && $month === $today_month && $year === $today_year);
                            $day_class = 'monthly-day' . ($is_today ? ' today' : '');

                            echo '<div class="'.$day_class.'">';
                            echo '<span class="day-number">'.$day.'</span>';
                            
                            if (isset($events_by_day[$day])) {
                                foreach ($events_by_day[$day] as $ev) {
                                    // Para passar o objeto inteiro como string json no onclick, usamos htmlspecialchars
                                    $json_obj = htmlspecialchars(json_encode($ev['obj']), ENT_QUOTES, 'UTF-8');
                                    echo '<div class="event-badge '.$ev['lab_class'].'" onclick="showEventDetails('.$json_obj.')" title="'.htmlspecialchars($ev['display']).'">';
                                    echo htmlspecialchars($ev['display']);
                                    echo '</div>';
                                }
                            }

                            echo '</div>';
                            
                            $current_dow++;
                            if ($current_dow == 7 && $day < $days_in_month) {
                                $current_dow = 0;
                            }
                        }

                        // Preenchimento dias mês seguinte
                        if ($current_dow > 0) {
                            $next_day = 1;
                            for ($i = $current_dow; $i < 7; $i++) {
                                echo '<div class="monthly-day other-month"><span class="day-number">'.$next_day.'</span></div>';
                                $next_day++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
<?php require_once '../includes/footer.php'; ?>

