
<?php

// 引入网页头部文件
// 通常负责启动 Session、加载辅助函数和显示导航栏
require_once __DIR__ . '/includes/header.php';

// 获取数据库连接对象
$pdo = getPDO();


// ---------- 1. 获取用户选择的筛选和排序条件 ----------

// 获取任务状态筛选条件
// all：显示全部任务
// incomplete：只显示未完成任务
// completed：只显示已完成任务
// 如果没有传入参数，则默认显示全部任务
$filter = $_GET['filter'] ?? 'all';

// 获取排序方式，默认按照截止日期升序排列
$sort = $_GET['sort'] ?? 'due_date';

// 获取优先级筛选条件
// 如果没有指定优先级，则显示所有优先级的任务
$priority = $_GET['priority'] ?? '';


// ---------- 2. 设置允许使用的排序方式 ----------

// 将用户可选择的排序名称映射为 SQL 排序表达式
// 不能直接把用户提交的 sort 参数拼接到 SQL 中
$allowedSorts = [

    // 按截止日期升序排列
    'due_date' => 'due_date ASC',

    // 按优先级排序：High 最先，然后 Medium、Low
    // 如果优先级不属于这三种，则排在最后
    // 相同优先级的任务再按照截止日期升序排列
    'priority' => "CASE priority
                   WHEN 'High' THEN 1
                   WHEN 'Medium' THEN 2
                   WHEN 'Low' THEN 3
                   ELSE 4
                   END, due_date ASC",

    // 按任务标题的字母顺序排列
    'title' => 'title ASC',

    // 按创建时间降序排列，最新创建的任务排在前面
    'created_at' => 'created_at DESC',
];

// 检查用户选择的排序方式是否在允许列表中
// 如果无效，则使用默认的截止日期排序方式
$orderBy = $allowedSorts[$sort] ?? $allowedSorts['due_date'];


// ---------- 3. 初始化 SQL 查询条件 ----------

// 保存需要添加到 WHERE 子句中的条件
$conditions = [];

// 保存 SQL 查询中使用的参数
$params = [];


// ---------- 4. 根据任务状态筛选 ----------

// 如果选择 incomplete，只显示未完成任务
if ($filter === 'incomplete') {
    $conditions[] = 'completed = 0';

// 如果选择 completed，只显示已完成任务
} elseif ($filter === 'completed') {
    $conditions[] = 'completed = 1';
}


// ---------- 5. 根据优先级筛选 ----------

// 定义允许的优先级
$allowedPriorities = ['Low', 'Medium', 'High'];

// 检查用户提交的优先级是否有效
if (in_array($priority, $allowedPriorities, true)) {

    // 将优先级筛选条件加入 SQL 条件数组
    $conditions[] = 'priority = :priority';

    // 为命名占位符 :priority 设置参数值
    $params[':priority'] = $priority;
}


// ---------- 6. 组合 WHERE 条件 ----------

// 如果存在筛选条件，则使用 AND 将它们连接起来
// 例如：WHERE completed = 0 AND priority = :priority
// 如果没有筛选条件，则不添加 WHERE 子句
$where = $conditions
    ? 'WHERE ' . implode(' AND ', $conditions)
    : '';


// ---------- 7. 查询任务数据 ----------

// 查询 tasks 表中的任务
// $where 保存筛选条件
// $orderBy 保存经过白名单验证的排序表达式
$sql = "SELECT * FROM tasks $where ORDER BY $orderBy";

// 准备 SQL 查询语句
$stmt = $pdo->prepare($sql);

// 执行查询，并传入筛选参数
$stmt->execute($params);

// 获取所有符合条件的任务
// 每条任务通常以关联数组的形式保存
$tasks = $stmt->fetchAll();


// ---------- 8. 统计任务数量 ----------

// 获取所有任务的总数量
$totalTasks = countTotal($pdo);

// 获取已完成任务的数量
$completedTasks = countCompleted($pdo);

// 使用总数减去已完成数量，计算未完成任务数量
$incompleteTasks = $totalTasks - $completedTasks;

?>


<!-- ---------- 9. 显示任务列表标题 ---------- -->

<h2>My Tasks</h2>


<!-- ---------- 10. 显示任务统计数据 ---------- -->

<div class="counters">

    <!-- 显示所有任务的数量 -->
    <span class="badge">
        Total: <?= $totalTasks ?>
    </span>

    <!-- 显示已经完成的任务数量 -->
    <span class="badge badge-done">
        Completed: <?= $completedTasks ?>
    </span>

    <!-- 显示尚未完成的任务数量 -->
    <span class="badge badge-pending">
        Incomplete: <?= $incompleteTasks ?>
    </span>

</div>


<!-- ---------- 11. 创建筛选和排序表单 ---------- -->

