<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();

// Include the header
require_once '../includes/header.php';

if (!isset($_GET['task_id'])) {
    header('Location: dashboard.php');
    exit();
}

$task_id = filter_input(INPUT_GET, 'task_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

// Fetch user's timer settings
try {
    $stmt = $pdo->prepare("SELECT work_duration, break_duration FROM settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Use default values if no settings found
    if (!$settings) {
        $settings = [
            'work_duration' => 25,
            'break_duration' => 5
        ];
    }
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    $settings = [
        'work_duration' => 25,
        'break_duration' => 5
    ];
}

// Verify task belongs to user and get task details
try {
    $stmt = $pdo->prepare("
        SELECT title, pomodoros_needed, completed_pomodoros 
        FROM tasks 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pomodoro Timer - <?php echo htmlspecialchars($task['title']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .timer-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .task-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
        }
        .timer-display {
            font-size: 4em;
            font-weight: bold;
            margin: 20px 0;
            color: #4CAF50;
        }
        .timer-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        .start-btn {
            background: #4CAF50;
            color: white;
        }
        .pause-btn {
            background: #ff9800;
            color: white;
        }
        .reset-btn {
            background: #f44336;
            color: white;
        }
        .back-btn {
            background: #2196F3;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .skip-btn {
            background: #ff9800;
            color: white;
            padding: 10px 20px;
            margin-top: 20px;
            display: block;
            width: fit-content;
            margin: 20px auto;
        }
        .skip-btn:hover {
            background: #f57c00;
        }
        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            text-align: center;
        }
        .confirmation-dialog p {
            margin-bottom: 20px;
        }
        .dialog-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        button:hover {
            opacity: 0.9;
        }
        .phase-indicator {
            margin-top: 10px;
            font-size: 1.2em;
            color: #666;
        }
        .break-message {
            background: #3f51b5;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            display: none;
        }
        
        .resume-btn {
            background: #4CAF50;
            color: white;
            padding: 15px 30px;
            font-size: 1.2em;
            margin: 20px 0;
            display: none;
            width: 100%;
        }
        
        .resume-btn:hover {
            background: #45a049;
        }
        
        .pomodoro-progress {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 8px;
        }

        .progress-text {
            margin-bottom: 10px;
            color: #4CAF50;
            font-weight: 500;
        }

        .progress-count {
            font-weight: bold;
            font-size: 1.2em;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: #4CAF50;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="timer-container">
        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
        <div class="pomodoro-progress">
            <div class="progress-text">
                Pomodoro Progress: 
                <span class="progress-count">
                    <?php echo $task['completed_pomodoros']; ?>/<?php echo $task['pomodoros_needed']; ?>
                </span>
            </div>
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo ($task['completed_pomodoros'] * 100 / $task['pomodoros_needed']); ?>%"></div>
            </div>
        </div>
        <div class="timer-display" id="timer">25:00</div>
        <div class="phase-indicator" id="phase">Work Time</div>
        
        <div class="break-message" id="breakMessage">
            Time for a break! Take a moment to relax and recharge.
        </div>

        <div class="timer-controls">
            <button class="start-btn" id="startBtn">Start</button>
            <button class="pause-btn" id="pauseBtn" disabled>Pause</button>
            <button class="reset-btn" id="resetBtn">Reset</button>
        </div>
        
        <button class="resume-btn" id="resumeBtn" style="display: none;">Resume Work</button>
        <button class="skip-btn" id="skipBtn">Skip Timer</button>
        <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="confirmation-dialog" id="skipDialog">
        <p>Are you sure you want to skip the current timer?</p>
        <div class="dialog-buttons">
            <button onclick="confirmSkip()">Yes, Skip</button>
            <button onclick="closeSkipDialog()">Cancel</button>
        </div>
    </div>

    <script>
        const WORK_TIME = <?php echo $settings['work_duration']; ?> * 60;
        const BREAK_TIME = <?php echo $settings['break_duration']; ?> * 60;
        const TASK_ID = <?php echo json_encode($task_id); ?>;
        const POMODOROS_NEEDED = <?php echo json_encode($task['pomodoros_needed']); ?>;
        const COMPLETED_POMODOROS = <?php echo json_encode($task['completed_pomodoros']); ?>;
        
        let timeLeft = WORK_TIME;
        let timerId = null;
        let isWorkPhase = true;
        let isBreakStarted = false;
        let isPaused = true;

        const timerDisplay = document.getElementById('timer');
        const phaseIndicator = document.getElementById('phase');
        const startBtn = document.getElementById('startBtn');
        const pauseBtn = document.getElementById('pauseBtn');
        const resetBtn = document.getElementById('resetBtn');
        const resumeBtn = document.getElementById('resumeBtn');
        const skipBtn = document.getElementById('skipBtn');
        const breakMessage = document.getElementById('breakMessage');
        const progressCount = document.querySelector('.progress-count');
        const progressBar = document.querySelector('.progress');

        function updateProgress(completedPomodoros) {
            progressCount.textContent = `${completedPomodoros}/${POMODOROS_NEEDED}`;
            const percentage = (completedPomodoros * 100 / POMODOROS_NEEDED);
            progressBar.style.width = `${percentage}%`;
        }

        function completePomodoro() {
            fetch('update_pomodoros.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `task_id=${TASK_ID}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                
                updateProgress(data.completed_pomodoros);
                
                if (data.is_complete) {
                    // If all pomodoros are completed, mark task as complete and redirect to dashboard
                    fetch('complete_session.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `task_id=${TASK_ID}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            console.error('Error completing task:', data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                } else {
                    startBreak();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function startBreak() {
            isWorkPhase = false;
            isBreakStarted = true;
            timeLeft = BREAK_TIME;
            updateTimer();
            phaseIndicator.textContent = 'Break Time';
            breakMessage.style.display = 'block';
            isPaused = true;
            startBtn.disabled = false;
            pauseBtn.disabled = true;
        }

        function startNextPomodoro() {
            isWorkPhase = true;
            isBreakStarted = false;
            timeLeft = WORK_TIME;
            updateTimer();
            phaseIndicator.textContent = 'Work Time';
            breakMessage.style.display = 'none';
            resumeBtn.style.display = 'none';
            isPaused = true;
            startBtn.disabled = false;
            pauseBtn.disabled = true;
        }

        function showSkipDialog() {
            document.getElementById('skipDialog').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeSkipDialog() {
            document.getElementById('skipDialog').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function confirmSkip() {
            closeSkipDialog();
            clearInterval(timerId);
            
            if (isWorkPhase) {
                completePomodoro();
            } else {
                startNextPomodoro();
            }
        }

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function startTimer() {
            if (!isPaused) return;
            
            isPaused = false;
            startBtn.disabled = true;
            pauseBtn.disabled = false;
            
            timerId = setInterval(() => {
                timeLeft--;
                updateTimer();
                
                if (timeLeft <= 0) {
                    clearInterval(timerId);
                    
                    if (isWorkPhase) {
                        completePomodoro();
                    } else if (isBreakStarted) {
                        resumeBtn.style.display = 'block';
                        startBtn.disabled = true;
                        pauseBtn.disabled = true;
                        resetBtn.disabled = true;
                        phaseIndicator.textContent = 'Break Complete';
                    }
                }
            }, 1000);
        }

        function pauseTimer() {
            if (isPaused) return;
            
            isPaused = true;
            clearInterval(timerId);
            startBtn.disabled = false;
            pauseBtn.disabled = true;
        }

        function resetTimer() {
            clearInterval(timerId);
            isPaused = true;
            timeLeft = isWorkPhase ? WORK_TIME : BREAK_TIME;
            updateTimer();
            startBtn.disabled = false;
            pauseBtn.disabled = true;
        }

        // Event Listeners
        startBtn.addEventListener('click', startTimer);
        pauseBtn.addEventListener('click', pauseTimer);
        resetBtn.addEventListener('click', resetTimer);
        skipBtn.addEventListener('click', showSkipDialog);
        resumeBtn.addEventListener('click', startNextPomodoro);

        // Initialize timer display
        updateTimer();
        updateProgress(COMPLETED_POMODOROS);
    </script>
</body>
</html>
