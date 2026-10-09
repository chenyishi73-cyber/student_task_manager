
<?php

// 定义数据库连接函数，返回一个 PDO 对象
function getPDO(): PDO
{
    // 使用 static 保存数据库连接，避免每次调用函数都重新连接
    static $pdo = null;

    // 如果还没有建立数据库连接，则创建连接
    if ($pdo === null) {

        // 数据库服务器地址
        $host = 'localhost';

        // 数据库名称
        $db = 'student_task_manager';

        // 数据库用户名
        $user = 'root';

        // 数据库密码
        $pass = 'root';

        // 数据库字符编码，utf8mb4 支持中文和特殊字符
        $charset = 'utf8mb4';

        // 创建 PDO 数据源名称（DSN），指定数据库类型、地址、名称和编码
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        // 设置 PDO 的连接选项
        $options = [
            // 数据库发生错误时抛出异常，方便调试
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // 默认以关联数组的形式获取查询结果
            // 例如：$row['student_name']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // 禁用模拟预处理，使用数据库支持的真实预处理语句
            // 有助于安全地执行带参数的 SQL 查询
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            // 创建 PDO 对象，连接 MySQL 数据库
            $pdo = new PDO($dsn, $user, $pass, $options);

        } catch (PDOException $e) {
            // 如果连接失败，显示错误信息并终止程序
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    // 返回数据库连接对象，供其他 PHP 文件执行 SQL 操作
    return $pdo;
}
