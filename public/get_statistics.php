<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$range = isset($_GET['range']) ? $_GET['range'] : 'last7days';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Calculate date range based on filter
switch ($range) {
    case 'last30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'thismonth':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        break;
    case 'lastmonth':
        $start_date = date('Y-m-01', strtotime('last month'));
        $end_date = date('Y-m-t', strtotime('last month'));
        break;
    case 'custom':
        // Use the provided custom dates
        if (!$start_date || !$end_date) {
            http_response_code(400);
            echo json_encode(['error' => 'Custom range requires start and end dates']);
            exit();
        }
        break;
    default: // last7days
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
}

try {
    // Fetch total completed tasks for the period
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_tasks 
        FROM tasks 
        WHERE user_id = ? 
        AND status = 'completed'
        AND DATE(completed_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $total_tasks = $stmt->fetch(PDO::FETCH_ASSOC)['total_tasks'];

    // Calculate average session duration
    $stmt = $pdo->prepare("SELECT work_duration FROM settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $avg_duration = $stmt->fetch(PDO::FETCH_ASSOC);
    $avg_duration = $avg_duration ? $avg_duration['work_duration'] : 25;

    // Find most active task in the period
    $stmt = $pdo->prepare("
        SELECT title, COUNT(*) as completion_count
        FROM tasks 
        WHERE user_id = ? 
        AND status = 'completed'
        AND DATE(completed_at) BETWEEN ? AND ?
        GROUP BY title
        ORDER BY completion_count DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $most_active_task = $stmt->fetch(PDO::FETCH_ASSOC);

    // Find best performing day in the period
    $stmt = $pdo->prepare("
        SELECT DATE(completed_at) as completion_date, COUNT(*) as tasks_completed
        FROM tasks 
        WHERE user_id = ? 
        AND status = 'completed'
        AND DATE(completed_at) BETWEEN ? AND ?
        GROUP BY DATE(completed_at)
        ORDER BY tasks_completed DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $best_day = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get daily statistics for the chart
    $stmt = $pdo->prepare("
        SELECT 
            DATE(completed_at) as date,
            COUNT(*) as completed_count
        FROM tasks 
        WHERE user_id = ? 
        AND status = 'completed'
        AND DATE(completed_at) BETWEEN ? AND ?
        GROUP BY DATE(completed_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $daily_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare chart data
    $dates = [];
    $counts = [];
    foreach ($daily_stats as $stat) {
        $dates[] = date('M d', strtotime($stat['date']));
        $counts[] = (int)$stat['completed_count'];
    }

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'total_tasks' => $total_tasks,
            'total_sessions' => $total_tasks,
            'avg_duration' => $avg_duration,
            'most_active_task' => $most_active_task ? [
                'title' => $most_active_task['title'],
                'count' => $most_active_task['completion_count']
            ] : null,
            'best_day' => $best_day ? [
                'date' => date('M d', strtotime($best_day['completion_date'])),
                'count' => $best_day['tasks_completed']
            ] : null,
            'chart_data' => [
                'labels' => $dates,
                'counts' => $counts
            ]
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
