<?php
// 创建数据库连接
$conn = new mysqli("localhost", "root", "root", "my_project");

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 设置正确的字符编码
$conn->set_charset("utf8mb4");

// 检查并创建users表（如果不存在）
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    avatar VARCHAR(255) DEFAULT 'default_avatar.png',
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB ROW_FORMAT=COMPACT DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    // 表创建成功
} else {
    // 如果表创建失败，输出错误信息
    echo "错误: " . $conn->error;
}

// 创建用户资料表（如果不存在）
$sql = "CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT(6) UNSIGNED PRIMARY KEY,
    bio TEXT,
    favorite_anime TEXT,
    favorite_game TEXT,
    avatar_url VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=COMPACT DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    // 表创建成功
} else {
    // 如果表创建失败，输出错误信息
    echo "错误: " . $conn->error;
}

// 创建公告表（如果不存在）
$sql = "CREATE TABLE IF NOT EXISTS announcements (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB ROW_FORMAT=COMPACT DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    // 表创建成功
} else {
    // 如果表创建失败，输出错误信息
    echo "错误: " . $conn->error;
}

// 创建密码历史表（如果不存在）
$sql = "CREATE TABLE IF NOT EXISTS password_history (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    password VARCHAR(255) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=COMPACT DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    // 表创建成功
} else {
    // 如果表创建失败，输出错误信息
    echo "错误: " . $conn->error;
}

// 创建角色偏好表（如果不存在）
$sql = "CREATE TABLE IF NOT EXISTS character_preferences (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    character_name VARCHAR(100) NOT NULL,
    anime_name VARCHAR(100) NOT NULL,
    preference_type ENUM('favorite', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=COMPACT DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    // 表创建成功
} else {
    // 如果表创建失败，输出错误信息
    echo "错误: " . $conn->error;
}

// 创建管理员账号（如果不存在）
$admin_username = 'Heyevery';
$admin_password = password_hash('pqtpqt0625', PASSWORD_DEFAULT);

// 检查管理员是否存在
$sql = "SELECT * FROM users WHERE username='".mysqli_real_escape_string($conn, $admin_username)."'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // 管理员不存在，创建一个
    $sql = "INSERT INTO users (username, password, is_admin) VALUES ('".mysqli_real_escape_string($conn, $admin_username)."', '".mysqli_real_escape_string($conn, $admin_password)."', 1)";
    if ($conn->query($sql) === FALSE) {
        echo "错误: " . $conn->error;
    }
} else {
    // 更新现有管理员的密码
    $sql = "UPDATE users SET password='".mysqli_real_escape_string($conn, $admin_password)."' WHERE username='".mysqli_real_escape_string($conn, $admin_username)."'";
    if ($conn->query($sql) === FALSE) {
        echo "错误: " . $conn->error;
    }
}

// 检查并更新users表结构
$sql = "SHOW COLUMNS FROM users LIKE 'is_admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // is_admin字段不存在，添加该列
    $sql = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
    if ($conn->query($sql) === FALSE) {
        echo "错误: " . $conn->error;
    }
}
?>