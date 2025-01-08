<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the header
require_once '../includes/header.php';

$username = htmlspecialchars($_SESSION['username']);
$user_id = $_SESSION['user_id'];

$task_message = '';
$task_error = '';

// Handle task form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pomodoros_needed = trim($_POST['pomodoros_needed'] ?? '');
    
    if (empty($title)) {
        $task_error = "Task title is required";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, pomodoros_needed) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $title, $description, $pomodoros_needed])) {
                $task_message = "Task added successfully!";
                $_POST = array();
            } else {
                $task_error = "Failed to add task";
            }
        } catch (PDOException $e) {
            $task_error = "Database error occurred";
        }
    }
}

// Fetch all tasks for the user
try {
    $stmt = $pdo->prepare("
        SELECT *, 
            CASE 
                WHEN status = 'completed' THEN 'Completed'
                ELSE 'Pending'
            END as status_text,
            DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as formatted_date,
            CONCAT(completed_pomodoros, '/', pomodoros_needed) as pomodoro_progress,
            (completed_pomodoros * 100 / pomodoros_needed) as pomodoro_percentage
        FROM tasks 
        WHERE user_id = ? 
        ORDER BY 
            CASE WHEN status = 'pending' THEN 0 ELSE 1 END,
            created_at DESC
    ");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $task_error = "Failed to fetch tasks";
    $tasks = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pomodoro App</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #ff9800;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, #45a049 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(76, 175, 80, 0.2);
        }

        .welcome-banner h2 {
            margin: 0;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .session-info {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .task-section {
            background: var(--light-bg);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .task-section h3 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .task-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input[type="number"] {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input[type="number"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .submit-task-btn {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-task-btn:hover {
            background: #45a049;
            transform: translateY(-1px);
        }

        .task-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .task-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .task-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .recent-tasks {
            margin-top: 2rem;
        }

        .task-item {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .task-item:hover {
            transform: translateY(-2px);
        }

        .task-item.completed {
            border-left-color: var(--success-color);
        }

        .task-content h4 {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .task-content p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .task-meta {
            color: #888;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .task-pomodoros {
            display: inline-flex;
            align-items: center;
            background: rgba(76, 175, 80, 0.1);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--primary-color);
            white-space: nowrap;
        }

        .pomodoro-progress {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: 1rem;
        }

        .progress-bar {
            flex-grow: 1;
            height: 6px;
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 3px;
            overflow: hidden;
            width: 150px;
        }

        .progress {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .task-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .timer-btn, .delete-btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .timer-btn {
            background: var(--primary-color);
            color: white;
        }

        .timer-btn:hover {
            background: #45a049;
            transform: translateY(-1px);
        }

        .delete-btn {
            background: var(--danger-color);
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .logout-container {
            text-align: center;
            margin-top: 2rem;
        }

        .logout-btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: var(--danger-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-container {
                padding: 1rem;
            }

            .welcome-banner {
                padding: 1.5rem;
            }

            .task-section {
                padding: 1.5rem;
            }

            .task-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <?php
            // Display session messages
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['info'])) {
                echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info']) . '</div>';
                unset($_SESSION['info']);
            }
            ?>

            <div class="welcome-banner">
                <h2>Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
                <div class="session-info">
                    <?php 
                    $last_activity = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : null;
                    if ($last_activity) {
                        echo 'Last activity: ' . date('H:i:s', $last_activity);
                    }
                    ?>
                </div>
            </div>

            <div class="task-section">
                <h3>Add New Task</h3>
                
                <?php if ($task_message): ?>
                    <div class="task-message task-success"><?php echo htmlspecialchars($task_message); ?></div>
                <?php endif; ?>
                
                <?php if ($task_error): ?>
                    <div class="task-message task-error"><?php echo htmlspecialchars($task_error); ?></div>
                <?php endif; ?>

                <form id="addTaskForm" class="task-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="form-group">
                        <input type="text" id="taskTitle" name="title" placeholder="Task Title" required>
                    </div>
                    <div class="form-group">
                        <textarea id="taskDescription" name="description" placeholder="Task Description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="pomodoros">Number of Pomodoros needed:</label>
                        <input type="number" id="pomodoros" name="pomodoros_needed" min="1" value="1" required>
                    </div>
                    <button type="submit" name="add_task" class="submit-task-btn">Add Task</button>
                </form>

                <div class="recent-tasks">
                    <h3>Pending Tasks</h3>
                    <?php
                    $pending_tasks = array_filter($tasks, function($task) {
                        return $task['status'] === 'pending';
                    });
                    
                    if (empty($pending_tasks)): ?>
                        <p class="no-tasks">No pending tasks</p>
                    <?php else: ?>
                        <div class="task-list">
                            <?php foreach ($pending_tasks as $task): ?>
                                <div class="task-item">
                                    <div class="task-content">
                                        <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                                        <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                        <div class="task-meta">
                                            <span class="task-date"><?php echo $task['formatted_date']; ?></span>
                                            <div class="pomodoro-progress">
                                                <span class="task-pomodoros">
                                                    <?php echo $task['pomodoro_progress']; ?> Pomodoros
                                                </span>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: <?php echo min(100, $task['pomodoro_percentage']); ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-actions">
                                        <a href="timer.php?task_id=<?php echo $task['id']; ?>" class="timer-btn">Start Timer</a>
                                        <form method="POST" action="delete_task.php" onsubmit="return confirmDelete(event, <?php echo $task['id']; ?>)" style="display: inline;">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h3>Completed Tasks</h3>
                    <?php
                    $completed_tasks = array_filter($tasks, function($task) {
                        return $task['status'] === 'completed';
                    });
                    
                    if (!empty($completed_tasks)): ?>
                        <?php foreach ($completed_tasks as $task): ?>
                            <div class="task-item completed">
                                <div class="task-content">
                                    <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                                    <?php if (!empty($task['description'])): ?>
                                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="task-meta">
                                        Created: <?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="task-actions">
                                    <form method="POST" action="delete_task.php" onsubmit="return confirmDelete(event, <?php echo $task['id']; ?>)">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No completed tasks.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="logout-container">
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <script>
    function deleteTask(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) return;

        fetch('delete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'task_id=' + taskId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show updated task list
                window.location.reload();
            } else {
                alert('Failed to delete task: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete task. Please try again.');
        });
    }

    function confirmDelete(event, taskId) {
        event.preventDefault();
        
        if (!confirm('Are you sure you want to delete this task?')) {
            return false;
        }

        fetch('delete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'task_id=' + taskId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to delete task: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete task. Please try again.');
        });

        return false;
    }
    </script>
</body>
</html>
