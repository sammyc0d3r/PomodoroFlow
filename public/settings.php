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

$user_id = $_SESSION['user_id'];
$message = '';
$settings = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_duration = filter_input(INPUT_POST, 'work_duration', FILTER_VALIDATE_INT);
    $break_duration = filter_input(INPUT_POST, 'break_duration', FILTER_VALIDATE_INT);

    // Validate input
    if ($work_duration < 1 || $work_duration > 60 || $break_duration < 1 || $break_duration > 30) {
        $message = 'Invalid duration values. Work duration must be between 1-60 minutes and break duration between 1-30 minutes.';
    } else {
        try {
            // Check if settings exist for user
            $stmt = $pdo->prepare("SELECT id FROM settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->rowCount() > 0) {
                // Update existing settings
                $stmt = $pdo->prepare("UPDATE settings SET work_duration = ?, break_duration = ? WHERE user_id = ?");
                $stmt->execute([$work_duration, $break_duration, $user_id]);
            } else {
                // Insert new settings
                $stmt = $pdo->prepare("INSERT INTO settings (user_id, work_duration, break_duration) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $work_duration, $break_duration]);
            }
            
            $message = 'Settings updated successfully!';
            
        } catch (PDOException $e) {
            $message = 'Error updating settings. Please try again.';
            error_log($e->getMessage());
        }
    }
}

// Fetch current settings
try {
    $stmt = $pdo->prepare("SELECT work_duration, break_duration FROM settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching settings.';
    error_log($e->getMessage());
}

// Use default values if no settings found
if (!$settings) {
    $settings = [
        'work_duration' => 25,
        'break_duration' => 5
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer Settings - PomodoroFlow</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --error-color: #ff6b6b;
            --success-color: #4CAF50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .settings-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: #444;
        }

        input[type="number"] {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: var(--secondary-color);
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        .error {
            background-color: #ffebee;
            color: var(--error-color);
        }

        .nav-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .nav-links a {
            color: var(--primary-color);
            text-decoration: none;
            margin: 0 10px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Timer Settings</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="settings-form" method="POST">
            <div class="form-group">
                <label for="work_duration">Work Duration (minutes):</label>
                <input type="number" id="work_duration" name="work_duration" 
                       value="<?php echo htmlspecialchars($settings['work_duration']); ?>" 
                       min="1" max="60" required>
            </div>

            <div class="form-group">
                <label for="break_duration">Break Duration (minutes):</label>
                <input type="number" id="break_duration" name="break_duration" 
                       value="<?php echo htmlspecialchars($settings['break_duration']); ?>" 
                       min="1" max="30" required>
            </div>

            <button type="submit">Save Settings</button>
        </form>

        <div class="nav-links">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
