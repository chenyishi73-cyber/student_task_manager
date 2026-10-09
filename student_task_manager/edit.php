
<?php

// 引入网页头部文件
// 通常负责启动 Session、引入辅助函数以及显示导航栏
require_once __DIR__ . '/includes/header.php';

// 获取数据库连接对象
$pdo = getPDO();


// ---------- 1. 获取并验证任务 ID ----------

// 从 URL 中获取任务 ID
// 例如：edit.php?id=5
// 如果没有 id 参数，则默认设置为 0
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// 如果 ID 小于或等于 0，则说明任务 ID 无效
if ($id <= 0) {

    // 保存错误提示，供首页显示
    setFlash('error', 'Invalid task ID.');

    // 跳转回任务列表页面
    header('Location: index.php');

    // 终止程序，避免继续执行
    exit;
}


// ---------- 2. 查询任务的原有信息 ----------

// 准备 SQL 查询语句
// SELECT * 表示查询该任务的所有字段
// WHERE id = :id 表示只查询指定 ID 的任务
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id');

// 将任务 ID 绑定到 :id 参数并执行查询
$stmt->execute([':id' => $id]);

// 获取查询结果
// 如果任务存在，则返回关联数组；否则返回 false
$task = $stmt->fetch();


// 如果没有找到对应的任务
if (!$task) {

    // 设置任务不存在的错误提示
    setFlash('error', 'Task not found.');

    // 返回任务列表页面
    header('Location: index.php');

    // 结束程序
    exit;
}


// ---------- 3. 初始化错误信息 ----------

// 用于保存表单验证过程中产生的错误
$errors = [];


// ---------- 4. 处理用户提交的修改 ----------

// 检查请求是否为 POST
// GET 请求通常用于首次打开编辑页面
// POST 请求通常用于提交修改后的任务数据
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 获取用户提交的新任务数据
    // 如果字段不存在，则使用默认值
    $task = [
        // 保留当前任务的 ID，供后续 UPDATE 使用
        'id'          => $id,

        // 获取修改后的标题
        'title'       => $_POST['title']       ?? '',

        // 获取修改后的任务描述
        'description' => $_POST['description'] ?? '',

        // 获取修改后的分类
        'category'    => $_POST['category']    ?? '',

        // 获取修改后的优先级，默认为 Medium
        'priority'    => $_POST['priority']    ?? 'Medium',

        // 获取修改后的截止日期
        'due_date'    => $_POST['due_date']    ?? '',

        // 如果勾选 Completed，则设置为 1
        // 如果没有勾选，则设置为 0
        'completed'   => isset($_POST['completed']) ? 1 : 0,
    ];


    // 验证用户提交的数据
    // validateTask() 会检查标题、优先级、日期和分类
    // 验证失败时，返回错误信息数组
    $errors = validateTask($task);


    // 只有没有验证错误时，才更新数据库
    if (empty($errors)) {

        // 编写 SQL UPDATE 语句
        // SET 用于指定需要修改的字段
        // WHERE id = :id 确保只更新指定任务
        $sql = 'UPDATE tasks
                SET title = :title,
                    description = :description,
                    category = :category,
                    priority = :priority,
                    due_date = :due_date,
                    completed = :completed
                WHERE id = :id';

        // 准备 SQL 语句
        $stmt = $pdo->prepare($sql);

        // 执行 SQL，并将新数据绑定到相应的占位符
        $stmt->execute([
            ':title'       => $task['title'],
            ':description' => $task['description'],
            ':category'    => $task['category'],
            ':priority'    => $task['priority'],

            // 如果截止日期为空，则向数据库传入 NULL
            // 否则保存用户选择的日期
            ':due_date'    => $task['due_date'] !== ''
                              ? $task['due_date']
                              : null,

            // 更新任务完成状态
            ':completed'   => $task['completed'],

            // 指定需要更新的任务 ID
            ':id'          => $id,
        ]);


        // 保存更新成功的提示消息
        setFlash('success', 'Task updated successfully.');

        // 更新完成后返回任务列表页面
        header('Location: index.php');

        // 终止脚本，避免继续输出编辑页面
        exit;
    }
}
?>


<!-- ---------- 5. 显示编辑页面 ---------- -->

<!-- 页面标题 -->
<h2>Edit Task</h2>


<?php
// 如果存在验证错误，则显示错误提示框
if (!empty($errors)):
?>

    <div class="flash flash-error">

        <!-- 使用列表逐条显示错误信息 -->
        <ul>

            <?php foreach ($errors as $err): ?>

                <!-- 转义错误消息中的 HTML 特殊字符 -->
                <li><?= e($err) ?></li>

            <?php endforeach; ?>

        </ul>
    </div>

<?php endif; ?>


<!-- ---------- 6. 编辑任务表单 ---------- -->

<!-- method="post" 表示提交时使用 POST 方法 -->
<form method="post" class="task-form">

    <!-- 任务标题 -->
    <label>Title *<br>

        <!-- 显示原有标题或用户刚刚提交的标题 -->
        <!-- required 表示浏览器要求填写标题 -->
        <!-- maxlength 限制标题最多为 150 个字符 -->
        <input type="text" name="title" maxlength="150"
               value="<?= e($task['title']) ?>" required>
    </label>


    <!-- 任务描述 -->
    <label>Description<br>

        <!-- 显示原有描述或用户提交的新描述 -->
        <!-- rows="4" 表示默认显示 4 行文本 -->
        <textarea name="description" rows="4"><?= e($task['description']) ?></textarea>
    </label>


    <!-- 任务分类 -->
    <label>Category<br>

        <!-- 显示当前分类，允许用户修改 -->
        <!-- 最多输入 50 个字符 -->
        <input type="text" name="category" maxlength="50"
               value="<?= e($task['category']) ?>">
    </label>


    <!-- 任务优先级 -->
    <label>Priority<br>

        <!-- 下拉菜单，提供三个优先级选项 -->
        <select name="priority">

            <!-- 依次生成 Low、Medium 和 High 三个选项 -->
            <?php foreach (['Low', 'Medium', 'High'] as $p): ?>

                <!-- 如果选项与当前任务优先级一致，则自动选中 -->
                <option value="<?= e($p) ?>"
                    <?= $task['priority'] === $p ? 'selected' : '' ?>>

                    <!-- 显示优先级文字 -->
                    <?= e($p) ?>

                </option>

            <?php endforeach; ?>

        </select>
    </label>


    <!-- 任务截止日期 -->
    <label>Due Date<br>

        <!-- 日期选择框，显示当前任务的截止日期 -->
        <input type="date" name="due_date"
               value="<?= e($task['due_date']) ?>">
    </label>


    <!-- 任务完成状态 -->
    <label class="checkbox">

        <!-- 如果任务已完成，则自动勾选复选框 -->
        <!-- 勾选时提交 completed=1；不勾选时不提交该字段 -->
        <input type="checkbox" name="completed" value="1"
               <?= $task['completed'] ? 'checked' : '' ?>>

        <!-- 复选框旁边的说明文字 -->
        Completed
    </label>


    <!-- 提交按钮：保存修改后的任务 -->
    <button type="submit">Update Task</button>

    <!-- 取消编辑，返回任务列表，不提交当前表单 -->
    <a href="index.php" class="cancel">Cancel</a>

</form>


<?php
// 引入网页底部文件
require_once __DIR__ . '/includes/footer.php';
?>
