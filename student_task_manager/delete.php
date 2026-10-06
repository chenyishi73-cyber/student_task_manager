<?php
require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid task ID.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT title FROM tasks WHERE id = :id');
$stmt->execute([':id' => $id]);
$task = $stmt->fetch();

if (!$task) {
    setFlash('error', 'Task not found.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
$stmt->execute([':id' => $id]);

setFlash('success', 'Task "' . $task['title'] . '" deleted.');
header('Location: index.php');
exit;
