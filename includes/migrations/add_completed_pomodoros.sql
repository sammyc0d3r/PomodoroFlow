-- Add completed_pomodoros column to tasks table
ALTER TABLE tasks
ADD COLUMN completed_pomodoros INT DEFAULT 0;

-- Update existing tasks to have default value
UPDATE tasks SET completed_pomodoros = 0 WHERE completed_pomodoros IS NULL;
