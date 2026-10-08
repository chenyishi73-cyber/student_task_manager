CREATE DATABASE IF NOT EXISTS student_task_manager
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE student_task_manager;

CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    category    VARCHAR(50),
    priority    VARCHAR(20) DEFAULT 'Medium',
    due_date    DATE,
    completed   TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
