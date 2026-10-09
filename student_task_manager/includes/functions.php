
<?php

// 引入数据库配置文件
// __DIR__ 表示当前 PHP 文件所在的目录
// ../config/database.php 表示上一级目录中的 config 文件夹下的 database.php
require_once __DIR__ . '/../config/database.php';


// 将用户输入的字符串转换为安全的 HTML 字符串
// ?string 表示参数可以是字符串，也可以是 null
// : string 表示函数最终返回字符串
function e(?string $value): string
{
    // 如果 value 为 null，则使用空字符串代替
    // htmlspecialchars 防止 HTML 特殊字符被浏览器当作代码执行
    // ENT_QUOTES 同时转义单引号和双引号
    // UTF-8 指定字符编码
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


// 判断任务是否已经逾期
// $dueDate：任务截止日期
// $completed：任务完成状态，1 表示已完成
// 返回 true 表示逾期，false 表示未逾期
function isOverdue(?string $dueDate, int $completed): bool
{
    // 如果任务已经完成，或者没有设置截止日期，则不算逾期
    if ($completed === 1 || empty($dueDate)) {
        return false;
    }

    // 将截止日期与今天的日期进行比较
    // strtotime() 将日期转换为时间戳，便于比较
    // 如果截止日期早于今天，则说明任务已经逾期
    return strtotime($dueDate) < strtotime(date('Y-m-d'));
}


// 验证任务表单提交的数据
// array &$data 表示传入数组的引用，函数可以直接修改原数组
// 返回包含所有错误信息的数组；如果没有错误，则返回空数组
function validateTask(array &$data): array
{
    // 用于保存表单验证过程中发现的错误
    $errors = [];


    // ---------- 1. 验证任务标题 ----------

    // 获取标题，如果没有提供则使用空字符串
    // trim() 删除字符串开头和结尾的空格
    $data['title'] = trim($data['title'] ?? '');

    // 检查标题是否为空
    if ($data['title'] === '') {
        $errors[] = 'Title is required.';

    // 检查标题长度是否至少为 3 个字符
    // mb_strlen() 可以正确计算中文等多字节字符的长度
    } elseif (mb_strlen($data['title']) < 3) {
        $errors[] = 'Title must be at least 3 characters.';

    // 检查标题长度是否超过 150 个字符
    } elseif (mb_strlen($data['title']) > 150) {
        $errors[] = 'Title must be 150 characters or fewer.';
    }


    // ---------- 2. 验证任务优先级 ----------

    // 定义允许使用的优先级选项
    $allowedPriorities = ['Low', 'Medium', 'High'];

    // in_array() 检查提交的优先级是否存在于允许列表中
    // 第三个参数 true 表示进行严格比较
    if (!in_array($data['priority'] ?? '', $allowedPriorities, true)) {
        $errors[] = 'Priority must be Low, Medium, or High.';
    }


    // ---------- 3. 验证任务截止日期 ----------

    // 如果填写了截止日期，则检查日期格式和有效性
    if (!empty($data['due_date'])) {

        // 按照 YYYY-MM-DD 格式解析日期
        $d = DateTime::createFromFormat('Y-m-d', $data['due_date']);

        // 如果解析失败，或者格式化后的日期与原日期不同，
        // 则认为日期无效
        if (!$d || $d->format('Y-m-d') !== $data['due_date']) {
            $errors[] = 'Due date must be a valid date (YYYY-MM-DD).';
        }
    }


    // ---------- 4. 验证任务分类 ----------

    // 获取分类名称，并删除开头和结尾的空格
    // 如果没有提供分类，则使用空字符串
    $data['category'] = trim($data['category'] ?? '');

    // 分类名称最多允许 50 个字符
    if (mb_strlen($data['category']) > 50) {
        $errors[] = 'Category must be 50 characters or fewer.';
    }


    // 返回所有验证错误
    // 如果验证全部通过，则返回空数组
    return $errors;
}


// 保存一次性提示消息
// $type 表示消息类型，例如 success 或 error
// $message 表示要显示给用户的消息
function setFlash(string $type, string $message): void
{
    // 将消息保存到 Session 中
    // 其他页面可以读取该消息并显示给用户
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}


// 获取一次性提示消息
// 如果存在消息，则返回消息数组；否则返回 null
function getFlash(): ?array
{
    // 检查 Session 中是否存在 flash 消息
    if (isset($_SESSION['flash'])) {

        // 先保存消息内容
        $flash = $_SESSION['flash'];

        // 删除 Session 中的消息，防止刷新页面时重复显示
        unset($_SESSION['flash']);

        // 返回消息内容
        return $flash;
    }

    // 如果没有消息，则返回 null
    return null;
}


// 统计已经完成的任务数量
// PDO $pdo 表示传入数据库连接对象
// : int 表示返回整数
function countCompleted(PDO $pdo): int
{
    // 查询 tasks 表中 completed 字段等于 1 的记录数量
    $stmt = $pdo->query(
        'SELECT COUNT(*) FROM tasks WHERE completed = 1'
    );

    // fetchColumn() 获取查询结果的第一列
    // 使用 int 将结果转换为整数后返回
    return (int) $stmt->fetchColumn();
}


// 统计任务总数量
function countTotal(PDO $pdo): int
{
    // 查询 tasks 表中的所有任务记录数量
    $stmt = $pdo->query('SELECT COUNT(*) FROM tasks');

    // 获取查询结果，并将其转换为整数
    return (int) $stmt->fetchColumn();
}
