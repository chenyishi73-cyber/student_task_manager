<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>=Ú Student Task Manager</h1>
    <nav>
        <a href="index.php">All Tasks</a>
        <a href="create.php">+ New Task</a>
    </nav>
</header>
<main>

<?php
$flash = getFlash();
if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
