<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$task_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid task ID']);
    exit();
}

try {
    // First verify that the task belongs to the current user
    $stmt = $pdo->prepare("SELECT id, pomodoros_needed, completed_pomodoros FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Task not found or access denied']);
        exit();
    }

    // Increment completed_pomodoros
    $completed_pomodoros = min($task['completed_pomodoros'] + 1, $task['pomodoros_needed']);
    
    // Update the task
    $stmt = $pdo->prepare("UPDATE tasks SET completed_pomodoros = ? WHERE id = ?");
    $stmt->execute([$completed_pomodoros, $task_id]);

    // Calculate progress percentage
    $percentage = ($completed_pomodoros * 100) / $task['pomodoros_needed'];
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'completed_pomodoros' => $completed_pomodoros,
        'total_pomodoros' => $task['pomodoros_needed'],
        'percentage' => $percentage,
        'is_complete' => $completed_pomodoros >= $task['pomodoros_needed']
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error occurred']);
    exit();
}
