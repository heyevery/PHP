<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "login_system";

// 创建连接
$conn = new mysqli($servername, $username, $password);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 创建数据库
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("创建数据库错误: " . $conn->error);
}

// 选择数据库
$conn->select_db($dbname);

// 检查数据库选择是否成功
if ($conn->error) {
    die("选择数据库失败: " . $conn->error);
}

// 检查 users 表是否存在，如果不存在则创建
$table_exists = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_exists->num_rows == 0) {
    $create_table_sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        avatar VARCHAR(255) DEFAULT 'default_avatar.png'
    )";
    if ($conn->query($create_table_sql) === FALSE)
    {
        die("Error creating table: " . $conn->error);
    }
} else {
    // 如果表已存在，检查是否有 avatar 字段，如果没有则添加
    $column_exists = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if ($column_exists->num_rows == 0) {
        $alter_table_sql = "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT 'default_avatar.png'";
        if ($conn->query($alter_table_sql) === FALSE) {
            die("Error adding column avatar: " . $conn->error);
        }
    }
}