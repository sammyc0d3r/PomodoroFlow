<?php
session_start();
require_once '../includes/db_connect.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => 'dashboard.php'
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to perform this action";
    header("Location: login.php");
    exit();
}

// Check if task ID was provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($task_id === false || $task_id === null) {
        $_SESSION['error'] = "Invalid task ID";
        header("Location: dashboard.php");
        exit();
    }

    try {
        // First verify that the task belongs to the current user
        $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['error'] = "Task not found or access denied";
            header("Location: dashboard.php");
            exit();
        }

        // Update the task status
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET status = 'completed',
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? 
            AND user_id = ? 
            AND status = 'pending'
        ");
        
        if ($stmt->execute([$task_id, $user_id])) {
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Task marked as completed successfully!";
            } else {
                $_SESSION['info'] = "Task was already completed";
            }
        } else {
            $_SESSION['error'] = "Failed to update task status";
        }

    } catch (PDOException $e) {
        // Log the error securely (in a production environment)
        error_log("Task update error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating the task";
    }

} else {
    $_SESSION['error'] = "Invalid request method";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>
