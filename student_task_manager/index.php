<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getPDO();

$filter   = $_GET['filter'] ?? 'all';
$sort     = $_GET['sort'] ?? 'due_date';
$priority = $_GET['priority'] ?? '';

$allowedSorts = [
    'due_date'   => 'due_date ASC',
    'priority'   => "CASE priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 WHEN 'Low' THEN 3 ELSE 4 END, due_date ASC",
    'title'      => 'title ASC',
    'created_at' => 'created_at DESC',
];
$orderBy = $allowedSorts[$sort] ?? $allowedSorts['due_date'];

$conditions = [];
$params     = [];

if ($filter === 'incomplete') {
    $conditions[] = 'completed = 0';
} elseif ($filter === 'completed') {
    $conditions[] = 'completed = 1';
}

$allowedPriorities = ['Low', 'Medium', 'High'];
if (in_array($priority, $allowedPriorities, true)) {
    $conditions[] = 'priority = :priority';
    $params[':priority'] = $priority;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql  = "SELECT * FROM tasks $where ORDER BY $orderBy";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$totalTasks      = countTotal($pdo);
$completedTasks  = countCompleted($pdo);
$incompleteTasks = $totalTasks - $completedTasks;
?>

<h2>My Tasks</h2>

<div class="counters">
    <span class="badge">Total: <?= $totalTasks ?></span>
    <span class="badge badge-done">Completed: <?= $completedTasks ?></span>
    <span class="badge badge-pending">Incomplete: <?= $incompleteTasks ?></span>
</div>

<form method="get" class="filters">
    <label>Filter:
        <select name="filter">
            <option value="all"        <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="incomplete" <?= $filter === 'incomplete' ? 'selected' : '' ?>>Incomplete</option>
            <option value="completed"  <?= $filter === 'completed' ? 'selected' : '' ?>>Completed</option>
        </select>
    </label>

    <label>Priority:
        <select name="priority">
            <option value="">Any</option>
            <?php foreach ($allowedPriorities as $p): ?>
                <option value="<?= e($p) ?>" <?= $priority === $p ? 'selected' : '' ?>>
                    <?= e($p) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Sort by:
        <select name="sort">
            <option value="due_date"   <?= $sort === 'due_date' ? 'selected' : '' ?>>Due date</option>
            <option value="priority"   <?= $sort === 'priority' ? 'selected' : '' ?>>Priority</option>
            <option value="title"      <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Newest</option>
        </select>
    </label>

    <button type="submit">Apply</button>
</form>

<?php if (empty($tasks)): ?>
    <p class="empty">No tasks found. <a href="create.php">Create one</a>.</p>
<?php else: ?>
<table>
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
    <tbody>
    <?php foreach ($tasks as $task): ?>
        <?php
            $overdue = isOverdue($task['due_date'], (int) $task['completed']);
            $rowClass = $overdue ? 'overdue' : '';
        ?>
        <tr class="<?= $rowClass ?>">
            <td>
                <strong><?= e($task['title']) ?></strong>
                <?php if ($overdue): ?>
                    <span class="tag-overdue">OVERDUE</span>
                <?php endif; ?>
                <?php if (!empty($task['description'])): ?>
                    <br><small><?= e($task['description']) ?></small>
                <?php endif; ?>
            </td>
            <td><?= e($task['category'] ?: '') ?></td>
            <td><span class="priority priority-<?= e(strtolower($task['priority'])) ?>">
                <?= e($task['priority']) ?></span>
            </td>
            <td><?= e($task['due_date'] ?: '') ?></td>
            <td>
                <?php if ($task['completed']): ?>
                    <span class="status done"> Done</span>
                <?php else: ?>
                    <span class="status pending">Pending</span>
                <?php endif; ?>
            </td>
            <td class="actions">
                <a href="edit.php?id=<?= (int) $task['id'] ?>">Edit</a>
                <a href="delete.php?id=<?= (int) $task['id'] ?>"
                   class="delete-link"
                   data-title="<?= e($task['title']) ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<script>
document.querySelectorAll('.delete-link').forEach(function (link) {
    link.addEventListener('click', function (evt) {
        if (!confirm('Delete "' + link.dataset.title + '"?')) {
            evt.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
