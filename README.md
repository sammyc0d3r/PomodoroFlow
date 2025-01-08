# 🍅 PomodoroFlow

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)](https://www.mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> A modern, feature-rich Pomodoro Timer and Task Management System built with PHP and MySQL. Boost your productivity with focused work sessions and organized task tracking.

## 🚀 Key Features

- **📊 Task Management**: Create, organize, and track your tasks
- **⏲️ Pomodoro Timer**: 25/5 minute work/break intervals
- **👥 User Authentication**: Secure multi-user support
- **📱 Responsive Design**: Works on desktop and mobile
- **📈 Progress Tracking**: Monitor your productivity
- **🔒 Secure**: Built with modern security practices

## PomodoroFlow Application

A comprehensive web-based Pomodoro Timer and Task Management application built with PHP, MySQL, and modern web technologies.

## Table of Contents
- [Introduction](#introduction)
- [Technical Stack](#technical-stack)
- [Database Structure](#database-structure)
- [Key Features](#key-features)
- [Code Structure](#code-structure)
- [Security Features](#security-features)
- [Best Practices](#best-practices)
- [Installation and Usage](#installation-and-usage)
- [Future Enhancements](#future-enhancements)
- [Technical Architecture](#technical-architecture)
- [Beginner's Guide](#beginners-guide)

## Introduction

PomodoroFlow combines the Pomodoro Technique with task management to help users improve productivity. The Pomodoro Technique is a time management method that uses a timer to break work into focused intervals (typically 25 minutes) separated by short breaks.

## Technical Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Server**: XAMPP (Apache)

## Database Structure

### Users Table
```sql
- id: Primary key
- username: Unique username for each user
- email: Unique email address
- password: Hashed password for security
- created_at: Timestamp of account creation
```

### Tasks Table
```sql
- id: Primary key
- user_id: Foreign key linking to users table
- title: Task title
- description: Detailed task description
- pomodoros_needed: Number of pomodoros planned for the task
- completed_pomodoros: Number of completed pomodoros
- status: Either 'pending' or 'completed'
- created_at: Task creation timestamp
- completed_at: Task completion timestamp
- updated_at: Last update timestamp
```

## Key Features

### User Authentication System
- Secure login and registration system
- Session management
- Password hashing for security
- Email verification

### Task Management
- Create new tasks
- Set number of pomodoros needed
- Track completed pomodoros
- Mark tasks as complete
- Delete tasks
- View task history

### Pomodoro Timer
- 25-minute work intervals
- Short breaks (5 minutes)
- Long breaks (15 minutes) after 4 pomodoros
- Visual and audio notifications

## Code Structure

### Directory Structure
```
/abdisamad
├── css/               # Stylesheets
├── includes/          # Database and utility files
│   ├── db_connect.php
│   ├── tasks_table.sql
│   ├── users_table.sql
│   └── migrations/    # Database migrations
└── public/           # Public-facing files
    ├── index.php     # Landing page/Login
    ├── dashboard.php # Main application interface
    └── delete_task.php # Task management
```

### Key Files Explained

#### index.php (Login Page)
- Handles user authentication
- Redirects logged-in users to dashboard
- Features a responsive design
- Includes password verification

#### Database Connection
- Establishes secure database connection
- Uses PDO for database operations
- Implements error handling

#### Task Management
- CRUD operations for tasks
- Pomodoro tracking
- Status updates
- Data validation

## Security Features

### User Authentication
- Password hashing using PHP's password_hash()
- Session management
- Protection against SQL injection using prepared statements
- CSRF protection

### Database Security
- Foreign key constraints
- Input validation
- Prepared statements
- Secure password storage

## Best Practices

### Code Organization
- Separation of concerns
- Modular structure
- Clear file naming conventions

### Database Design
- Proper indexing
- Relationship management
- Data integrity constraints

### Security Implementation
- Input sanitization
- Password hashing
- Session management
- SQL injection prevention

### User Experience
- Responsive design
- Intuitive interface
- Error handling
- Success messages

## Installation and Usage

1. **Prerequisites**
   - XAMPP installed
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. **Installation Steps**
   - Clone the repository to your XAMPP htdocs folder
   - Import the database schema from `includes/tasks_table.sql` and `includes/users_table.sql`
   - Configure database connection in `includes/db_connect.php`
   - Start Apache and MySQL services in XAMPP

3. **User Registration**
   - Navigate to the homepage
   - Click "Register"
   - Fill in username, email, and password
   - Submit the form

4. **Login**
   - Enter email and password
   - Click "Login"
   - Redirects to dashboard upon success

5. **Creating Tasks**
   - Click "Add New Task"
   - Enter task title and description
   - Set number of pomodoros needed
   - Submit the form

6. **Using the Pomodoro Timer**
   - Select a task
   - Start the timer
   - Work until the timer ends
   - Take breaks as suggested

7. **Task Management**
   - View all tasks in dashboard
   - Update task status
   - Track pomodoro progress
   - Delete completed tasks

## Future Enhancements

### Feature Enhancements
- Task categories/tags
- Team collaboration
- Statistics and reports
- Custom timer settings

### Technical Enhancements
- API implementation
- Real-time updates
- Mobile application
- Offline support

## Technical Architecture

### System Components

#### 1. Database Layer (`includes/`)
- **db_connect.php**
```php
// Establishes database connection using PDO
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
```
- Uses PDO for database operations with error handling
- Configurable database parameters
- Implements connection pooling
- Sets default fetch mode to associative arrays

#### 2. Authentication System (`public/`)
- **Session Management**
```php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
```
- Implements secure session handling
- Uses PHP's native session management
- Session timeout configuration
- CSRF protection implementation

#### 3. Task Management System
- **delete_task.php**
```php
// Secure task deletion with user verification
$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
```
- Input validation and sanitization
- User ownership verification
- Prepared statements for SQL injection prevention
- JSON response format for API consistency

### Database Schema Details

#### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;
```
- Indexed fields for optimized queries
- Password hashing using bcrypt
- Unique constraints on username and email
- Timestamp tracking for user creation

#### Tasks Table
```sql
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    pomodoros_needed INT DEFAULT 1,
    completed_pomodoros INT DEFAULT 0,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
```
- Foreign key constraints with cascade actions
- Status tracking using ENUM type
- Automatic timestamp updates
- Indexed fields for performance

### API Endpoints

#### 1. Task Management
- **POST /delete_task.php**
  - Parameters: task_id (INT)
  - Authentication: Required
  - Returns: JSON response
  ```json
  {
    "success": boolean,
    "message": string
  }
  ```

- **POST /create_task.php**
  - Parameters: title (STRING), description (TEXT), pomodoros_needed (INT)
  - Authentication: Required
  - Validation: Server-side input validation

#### 2. User Management
- **POST /register.php**
  - Parameters: username, email, password
  - Validation: Email format, password strength
  - Returns: Redirect on success

- **POST /login.php**
  - Parameters: email, password
  - Session: Creates user session
  - Security: Brute force protection

### Security Implementation

#### 1. Password Security
```php
// Password hashing
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Password verification
if (password_verify($password, $user['password'])) {
    // Login success
}
```

#### 2. SQL Injection Prevention
```php
// Using prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

#### 3. XSS Prevention
```php
// Output escaping
htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
```

### Frontend Architecture

#### 1. JavaScript Components
- **timer.js**: Pomodoro timer implementation
- **tasks.js**: Task management AJAX calls
- **validation.js**: Client-side form validation

#### 2. CSS Architecture
- Responsive design using CSS Grid and Flexbox
- Mobile-first approach
- CSS custom properties for theming
- BEM naming convention

### Development Workflow

1. **Local Development**
   ```bash
   # Start XAMPP services
   xampp-control.exe
   
   # Database migrations
   mysql -u root -p pomodoro < includes/migrations/*.sql
   ```

2. **Testing**
   - PHPUnit for unit testing
   - Selenium for E2E testing
   - Jest for JavaScript testing

3. **Deployment**
   - Version control with Git
   - Continuous Integration pipeline
   - Production environment configuration

### Error Handling

```php
try {
    // Database operations
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    return json_encode(['error' => 'Database error occurred']);
}
```

### Logging System

- Error logging to system log
- Activity tracking
- Security event logging
- Performance monitoring

## Beginner's Guide

This section is designed to help beginners understand the core concepts used in this project.

### Understanding the Basics

#### 1. PHP Fundamentals Used in This Project
```php
// Starting a PHP section
<?php
// Including other PHP files
require_once '../includes/db_connect.php';

// Variables in PHP
$username = "john_doe";  // String
$task_id = 42;          // Integer
$is_complete = true;    // Boolean

// Arrays
$user = [
    'id' => 1,
    'name' => 'John',
    'email' => 'john@example.com'
];

// Conditional statements
if (isset($_SESSION['user_id'])) {
    // User is logged in
} else {
    // User is not logged in
}

// Loops
foreach ($tasks as $task) {
    echo $task['title'];
}
```

#### 2. Database Operations Explained
```php
// Creating a new task (INSERT)
$stmt = $pdo->prepare("INSERT INTO tasks (title, user_id) VALUES (?, ?)");
$stmt->execute([$title, $user_id]);

// Reading tasks (SELECT)
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

// Updating a task (UPDATE)
$stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
$stmt->execute(['completed', $task_id]);

// Deleting a task (DELETE)
$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
```

#### 3. Sessions and Authentication
```php
// Starting a session
session_start();

// Logging in a user
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

// Checking if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Logging out
session_destroy();
```

### Common Web Development Concepts

#### 1. HTTP Methods
- **GET**: Used to retrieve data (e.g., viewing tasks)
- **POST**: Used to submit data (e.g., creating a task)
- **DELETE**: Used to remove data (e.g., deleting a task)

Example of handling different methods:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Display data
}
```

#### 2. Forms and Data Handling
```html
<!-- HTML Form -->
<form method="POST" action="create_task.php">
    <input type="text" name="title" required>
    <textarea name="description"></textarea>
    <button type="submit">Create Task</button>
</form>

<!-- PHP handling -->
<?php
$title = $_POST['title'];
$description = $_POST['description'];
```

#### 3. Security Best Practices for Beginners

##### Input Validation
```php
// Sanitizing user input
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);

// Validating email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format";
}
```

##### Output Escaping
```php
// Always escape output to prevent XSS
<div class="task-title">
    <?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?>
</div>
```

### JavaScript Interactions

#### 1. AJAX Requests
```javascript
// Example of deleting a task with AJAX
function deleteTask(taskId) {
    fetch('delete_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove task from DOM
            document.querySelector(`#task-${taskId}`).remove();
        }
    });
}
```

#### 2. DOM Manipulation
```javascript
// Adding a new task to the list
function addTaskToList(task) {
    const taskList = document.querySelector('.task-list');
    const taskElement = document.createElement('div');
    taskElement.id = `task-${task.id}`;
    taskElement.innerHTML = `
        <h3>${task.title}</h3>
        <p>${task.description}</p>
        <button onclick="deleteTask(${task.id})">Delete</button>
    `;
    taskList.appendChild(taskElement);
}
```

### Common Issues and Solutions

#### 1. Database Connection Issues
```php
// Common error: "Could not connect to database"
// Solution: Check your database credentials in db_connect.php
$host = 'localhost';     // Usually correct for XAMPP
$dbname = 'pomodoro';    // Make sure this database exists
$username = 'root';      // Default XAMPP username
$password = '';          // Default XAMPP password is empty
```

#### 2. Session Problems
```php
// Issue: Session variables not persisting
// Solution: Make sure session_start() is at the top of every page
<?php
session_start();  // Must come before any output
```

#### 3. Form Submission Issues
```php
// Issue: Form data not being received
// Solution: Check form method and field names
<form method="POST" action="create_task.php">  // POST, not GET
    <input name="title" required>  // 'name' attribute is important
</form>
```

### Learning Resources

1. **PHP Fundamentals**
   - [PHP Official Documentation](https://www.php.net/docs.php)
   - [W3Schools PHP Tutorial](https://www.w3schools.com/php/)
   - [PHP The Right Way](https://phptherightway.com/)

2. **Database**
   - [MySQL Tutorial](https://www.mysqltutorial.org/)
   - [PDO Tutorial](https://phpdelusions.net/pdo)

3. **Security**
   - [OWASP Top 10](https://owasp.org/www-project-top-ten/)
   - [PHP Security Guide](https://phpsecurity.readthedocs.io/en/latest/)

4. **JavaScript**
   - [MDN JavaScript Guide](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide)
   - [JavaScript.info](https://javascript.info/)

### Development Tools

1. **Essential XAMPP Tools**
   - phpMyAdmin: `http://localhost/phpmyadmin`
   - XAMPP Control Panel
   - MySQL Console

2. **Recommended Code Editors**
   - Visual Studio Code with PHP extensions
   - PHPStorm (paid but powerful)
   - Sublime Text

3. **Browser Developer Tools**
   - Chrome DevTools (F12)
   - Firefox Developer Tools

### Debugging Tips

1. **PHP Errors**
   ```php
   // Enable error reporting in development
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   
   // Use var_dump() for debugging
   var_dump($variable);
   
   // Log errors to file
   error_log("Error message here");
   ```

2. **Database Debugging**
   ```php
   try {
       $stmt = $pdo->prepare($query);
       $stmt->execute($params);
   } catch (PDOException $e) {
       // Log the error details
       error_log("SQL Error: " . $e->getMessage());
       error_log("Query: " . $query);
       error_log("Parameters: " . print_r($params, true));
   }
   ```

3. **JavaScript Debugging**
   ```javascript
   // Use console.log for debugging
   console.log('Variable value:', someVariable);
   
   // Track function execution
   console.trace('Function called');
   
   // Measure time
   console.time('operationName');
   // ... code to measure
   console.timeEnd('operationName');
   ```

## Contributing

Feel free to fork this repository and submit pull requests. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- The Pomodoro Technique® and Pomodoro™ are registered trademarks of Francesco Cirillo.
- Thanks to all contributors who have helped shape this project.
