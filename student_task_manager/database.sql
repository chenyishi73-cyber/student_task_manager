
-- 创建数据库 student_task_manager
-- 如果数据库已经存在，则不会重复创建
-- CHARACTER SET utf8mb4：支持中文、英文和特殊字符
-- COLLATE utf8mb4_unicode_ci：设置字符串比较和排序规则
CREATE DATABASE IF NOT EXISTS student_task_manager
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;


-- 选择 student_task_manager 数据库
-- 后续的 SQL 操作默认在这个数据库中执行
USE student_task_manager;


-- 创建 tasks 表，用于保存学生的任务信息
-- 如果表已经存在，则不会重复创建
CREATE TABLE IF NOT EXISTS tasks (

    -- 任务的唯一编号
    -- INT：整数类型
    -- AUTO_INCREMENT：每插入一条新记录，编号自动增加
    -- PRIMARY KEY：主键，确保每条任务记录具有唯一标识
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- 任务标题
    -- VARCHAR(150)：最多保存 150 个字符
    -- NOT NULL：此字段不能为空
    title VARCHAR(150) NOT NULL,

    -- 任务详细描述
    -- TEXT：用于保存较长的文本内容
    -- 允许 NULL，表示任务可以没有描述
    description TEXT,

    -- 任务分类，例如 Study、Work 或 Personal
    -- 最多保存 50 个字符
    -- 允许 NULL
    category VARCHAR(50),

    -- 任务优先级，例如 Low、Medium 或 High
    -- 最多保存 20 个字符
    -- 如果插入记录时没有指定优先级，则默认为 Medium
    priority VARCHAR(20) DEFAULT 'Medium',

    -- 任务截止日期
    -- DATE：只保存日期，不保存具体时间
    -- 允许 NULL，表示任务没有截止日期
    due_date DATE,

    -- 任务完成状态
    -- TINYINT(1)：通常用于保存 0 或 1
    -- 0 表示未完成，1 表示已完成
    -- NOT NULL：不能为 NULL
    -- DEFAULT 0：如果没有指定状态，则默认为未完成
    completed TINYINT(1) NOT NULL DEFAULT 0,

    -- 任务创建时间
    -- DATETIME：保存日期和时间
    -- NOT NULL：不能为 NULL
    -- DEFAULT CURRENT_TIMESTAMP：插入记录时自动设置当前时间
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

-- ENGINE=InnoDB：使用 InnoDB 存储引擎
-- 支持事务、行级锁和外键等功能
) ENGINE=InnoDB;
