<?php
include 'config.php';
include 'nav.php';

/* MEMBER LOGIN CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===============================
   ACTIVE MEMBERSHIP ACCESS CHECK
   =============================== */
$membership_check = mysqli_query(
    $conn,
    "SELECT * FROM member_memberships 
     WHERE user_id = $user_id 
     AND status = 'active'
     LIMIT 1"
);

if (mysqli_num_rows($membership_check) == 0) {
    echo "
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Membership Required',
            text: 'You need an active membership to access fitness progress tracking.',
            confirmButtonText: 'View Plans',
            showCancelButton: true,
            cancelButtonText: 'Go to Dashboard'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'membership.php';
            } else {
                window.location.href = 'member_dashboard.php';
            }
        });
    </script>
    ";
    exit();
}

// Handle adding a new fitness log
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_log'])) {
    $log_date = date('Y-m-d', strtotime($_POST['log_date']));
    $weight = $_POST['weight'] ?: NULL;
    $body_fat = $_POST['body_fat'] ?: NULL;
    $chest = $_POST['chest'] ?: NULL;
    $waist = $_POST['waist'] ?: NULL;
    $hips = $_POST['hips'] ?: NULL;
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $sql = "INSERT INTO FitnessLogs (user_id, log_date, weight, body_fat, chest, waist, hips, notes) 
            VALUES ($user_id, '$log_date', ".($weight===NULL?'NULL':$weight).", ".($body_fat===NULL?'NULL':$body_fat).", 
            ".($chest===NULL?'NULL':$chest).", ".($waist===NULL?'NULL':$waist).", ".($hips===NULL?'NULL':$hips).", '$notes')";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Fitness log added successfully!";
    } else {
        $error = "Error adding log: " . mysqli_error($conn);
    }
}

// Handle chart filter
$metric = isset($_GET['metric']) ? $_GET['metric'] : 'weight';

// Metric options with icons
$metrics_options = [
    'weight' => ['label' => 'Weight (kg)', 'icon' => 'fa-weight-scale', 'color' => '#667eea'],
    'body_fat' => ['label' => 'Body Fat (%)', 'icon' => 'fa-percentage', 'color' => '#f5576c'],
    'chest' => ['label' => 'Chest (cm)', 'icon' => 'fa-expand', 'color' => '#4cd964'],
    'waist' => ['label' => 'Waist (cm)', 'icon' => 'fa-arrow-down-wide-short', 'color' => '#ff9500'],
    'hips' => ['label' => 'Hips (cm)', 'icon' => 'fa-person-dress', 'color' => '#5856d6']
];

// Get all logs for chart and table
$logs_sql = "SELECT * FROM FitnessLogs WHERE user_id=$user_id ORDER BY log_date ASC";
$logs_result = mysqli_query($conn, $logs_sql);

// Calculate progress statistics
$first_log = null;
$last_log = null;
$chart_dates = [];
$chart_values = [];
$all_logs = [];
$total_entries = 0;

while ($row = mysqli_fetch_assoc($logs_result)) {
    if (!$first_log) $first_log = $row;
    $last_log = $row;
    $chart_dates[] = date('M d', strtotime($row['log_date']));
    $chart_values[] = $row[$metric] ?: 0;
    $all_logs[] = $row;
    $total_entries++;
}

