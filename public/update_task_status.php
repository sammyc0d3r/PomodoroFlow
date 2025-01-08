<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = (int)$_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Verify task belongs to user before updating
        $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed', updated_at = CURRENT_TIMESTAMP 
                              WHERE id = ? AND user_id = ? AND status = 'pending'");
        $result = $stmt->execute([$task_id, $user_id]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update task']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
