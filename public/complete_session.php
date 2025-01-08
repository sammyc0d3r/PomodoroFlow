<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to perform this action']);
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$task_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit();
    }

    try {
        // First verify the task exists and belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$task_id, $user_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Task not found or already completed']);
            exit();
        }

        // Update the task
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        
        if ($stmt->execute([$task_id, $user_id])) {
            echo json_encode(['success' => true, 'message' => 'Task completed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update task']);
        }

    } catch (PDOException $e) {
        error_log("Task completion error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
