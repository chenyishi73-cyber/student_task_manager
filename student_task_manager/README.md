# Student Task Manager
# 学生任务管理器

## Student Information
## 学生信息
- **Name**: [Your Name] / [你的名字]
- **Student ID**: [Your Student ID] / [你的学号]

## Setup Instructions
## 安装说明

1. Copy the project folder into the `htdocs` directory of XAMPP/Laragon.
   把项目文件夹复制到 XAMPP/Laragon 的 `htdocs` 目录。

2. Start Apache and MySQL.
   启动 Apache 和 MySQL。

3. Open phpMyAdmin and import `database.sql`.
   打开 phpMyAdmin，导入 `database.sql`。

4. If your MySQL password is not empty, edit `$pass` in `config/database.php`.
   如果你的 MySQL 密码不是空，编辑 `config/database.php` 里的 `$pass`。

5. Open the browser and visit `http://localhost/student_task_manager/index.php`.
   浏览器访问 `http://localhost/student_task_manager/index.php`。

## Features
## 功能

- **CREATE**: `create.php` — Add a new task
  **创建**：`create.php` — 添加新任务
- **READ**: `index.php` — Display all tasks
  **读取**：`index.php` — 显示所有任务
- **UPDATE**: `edit.php` — Edit an existing task
  **更新**：`edit.php` — 编辑已有任务
- **DELETE**: `delete.php` — Delete a task
  **删除**：`delete.php` — 删除任务

## Assigned Challenge
## 已实现的挑战功能

1. **Filter incomplete tasks** — Filter dropdown (All / Incomplete / Completed)
   **筛选未完成任务** — 下拉筛选框（全部 / 未完成 / 已完成）

2. **Highlight overdue tasks** — OVERDUE tag + orange row background
   **高亮过期任务** — OVERDUE 标签 + 橙色行背景

3. **Sort tasks** — Sort by due date, priority, or title
   **排序任务** — 按截止日期、优先级或标题排序

4. **Count completed tasks** — Counter badges at the top
   **统计已完成任务** — 页面顶部计数器徽章

## AI-Use Reflection
## AI 使用反思

**AI tool(s) used**: ChatGPT / Claude
**使用的 AI 工具**：ChatGPT / Claude

**Three examples of how AI helped me**:
**AI 帮助我的三个例子**：

1. Suggested the standard PDO connection pattern (`ERRMODE_EXCEPTION`, `EMULATE_PREPARES = false`).
   建议了标准的 PDO 连接写法（`ERRMODE_EXCEPTION`、`EMULATE_PREPARES = false`）。

2. Helped write strict date validation inside `validateTask()` using `DateTime::createFromFormat`.
   帮助在 `validateTask()` 里用 `DateTime::createFromFormat` 编写严格的日期校验。

3. Suggested using a whitelist array for the `sort` parameter to prevent SQL injection.
   建议用白名单数组处理 `sort` 参数，防止 SQL 注入。

**One AI-generated suggestion I changed or rejected**:
**我修改或拒绝的一条 AI 建议**：

- **What**: The AI initially suggested directly concatenating `$_GET['filter']` into the SQL query.
  **内容**：AI 最初建议直接把 `$_GET['filter']` 拼接到 SQL 查询里。

- **Why**: That would cause SQL injection. I replaced it with fixed condition strings, a whitelist array, and prepared statement placeholders.
  **原因**：那会导致 SQL 注入。我改成了固定条件字符串、白名单数组和预处理语句占位符。

**The part of this application I understand least**:
**我对本应用理解最弱的部分**：

- (Fill in honestly, e.g. the exact behavior of PDO's `unix_socket` connection,
  or how `PDO::ATTR_EMULATE_PREPARES = false` differs from the emulated mode
  on different MySQL versions.)
  如实填写，例如 PDO 的 `unix_socket` 连接细节，
  或者 `PDO::ATTR_EMULATE_PREPARES = false` 在不同 MySQL 版本下的行为差异。
