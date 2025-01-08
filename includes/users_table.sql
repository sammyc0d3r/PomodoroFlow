-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add comments to describe the table and columns
ALTER TABLE users
    COMMENT 'Stores user account information for the Pomodoro application';

-- Example of how to insert a test user (commented out for safety)
/*
INSERT INTO users (username, email, password) VALUES
    ('test_user', 'test@example.com', 'hashed_password_here');
*/
