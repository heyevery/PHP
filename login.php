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

// 获取公告信息
$announcements = [];
$sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

// 检查用户是否登录
if (isset($_SESSION['username'])) {
    // 用户已登录，重定向到主页
    header("Location: index.php");
    exit();
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 特别处理管理员账号
    if ($username === 'Heyevery' && $password === 'pqtpqt0625') {
        $_SESSION['username'] = $username;
        // 设置管理员标志
        $_SESSION['is_admin'] = true;
        
        // 将管理员登录信息存储到数据库（如果不存在）
        $sql = "SELECT * FROM users WHERE username='".$conn->real_escape_string($username)."'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 0) {
            // 管理员账号不存在，创建一个
            $sql = "INSERT INTO users (username, password, is_admin) VALUES ('".$conn->real_escape_string($username)."', '".$conn->real_escape_string(password_hash('pqtpqt0625', PASSWORD_DEFAULT))."', 1)";
            $conn->query($sql);
        } else {
            // 更新现有管理员账号的密码
            $sql = "UPDATE users SET password='".$conn->real_escape_string(password_hash('pqtpqt0625', PASSWORD_DEFAULT))."' WHERE username='".$conn->real_escape_string($username)."'";
            $conn->query($sql);
        }
        
        header("Location: index.php");
        exit();
    }

    // 普通用户登录检查
    $sql = "SELECT * FROM users WHERE username='".$conn->real_escape_string($username)."' AND password='".$conn->real_escape_string($password)."'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['username'] = $username;
        $user = $result->fetch_assoc();
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: index.php");
        exit();
    } else {
        $error = "用户名或密码错误。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - ACGFans</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6ec7;
            --secondary-color: #1a1a2e;
            --accent-color: #ce113c;
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
            max-width: 400px;
            overflow: hidden;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .anime-character {
            position: relative;
            overflow: hidden;
            height: 200px;
        }

        .anime-character img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .anime-character:hover img {
            transform: scale(1.1);
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

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8em;
            color: var(--text-color);
            letter-spacing: 1px;
        }

        input[type="text"],
        input[type="password"] {
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

        input[type="text"]:focus,
        input[type="password"]:focus {
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

        .error {
            color: var(--error-color);
            font-size: 0.8em;
            margin-top: 10px;
            text-align: center;
        }

        .link {
            text-align: center;
            margin-top: 20px;
        }

        .link a {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 0.8em;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .link a:hover {
            color: var(--primary-color);
            text-shadow: 0 0 5px rgba(255, 110, 199, 0.5);
        }

        .footer {
            text-align: center;
            font-size: 0.7em;
            color: #aaa;
            margin-top: 20px;
            letter-spacing: 1px;
        }

        @media (max-width: 500px) {
            .container {
                max-width: 90%;
            }

            .anime-character {
                height: 150px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="anime-character">
        <img src="uploads/load.png" alt="ACG角色">
    </div>
    <div class="content">
        <h2>欢迎回来！</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="submit" value="登录">
        </form>
        <div class="link">
            <p>没有账号？<a href="register.php">立即注册</a></p>
        </div>
        <div class="footer">
            <p>© 2025 ACGFans</p>
        </div>
    </div>
</div>
</body>
</html>