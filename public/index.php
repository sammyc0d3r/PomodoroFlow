<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PomodoroFlow - Focus Better, Achieve More</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #ff9800;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            overflow-x: hidden;
        }

        .nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-btn {
            background: var(--primary-color);
            color: white;
        }

        .register-btn {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 2rem 2rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #666;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .timer-demo {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 3rem auto;
            max-width: 300px;
        }

        .timer-display {
            font-size: 4rem;
            font-weight: bold;
            color: var(--primary-color);
            font-family: monospace;
            margin: 1rem 0;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            text-align: left;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .feature i {
            font-size: 1.5rem;
            color: var(--primary-color);
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 50%;
        }

        .feature-text h3 {
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .feature-text p {
            font-size: 0.95rem;
            color: #666;
            margin: 0;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero {
                padding-top: 5rem;
            }

            .features {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .get-started {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 500;
            text-decoration: none;
            margin-top: 2rem;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }

        .get-started:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="index.php" class="logo">
            <i class="fas fa-clock"></i>
            PomodoroFlow
        </a>
        <div class="nav-buttons">
            <a href="login.php" class="btn login-btn">Login</a>
            <a href="register.php" class="btn register-btn">Register</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Master Your Time,<br>Boost Your Productivity</h1>
            <p>Stay focused and accomplish more with our intuitive Pomodoro timer. Break your work into focused sessions, take smart breaks, and track your progress.</p>
            
            <div class="timer-demo">
                <div class="timer-display">25:00</div>
                <p>Simple, focused, effective.</p>
            </div>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-brain"></i>
                    <div class="feature-text">
                        <h3>Stay Focused</h3>
                        <p>Work in 25-minute focused sessions to maintain peak productivity.</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <div class="feature-text">
                        <h3>Track Progress</h3>
                        <p>Monitor your productivity with detailed statistics and insights.</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-coffee"></i>
                    <div class="feature-text">
                        <h3>Smart Breaks</h3>
                        <p>Take regular breaks to stay refreshed and maintain energy levels.</p>
                    </div>
                </div>
            </div>

            <a href="register.php" class="get-started">
                Get Started Free
                <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </a>
        </div>
    </section>
</body>
</html>
