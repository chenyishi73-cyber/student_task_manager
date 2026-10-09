
<?php

// 引入网页头部文件
// 该文件通常会启动 Session、加载辅助函数，并显示网页标题和导航栏
require_once __DIR__ . '/includes/header.php';

// 获取数据库连接对象，用于执行 SQL 操作
$pdo = getPDO();


// 初始化任务数据
// 第一次打开页面时，表单使用这些默认值
$task = [
    'title'       => '',       // 任务标题，默认为空
    'description' => '',       // 任务描述，默认为空
    'category'    => '',       // 任务分类，默认为空
    'priority'    => 'Medium', // 默认优先级为 Medium
    'due_date'    => '',       // 截止日期，默认为空
    'completed'   => 0,        // 默认未完成，0 表示未完成
];

// 初始化错误数组，用于保存表单验证错误
$errors = [];


// 检查用户是否通过 POST 方法提交表单
// 第一次打开页面通常是 GET 请求
// 点击 Save Task 后，浏览器会发送 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 从提交的数据中获取各个字段
    // 如果某个字段不存在，则使用默认值
    $task = [
        'title'       => $_POST['title']       ?? '',
        'description' => $_POST['description'] ?? '',
        'category'    => $_POST['category']    ?? '',
        'priority'    => $_POST['priority']    ?? 'Medium',

        // 获取截止日期；如果没有提交，则使用空字符串
        'due_date'    => $_POST['due_date']    ?? '',

        // 如果勾选了完成复选框，则设置为 1
        // 如果没有勾选，则设置为 0
        'completed'   => isset($_POST['completed']) ? 1 : 0,
    ];

    // 调用 validateTask() 检查标题、优先级、日期和分类
    // 如果验证失败，返回包含错误信息的数组
    $errors = validateTask($task);


    // 只有在没有验证错误时，才将任务保存到数据库
    if (empty($errors)) {

        // 创建 SQL 插入语句
        // 使用命名占位符，避免直接拼接用户输入到 SQL 中
        $sql = 'INSERT INTO tasks
                (title, description, category, priority, due_date, completed)
                VALUES
                (:title, :description, :category, :priority, :due_date, :completed)';

        // 准备 SQL 语句
        // prepare() 将 SQL 语句与后续的数据绑定分开
        $stmt = $pdo->prepare($sql);

        // 执行 SQL 语句，并将表单数据绑定到对应占位符
        $stmt->execute([
            ':title'       => $task['title'],
            ':description' => $task['description'],
            ':category'    => $task['category'],
            ':priority'    => $task['priority'],

            // 如果截止日期不为空，则保存日期
            // 如果为空，则传入 null，表示数据库中没有设置截止日期
            ':due_date'    => $task['due_date'] !== ''
                              ? $task['due_date']
                              : null,

            // 保存任务完成状态：1 表示完成，0 表示未完成
            ':completed'   => $task['completed'],
        ]);

        // 设置一次性成功提示
        // 跳转后，首页可以通过 getFlash() 显示这条消息
        setFlash('success', 'Task created successfully.');

        // 保存成功后跳转到任务列表页面
        header('Location: index.php');

        // 终止当前脚本，避免继续执行后面的代码
        exit;
    }
}
?>


<!-- 显示页面标题 -->
<h2>Create New Task</h2>


<?php
// 如果错误数组不为空，说明表单验证没有通过
if (!empty($errors)):
?>

    <!-- 显示错误提示框 -->
    <div class="flash flash-error">

        <!-- 使用无序列表逐条显示错误 -->
        <ul>

            <?php foreach ($errors as $err): ?>

                <!-- 输出每一条错误信息
                     e() 用于转义 HTML 特殊字符，降低 XSS 风险 -->
                <li><?= e($err) ?></li>

            <?php endforeach; ?>

        </ul>
    </div>

<?php endif; ?>


<!-- 创建任务表单
     method="post" 表示提交时通过 POST 方法发送数据
     task-form 是 CSS 类，用于设置表单样式 -->
<form method="post" class="task-form">

    <!-- ---------- 1. 任务标题 ---------- -->
    <label>Title *<br>

        <!--
            type="text" 表示文本输入框
            name="title" 是提交到 PHP 的字段名称
            maxlength="150" 限制最多输入 150 个字符
            value 用于保留之前输入的内容
            required 表示浏览器要求填写该字段
        -->
        <input type="text" name="title" maxlength="150"
               value="<?= e($task['title']) ?>" required>
    </label>


    <!-- ---------- 2. 任务描述 ---------- -->
    <label>Description<br>

        <!--
            textarea 用于输入多行文本
            rows="4" 表示默认显示约 4 行文本
            将之前输入的描述放回文本框，避免验证失败后内容丢失
        -->
        <textarea name="description" rows="4"><?= e($task['description']) ?></textarea>
    </label>


    <!-- ---------- 3. 任务分类 ---------- -->
    <label>Category<br>

        <!-- 输入任务分类，最多允许 50 个字符 -->
        <input type="text" name="category" maxlength="50"
               value="<?= e($task['category']) ?>">
    </label>


    <!-- ---------- 4. 任务优先级 ---------- -->
    <label>Priority<br>

        <!-- 下拉菜单，用于选择任务优先级 -->
        <select name="priority">

            <!-- 遍历三个允许的优先级选项 -->
            <?php foreach (['Low', 'Medium', 'High'] as $p): ?>

                <!--
                    value 是提交给 PHP 的值
                    如果当前选项与任务已有的优先级相同，
                    则添加 selected，让它成为默认选项
                -->
                <option value="<?= e($p) ?>"
                    <?= $task['priority'] === $p ? 'selected' : '' ?>>

                    <!-- 显示优先级文字 -->
                    <?= e($p) ?>

                </option>

            <?php endforeach; ?>

        </select>
    </label>


    <!-- ---------- 5. 任务截止日期 ---------- -->
    <label>Due Date<br>

        <!--
            type="date" 显示日期选择器
            日期通常以 YYYY-MM-DD 格式提交
            value 用于保留已经填写的日期
        -->
        <input type="date" name="due_date"
               value="<?= e($task['due_date']) ?>">
    </label>


    <!-- ---------- 6. 任务完成状态 ---------- -->
    <label class="checkbox">

        <!--
            复选框用于标记任务是否已经完成
            name="completed" 是提交字段名称
            value="1" 表示勾选时提交的值
            如果 completed 为真，则显示 checked
        -->
        <input type="checkbox" name="completed" value="1"
               <?= $task['completed'] ? 'checked' : '' ?>>

        <!-- 复选框旁边显示的文字 -->
        Already completed

    </label>


    <!-- 提交按钮：点击后提交整个表单 -->
    <button type="submit">Save Task</button>

    <!-- 取消按钮：返回任务列表，不保存当前表单 -->
    <a href="index.php" class="cancel">Cancel</a>

</form>


<?php
// 引入网页底部文件
// 通常包含结束 main、body、html 标签或其他公共页面内容
require_once __DIR__ . '/includes/footer.php';
?>
