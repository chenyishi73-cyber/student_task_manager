
<?php
// 检查当前 Session 是否尚未启动
// 如果没有启动，则调用 session_start() 开启 Session
// Session 用于在不同页面之间保存用户的临时数据，例如提示消息
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 引入 functions.php 文件
// __DIR__ 表示当前文件所在的目录
// require_once 确保该文件只被引入一次
// 该文件包含 getFlash()、e() 等辅助函数
require_once __DIR__ . '/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- 设置网页字符编码为 UTF-8，支持中文和特殊字符 -->
    <meta charset="UTF-8">

    <!-- 设置浏览器标签页显示的标题 -->
    <title>Student Task Manager</title>

    <!-- 引入外部 CSS 文件，用于设置网页样式 -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

<!-- 网页顶部区域，通常包含网站标题和导航菜单 -->
<header>

    <!-- 显示网站名称 -->
    <h1>=Ú Student Task Manager</h1>

    <!-- 网站导航菜单 -->
    <nav>

        <!-- 点击后跳转到 index.php，查看所有任务 -->
        <a href="index.php">All Tasks</a>

        <!-- 点击后跳转到 create.php，创建新任务 -->
        <a href="create.php">+ New Task</a>

    </nav>
</header>

<!-- 网页的主要内容区域 -->
<main>

<?php
// 获取保存在 Session 中的一次性提示消息
// 如果没有提示消息，getFlash() 将返回 null
$flash = getFlash();

// 如果存在提示消息，则显示下面的 HTML 内容
if ($flash): ?>

    <!--
        flash 是提示框的基础 CSS 类
        flash-<?= e($flash['type']) ?> 根据消息类型动态设置 CSS 类
        例如：flash-success 或 flash-error
        e() 用于转义输出内容，降低 HTML 注入风险
    -->
    <div class="flash flash-<?= e($flash['type']) ?>">

        <!-- 显示提示消息，并转义特殊 HTML 字符 -->
        <?= e($flash['message']) ?>

    </div>

<?php endif; ?>
<!-- endif 结束上面的 if 条件判断 -->
