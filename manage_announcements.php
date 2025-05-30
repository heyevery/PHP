<?php
// 开始会话
session_start();

// 包含数据库连接
include 'db_connect.php';

// 确保$conn变量存在
if (!isset($conn) || !is_object($conn)) {
    // 如果$conn不存在或不是对象，尝试重新连接
    global $conn;
    $conn = new mysqli("localhost", "root", "root", "my_project");
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
}

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 获取用户信息
$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username='".$conn->real_escape_string($username)."'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    // 用户不存在，重定向到登录页面
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// 检查用户是否是管理员
if (!$user['is_admin']) {
    // 如果不是管理员但尝试访问管理页面，则重定向到主页
    if (basename($_SERVER['PHP_SELF']) === 'manage_announcements.php') {
        header("Location: index.php");
        exit();
    }
}

// 处理添加公告
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_announcement'])) {
    $title = $_POST['title'];
    
    if (empty($title)) {
        $error = "请输入公告内容：";
    } else {
        $sql = "INSERT INTO announcements (title, content) VALUES ('" . $conn->real_escape_string($title) . "', '" . $conn->real_escape_string($title) . "')";
        if ($conn->query($sql) === TRUE) {
            $success = "公告添加成功！";
            // 重新获取公告
            $announcements = [];
            $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $announcements[] = $row;
                }
            }
        } else {
            $error = "公告添加失败: " . $conn->error;
        }
    }
}

// 处理删除公告
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_announcement'])) {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM announcements WHERE id=" . $conn->real_escape_string($id);
    if ($conn->query($sql) === TRUE) {
        $success = "公告删除成功！";
        // 重新获取公告
        $announcements = [];
        $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $announcements[] = $row;
            }
        }
    } else {
        $error = "公告删除失败: " . $conn->error;
    }
}

// 获取所有公告
$announcements = [];
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

// 获取用户权限信息（在处理完所有数据库操作后）
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT is_admin FROM users WHERE username='".$conn->real_escape_string($username)."'";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['is_admin'] = $row['is_admin'];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理公告 - ACGFans</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6ec7;
            --secondary-color: #1a1a2e;
            --accent-color: #bd0d0d;
            --text-color: #e0e0e0;
            --bg-color: #16213e;
            --error-color: #ff2e63;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Press Start 2P', cursive, Arial, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(255, 110, 199, 0.2);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .content {
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 1.2em;
            letter-spacing: 2px;
        }

        form {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8em;
            color: var(--text-color);
            letter-spacing: 1px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 0.9em;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 8px rgba(255, 110, 199, 0.4);
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, var(--primary-color), #ff9cda);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 0.9em;
            cursor: pointer;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        input[type="submit"]:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 110, 199, 0.5);
        }

        .announcements {
            list-style-type: none;
            padding: 0;
        }

        .announcements li {
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .delete-btn {
            background: var(--error-color);
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: #ff4a7d;
        }

        .messages {
            margin-bottom: 20px;
        }

        .error {
            color: var(--error-color);
            font-size: 0.8em;
            text-align: center;
        }

        .success {
            color: var(--accent-color);
            font-size: 0.8em;
            text-align: center;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 0.8em;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--primary-color);
            text-shadow: 0 0 5px rgba(255, 110, 199, 0.5);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <h2>管理公告</h2>

        <?php if (!empty($error)): ?>
            <div class="messages">
                <p class="error"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="messages">
                <p class="success"><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="manage_announcements.php">
            <div class="form-group">
                <label for="title">新公告内容:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <input type="submit" name="add_announcement" value="添加公告">
        </form>

        <?php if (!empty($announcements)): ?>
            <ul class="announcements">
                <?php foreach ($announcements as $announcement): ?>
                    <li>
                        <span><?php echo date('Y年n月d日', strtotime($announcement['created_at'])) . '：' . $announcement['title']; ?></span>
                        <form method="post" action="manage_announcements.php">
                            <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                            <input type="submit" name="delete_announcement" value="删除" class="delete-btn">
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>暂无公告</p>
        <?php endif; ?>

        <div class="back-link">
            <p><a href="index.php">返回首页</a></p>
        </div>
    </div>
</div>
</body>
</html>