<!-- GET 方法会将选择的筛选条件放入 URL 参数中 -->
<form method="get" class="filters">

    <!-- 任务状态筛选 -->
    <label>Filter:

        <select name="filter">

            <!-- 显示全部任务 -->
            <option value="all"
                <?= $filter === 'all' ? 'selected' : '' ?>>
                All
            </option>

            <!-- 只显示未完成任务 -->
            <option value="incomplete"
                <?= $filter === 'incomplete' ? 'selected' : '' ?>>
                Incomplete
            </option>

            <!-- 只显示已完成任务 -->
            <option value="completed"
                <?= $filter === 'completed' ? 'selected' : '' ?>>
                Completed
            </option>

        </select>
    </label>


    <!-- 优先级筛选 -->
    <label>Priority:

        <select name="priority">

            <!-- 空字符串代表不限制优先级 -->
            <option value="">Any</option>

            <!-- 循环生成 Low、Medium、High 三个选项 -->
            <?php foreach ($allowedPriorities as $p): ?>

                <option value="<?= e($p) ?>"
                    <?= $priority === $p ? 'selected' : '' ?>>

                    <?= e($p) ?>

                </option>

            <?php endforeach; ?>

        </select>
    </label>


    <!-- 排序方式选择 -->
    <label>Sort by:

        <select name="sort">

            <!-- 按截止日期排序 -->
            <option value="due_date"
                <?= $sort === 'due_date' ? 'selected' : '' ?>>
                Due date
            </option>

            <!-- 按优先级排序 -->
            <option value="priority"
                <?= $sort === 'priority' ? 'selected' : '' ?>>
                Priority
            </option>

            <!-- 按任务标题排序 -->
            <option value="title"
                <?= $sort === 'title' ? 'selected' : '' ?>>
                Title
            </option>

            <!-- 最新创建的任务排在前面 -->
            <option value="created_at"
                <?= $sort === 'created_at' ? 'selected' : '' ?>>
                Newest
            </option>

        </select>
    </label>


    <!-- 点击按钮后提交筛选条件 -->
    <button type="submit">Apply</button>

</form>


<?php
// ---------- 12. 判断是否有符合条件的任务 ----------

// 如果任务数组为空，说明没有任务符合当前筛选条件
if (empty($tasks)):
?>

    <!-- 显示空任务提示，并提供创建任务的链接 -->
    <p class="empty">
        No tasks found.
        <a href="create.php">Create one</a>.
    </p>

<?php else: ?>

    <!-- ---------- 13. 显示任务表格 ---------- -->

    <table>

        <!-- 表格标题行 -->
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <!-- 表格数据区域 -->
        <tbody>

        <!-- 遍历所有查询到的任务 -->
        <?php foreach ($tasks as $task): ?>

            <?php
                // 判断当前任务是否已经逾期
                // 将 completed 转换为整数再传入函数
                $overdue = isOverdue(
                    $task['due_date'],
                    (int) $task['completed']
                );

                // 如果任务逾期，则为表格行添加 overdue CSS 类
                // 否则使用空字符串，不添加额外样式
                $rowClass = $overdue ? 'overdue' : '';
            ?>

            <!-- 根据是否逾期动态设置表格行的 CSS 类 -->
            <tr class="<?= $rowClass ?>">

                <!-- ---------- 任务标题和描述 ---------- -->
                <td>

                    <!-- 显示任务标题 -->
                    <strong><?= e($task['title']) ?></strong>

                    <?php if ($overdue): ?>

                        <!-- 如果任务逾期，显示 OVERDUE 标签 -->
                        <span class="tag-overdue">OVERDUE</span>

                    <?php endif; ?>


                    <?php if (!empty($task['description'])): ?>

                        <!-- 如果存在任务描述，则换行显示 -->
                        <!-- small 标签让描述文字更小 -->
                        <br>
                        <small><?= e($task['description']) ?></small>

                    <?php endif; ?>

                </td>


                <!-- ---------- 任务分类 ---------- -->
                <td>

                    <!-- 如果分类为空，则显示空字符串 -->
                    <?
                        // 此处不需要额外的 PHP 逻辑
                    ?>
                    <?= e($task['category'] ?: '') ?>

                </td>


                <!-- ---------- 任务优先级 ---------- -->
                <td>

                    <!--
                        根据优先级设置不同的 CSS 类
                        High   -> priority-high
                        Medium -> priority-medium
                        Low    -> priority-low
                    -->
                    <span class="priority priority-<?= e(strtolower($task['priority'])) ?>">

                        <!-- 显示优先级文字 -->
                        <?= e($task['priority']) ?>

                    </span>

                </td>


                <!-- ---------- 任务截止日期 ---------- -->
                <td>

                    <!-- 如果没有截止日期，则显示空字符串 -->
                    <?= e($task['due_date'] ?: '') ?>

                </td>


                <!-- ---------- 任务完成状态 ---------- -->
                <td>

                    <?php if ($task['completed']): ?>

                        <!-- 已完成任务显示 Done -->
                        <span class="status done">Done</span>

                    <?php else: ?>

                        <!-- 未完成任务显示 Pending -->
                        <span class="status pending">Pending</span>

                    <?php endif; ?>

                </td>


                <!-- ---------- 编辑和删除操作 ---------- -->
                <td class="actions">

                    <!-- 编辑链接，将当前任务 ID 传递给 edit.php -->
                    <a href="edit.php?id=<?= (int) $task['id'] ?>">
                        Edit
                    </a>

                    <!--
                        删除链接，将任务 ID 传递给 delete.php
                        delete-link 用于 JavaScript 识别删除链接
                        data-title 保存任务标题，供确认弹窗使用
                    -->
                    <a href="delete.php?id=<?= (int) $task['id'] ?>"
                       class="delete-link"
                       data-title="<?= e($task['title']) ?>">
                        Delete
                    </a>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>

<?php endif; ?>


<!-- ---------- 14. 删除确认功能 ---------- -->

<script>

// 查找页面中所有 class 为 delete-link 的链接
document.querySelectorAll('.delete-link').forEach(function (link) {

    // 为每个删除链接添加点击事件
    link.addEventListener('click', function (evt) {

        // 获取链接上的任务标题，并显示确认弹窗
        // 例如：Delete "Finish homework"?
        if (!confirm('Delete "' + link.dataset.title + '"?')) {

            // 如果用户点击 Cancel，则阻止浏览器访问删除链接
            evt.preventDefault();
        }

        // 如果用户点击 OK，则浏览器继续访问 delete.php
    });

});

</script>


<?php
// 引入网页底部文件
require_once __DIR__ . '/includes/footer.php';
?>
