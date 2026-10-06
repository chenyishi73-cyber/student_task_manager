<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getPDO();

$task = [
    'title'       => '',
    'description' => '',
    'category'    => '',
    'priority'    => 'Medium',
    'due_date'    => '',
    'completed'   => 0,
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = [
        'title'       => $_POST['title']       ?? '',
        'description' => $_POST['description'] ?? '',
        'category'    => $_POST['category']    ?? '',
        'priority'    => $_POST['priority']    ?? 'Medium',
        'due_date'    => $_POST['due_date']    ?? '',
        'completed'   => isset($_POST['completed']) ? 1 : 0,
    ];

    $errors = validateTask($task);

    if (empty($errors)) {
        $sql = 'INSERT INTO tasks (title, description, category, priority, due_date, completed)
                VALUES (:title, :description, :category, :priority, :due_date, :completed)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title'       => $task['title'],
            ':description' => $task['description'],
            ':category'    => $task['category'],
            ':priority'    => $task['priority'],
            ':due_date'    => $task['due_date'] !== '' ? $task['due_date'] : null,
            ':completed'   => $task['completed'],
        ]);

        setFlash('success', 'Task created successfully.');
        header('Location: index.php');
        exit;
    }
}
?>

<h2>Create New Task</h2>

<?php if (!empty($errors)): ?>
    <div class="flash flash-error">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="task-form">
    <label>Title *<br>
        <input type="text" name="title" maxlength="150"
               value="<?= e($task['title']) ?>" required>
    </label>

    <label>Description<br>
        <textarea name="description" rows="4"><?= e($task['description']) ?></textarea>
    </label>

    <label>Category<br>
        <input type="text" name="category" maxlength="50"
               value="<?= e($task['category']) ?>">
    </label>

    <label>Priority<br>
        <select name="priority">
            <?php foreach (['Low', 'Medium', 'High'] as $p): ?>
                <option value="<?= e($p) ?>" <?= $task['priority'] === $p ? 'selected' : '' ?>>
                    <?= e($p) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Due Date<br>
        <input type="date" name="due_date" value="<?= e($task['due_date']) ?>">
    </label>

    <label class="checkbox">
        <input type="checkbox" name="completed" value="1"
               <?= $task['completed'] ? 'checked' : '' ?>>
        Already completed
    </label>

    <button type="submit">Save Task</button>
    <a href="index.php" class="cancel">Cancel</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
