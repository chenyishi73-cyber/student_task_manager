<?php
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function isOverdue(?string $dueDate, int $completed): bool
{
    if ($completed === 1 || empty($dueDate)) {
        return false;
    }
    return strtotime($dueDate) < strtotime(date('Y-m-d'));
}

function validateTask(array &$data): array
{
    $errors = [];

    $data['title'] = trim($data['title'] ?? '');
    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($data['title']) < 3) {
        $errors[] = 'Title must be at least 3 characters.';
    } elseif (mb_strlen($data['title']) > 150) {
        $errors[] = 'Title must be 150 characters or fewer.';
    }

    $allowedPriorities = ['Low', 'Medium', 'High'];
    if (!in_array($data['priority'] ?? '', $allowedPriorities, true)) {
        $errors[] = 'Priority must be Low, Medium, or High.';
    }

    if (!empty($data['due_date'])) {
        $d = DateTime::createFromFormat('Y-m-d', $data['due_date']);
        if (!$d || $d->format('Y-m-d') !== $data['due_date']) {
            $errors[] = 'Due date must be a valid date (YYYY-MM-DD).';
        }
    }

    $data['category'] = trim($data['category'] ?? '');
    if (mb_strlen($data['category']) > 50) {
        $errors[] = 'Category must be 50 characters or fewer.';
    }

    return $errors;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function countCompleted(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM tasks WHERE completed = 1');
    return (int) $stmt->fetchColumn();
}

function countTotal(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM tasks');
    return (int) $stmt->fetchColumn();
}