// Calculate progress
$progress = 0;
if ($first_log && $last_log && $first_log[$metric] && $last_log[$metric]) {
    if (in_array($metric, ['waist', 'body_fat', 'weight'])) {
        // For metrics where lower is better
        $progress = (($first_log[$metric] - $last_log[$metric]) / $first_log[$metric]) * 100;
    } else {
        // For metrics where higher is better (chest for muscle gain)
        $progress = (($last_log[$metric] - $first_log[$metric]) / $first_log[$metric]) * 100;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fitness Progress - FitPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4cd964 0%, #5ac8fa 100%);
            --warning-gradient: linear-gradient(135deg, #ff9500 0%, #ff5e3a 100%);
            --danger-gradient: linear-gradient(135deg, #ff2d55 0%, #ff5e3a 100%);
            --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
            --hover-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .progress-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 25px;
        }
        
        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .metric-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .metric-card.active {
            border: 2px solid #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }
        
        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .progress-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .progress-up {
            background: rgba(76, 217, 100, 0.15);
            color: #4cd964;
        }
        
        .progress-down {
            background: rgba(255, 69, 58, 0.15);
            color: #ff453a;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            margin: 10px 0;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            margin: 20px 0;
        }
        
        .log-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .log-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
        }
        
        .log-change {
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .table {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table tbody tr {
            transition: all 0.3s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
            transform: translateX(5px);
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        @media (max-width: 768px) {
            .chart-container {
                height: 300px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 fw-bold text-primary">Fitness Progress</h1>
                        <p class="text-muted mb-0">Track your journey towards a healthier you</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-3">
                            <i class="fas fa-calendar-check me-1"></i>
                            <?php echo $total_entries; ?> Logs
                        </span>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLogModal">
                            <i class="fas fa-plus me-2"></i>Add New Log
                        </button>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">Success!</h5>
                            <p class="mb-0"><?php echo $success; ?></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">Error!</h5>
                            <p class="mb-0"><?php echo $error; ?></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Progress Overview Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 mb-4">
                        <div class="card progress-card">
                            <div class="card-body text-center p-4">
                                <div class="metric-icon" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%); color: #667eea;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5 class="card-title mb-2">Overall Progress</h5>
                                <div class="stat-number">
                                    <?php echo number_format(abs($progress), 1); ?>%
                                </div>
                                <span class="progress-badge <?php echo $progress >= 0 ? 'progress-up' : 'progress-down'; ?>">
                                    <i class="fas fa-arrow-<?php echo $progress >= 0 ? 'up' : 'down'; ?> me-1"></i>
                                    <?php echo $progress >= 0 ? 'Improving' : 'Needs Work'; ?>
                                </span>
                                <p class="text-muted mt-3 mb-0 small">
                                    <?php 
                                    if ($first_log && $last_log) {
                                        echo "From " . date('M d', strtotime($first_log['log_date'])) . " to " . date('M d', strtotime($last_log['log_date']));
                                    } else {
                                        echo "Add more logs to see progress";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-9 col-lg-6">
                        <div class="card progress-card">
                            <div class="card-header bg-white border-0">
                                <h5 class="card-title mb-0">Quick Metrics Overview</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <?php foreach($metrics_options as $key => $metric_data): ?>
                                        <?php
                                        $current_value = $last_log ? ($last_log[$key] ?: 'N/A') : 'N/A';
                                        $previous_value = count($all_logs) > 1 ? ($all_logs[count($all_logs)-2][$key] ?: 'N/A') : 'N/A';
                                        $change = '';
                                        $change_class = '';
                                        
                                        if ($current_value !== 'N/A' && $previous_value !== 'N/A') {
                                            $change_value = $current_value - $previous_value;
                                            if ($change_value > 0) {
                                                $change = '<i class="fas fa-arrow-up text-success me-1"></i>+' . number_format($change_value, 1);
                                                $change_class = 'text-success';
                                            } elseif ($change_value < 0) {
                                                $change = '<i class="fas fa-arrow-down text-danger me-1"></i>' . number_format($change_value, 1);
                                                $change_class = 'text-danger';
                                            } else {
                                                $change = '<i class="fas fa-equals text-secondary me-1"></i>0';
                                                $change_class = 'text-secondary';
                                            }
                                        }
                                        ?>
                                        <div class="col-sm-4 col-6 mb-3">
                                            <a href="?metric=<?php echo $key; ?>" class="text-decoration-none">
                                                <div class="metric-card p-3 text-center <?php echo $metric == $key ? 'active' : ''; ?>">
                                                    <div class="metric-icon mx-auto" style="background: linear-gradient(135deg, <?php echo hex2rgba($metric_data['color'], 0.15); ?>); color: <?php echo $metric_data['color']; ?>;">
                                                        <i class="fas <?php echo $metric_data['icon']; ?>"></i>
                                                    </div>
                                                    <h6 class="mb-2"><?php echo $metric_data['label']; ?></h6>
                                                    <div class="log-value"><?php echo $current_value; ?></div>
                                                    <?php if($change): ?>
                                                        <div class="log-change <?php echo $change_class; ?>"><?php echo $change; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Progress Chart -->
                <div class="card progress-card mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas <?php echo $metrics_options[$metric]['icon']; ?> me-2" style="color: <?php echo $metrics_options[$metric]['color']; ?>;"></i>
                                <?php echo $metrics_options[$metric]['label']; ?> Progress
                            </h5>
                        </div>
                        <form method="GET" class="d-flex align-items-center">
                            <select name="metric" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <?php foreach($metrics_options as $key => $metric_data): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $metric==$key?'selected':''; ?>>
                                        <?php echo $metric_data['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container">
                            <canvas id="fitnessChart"></canvas>
                        </div>
                        
                        <!-- Chart Statistics -->
                        <?php if(count($chart_values) > 0): ?>
                        <div class="row mt-4 text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(76, 217, 100, 0.1) 0%, rgba(90, 200, 250, 0.1) 100%);">
                                    <div class="text-primary fw-bold"><?php echo min(array_filter($chart_values)); ?></div>
                                    <small class="text-muted">Minimum Value</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(255, 149, 0, 0.1) 0%, rgba(255, 94, 58, 0.1) 100%);">
                                    <div class="text-warning fw-bold"><?php echo max(array_filter($chart_values)); ?></div>
                                    <small class="text-muted">Maximum Value</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(255, 45, 85, 0.1) 0%, rgba(255, 94, 58, 0.1) 100%);">
                                    <div class="text-danger fw-bold">
                                        <?php 
                                        $non_zero = array_filter($chart_values);
                                        echo count($non_zero) > 0 ? number_format(array_sum($non_zero)/count($non_zero), 1) : '0';
                                        ?>
                                    </div>
                                    <small class="text-muted">Average Value</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);">
                                    <div class="text-info fw-bold"><?php echo count(array_filter($chart_values)); ?></div>
                                    <small class="text-muted">Data Points</small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fitness Logs Table -->
                <div class="card progress-card">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Fitness Logs</h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLogModal">
                            <i class="fas fa-plus me-1"></i>Add New
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <?php if(count($all_logs) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Weight</th>
                                            <th>Body Fat</th>
                                            <th>Chest</th>
                                            <th>Waist</th>
                                            <th>Hips</th>
                                            <th>Notes</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach(array_reverse($all_logs) as $index => $log): ?>
                                            <?php
                                            $prev_log = $index > 0 ? $all_logs[count($all_logs) - $index] : null;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo date('M d', strtotime($log['log_date'])); ?></div>
                                                    <div class="log-date"><?php echo date('Y', strtotime($log['log_date'])); ?></div>
                                                </td>
                                                <td>
                                                    <div class="log-value"><?php echo $log['weight'] ? $log['weight'] . ' kg' : '--'; ?></div>
                                                    <?php if($prev_log && $log['weight'] && $prev_log['weight']): ?>
                                                        <?php $change = $log['weight'] - $prev_log['weight']; ?>
                                                        <div class="log-change <?php echo $change < 0 ? 'text-success' : ($change > 0 ? 'text-danger' : 'text-secondary'); ?>">
                                                            <?php echo $change < 0 ? '↓' : ($change > 0 ? '↑' : '→'); ?>
                                                            <?php echo number_format(abs($change), 1); ?> kg
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="log-value"><?php echo $log['body_fat'] ? $log['body_fat'] . ' %' : '--'; ?></div>
                                                    <?php if($prev_log && $log['body_fat'] && $prev_log['body_fat']): ?>
                                                        <?php $change = $log['body_fat'] - $prev_log['body_fat']; ?>
                                                        <div class="log-change <?php echo $change < 0 ? 'text-success' : ($change > 0 ? 'text-danger' : 'text-secondary'); ?>">
                                                            <?php echo $change < 0 ? '↓' : ($change > 0 ? '↑' : '→'); ?>
                                                            <?php echo number_format(abs($change), 1); ?>%
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="log-value"><?php echo $log['chest'] ? $log['chest'] . ' cm' : '--'; ?></div>
                                                    <?php if($prev_log && $log['chest'] && $prev_log['chest']): ?>
                                                        <?php $change = $log['chest'] - $prev_log['chest']; ?>
                                                        <div class="log-change <?php echo $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-secondary'); ?>">
                                                            <?php echo $change > 0 ? '↑' : ($change < 0 ? '↓' : '→'); ?>
                                                            <?php echo number_format(abs($change), 1); ?> cm
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="log-value"><?php echo $log['waist'] ? $log['waist'] . ' cm' : '--'; ?></div>
                                                    <?php if($prev_log && $log['waist'] && $prev_log['waist']): ?>
                                                        <?php $change = $log['waist'] - $prev_log['waist']; ?>
                                                        <div class="log-change <?php echo $change < 0 ? 'text-success' : ($change > 0 ? 'text-danger' : 'text-secondary'); ?>">
                                                            <?php echo $change < 0 ? '↓' : ($change > 0 ? '↑' : '→'); ?>
                                                            <?php echo number_format(abs($change), 1); ?> cm
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="log-value"><?php echo $log['hips'] ? $log['hips'] . ' cm' : '--'; ?></div>
                                                    <?php if($prev_log && $log['hips'] && $prev_log['hips']): ?>
                                                        <?php $change = $log['hips'] - $prev_log['hips']; ?>
                                                        <div class="log-change <?php echo $change < 0 ? 'text-success' : ($change > 0 ? 'text-danger' : 'text-secondary'); ?>">
                                                            <?php echo $change < 0 ? '↓' : ($change > 0 ? '↑' : '→'); ?>
                                                            <?php echo number_format(abs($change), 1); ?> cm
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($log['notes']): ?>
                                                        <span class="badge bg-light text-dark" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($log['notes']); ?>">
                                                            <i class="fas fa-sticky-note me-1"></i>View Note
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">--</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-bar"></i>
                                <h4 class="mb-3">No Fitness Logs Yet</h4>
                                <p class="text-muted mb-4">Start tracking your fitness journey by adding your first log</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLogModal">
                                    <i class="fas fa-plus me-2"></i>Add First Log
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Fitness Log Modal -->
    <div class="modal fade" id="addLogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        Add New Fitness Log
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date *</label>
                                <input type="date" name="log_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                <div class="form-text">Select the date for this log entry</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Weight (kg)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="weight" class="form-control" placeholder="e.g., 75.5">
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Body Fat Percentage</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="body_fat" class="form-control" placeholder="e.g., 18.5">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Chest Measurement</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="chest" class="form-control" placeholder="e.g., 100.5">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Waist Measurement</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="waist" class="form-control" placeholder="e.g., 85.5">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Hips Measurement</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="hips" class="form-control" placeholder="e.g., 95.5">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes & Observations</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="How are you feeling? Any observations about your workout or diet?"></textarea>
                            <div class="form-text">Optional - Add any notes about your progress or how you're feeling</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="fas fa-lightbulb fa-2x me-3"></i>
                                <div>
                                    <strong>Tip:</strong> Regular logging (weekly) gives you better insights into your progress trends.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_log" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Log Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Chart Configuration
        const ctx = document.getElementById('fitnessChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
        gradient.addColorStop(1, 'rgba(118, 75, 162, 0.2)');
        
        const fitnessChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_dates); ?>,
                datasets: [{
                    label: '<?php echo $metrics_options[$metric]['label']; ?>',
                    data: <?php echo json_encode($chart_values); ?>,
                    borderColor: '<?php echo $metrics_options[$metric]['color']; ?>',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '<?php echo $metrics_options[$metric]['color']; ?>',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: "'Segoe UI', sans-serif"
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        },
                        padding: 12,
                        cornerRadius: 6
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animations: {
                    tension: {
                        duration: 1000,
                        easing: 'linear'
                    }
                }
            }
        });

        // Function to convert hex to rgba (for gradient backgrounds)
        function hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    </script>
</body>
</html>

<?php
// Helper function to convert hex color to rgba
function hex2rgba($color, $opacity = false) {
    $default = 'rgb(0,0,0)';
    
    if (empty($color)) {
        return $default;
    }
    
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }
    
    if (strlen($color) == 6) {
        $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
    } elseif (strlen($color) == 3) {
        $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return $default;
    }
    
    $rgb = array_map('hexdec', $hex);
    
    if ($opacity) {
        if (abs($opacity) > 1) {
            $opacity = 1.0;
        }
        $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode(",", $rgb) . ')';
    }
    
    return $output;
}
?>