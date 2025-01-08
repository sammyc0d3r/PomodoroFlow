-- Create tasks table with foreign key reference to users table
CREATE TABLE IF NOT EXISTS tasks (
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
        ON UPDATE CASCADE,
    
    -- Add indexes for better performance
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add table comment
ALTER TABLE tasks
    COMMENT 'Stores Pomodoro tasks for users';

-- Example of how to insert a test task (commented out for safety)
/*
INSERT INTO tasks (user_id, title, description) VALUES
    (1, 'Complete Project Documentation', 'Write technical documentation for the new feature');
*/

-- Example of how to update a task status (commented out for safety)
/*
UPDATE tasks 
SET status = 'completed', 
    updated_at = CURRENT_TIMESTAMP 
WHERE id = 1;
*/
