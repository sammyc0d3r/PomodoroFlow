-- Add pomodoros_needed column to tasks table
ALTER TABLE tasks
ADD COLUMN pomodoros_needed INT DEFAULT 1;

-- Update existing tasks to have default value
UPDATE tasks SET pomodoros_needed = 1 WHERE pomodoros_needed IS NULL;
