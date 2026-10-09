
<?php

// 引入辅助函数文件
// 其中包含 getPDO()、setFlash() 等函数
require_once __DIR__ . '/includes/functions.php';


// 获取数据库连接对象
$pdo = getPDO();


// ---------- 1. 获取并验证任务 ID ----------

// 检查 URL 中是否传入了 id 参数
// 例如：delete.php?id=5
// 如果存在，则将其转换为整数；否则设置为 0
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// 如果 ID 小于或等于 0，说明 ID 无效
if ($id <= 0) {

    // 保存错误提示，供跳转后的首页显示
    setFlash('error', 'Invalid task ID.');

    // 返回任务列表页面
    header('Location: index.php');

    // 立即终止脚本，避免继续执行删除操作
    exit;
}


// ---------- 2. 检查任务是否存在 ----------

// 准备 SQL 查询语句，只查询指定任务的标题
// 使用 :id 作为命名参数，避免直接拼接用户输入
$stmt = $pdo->prepare('SELECT title FROM tasks WHERE id = :id');

// 将任务 ID 绑定到 :id 参数并执行查询
$stmt->execute([':id' => $id]);

// 获取查询结果
// 如果找到任务，则返回关联数组；否则返回 false
$task = $stmt->fetch();


// 如果没有找到对应的任务
if (!$task) {

    // 保存任务不存在的错误提示
    setFlash('error', 'Task not found.');

    // 返回任务列表页面
    header('Location: index.php');

    // 终止脚本，不再执行删除操作
    exit;
}


// ---------- 3. 删除任务 ----------

// 准备删除 SQL 语句
// 只删除 id 与指定值匹配的任务
$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');

// 绑定任务 ID 并执行删除
$stmt->execute([':id' => $id]);


// ---------- 4. 显示删除成功提示 ----------

// 将被删除任务的标题放入提示消息
// 例如：Task "Complete PHP homework" deleted.
// setFlash() 会将消息保存到 Session 中
setFlash('success', 'Task "' . $task['title'] . '" deleted.');


// ---------- 5. 返回任务列表 ----------

// 删除完成后跳转到首页
header('Location: index.php');

// 终止当前脚本
exit;
