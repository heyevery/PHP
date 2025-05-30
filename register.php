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

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // 验证密码一致性
    if ($password != $confirm_password) {
        $error = "两次输入的密码不一致。";
    } elseif ($username === 'Heyevery') {
        $error = "该用户名受保护，不能用于注册。";
    } else {
        // 检查用户名是否已存在
        $sql = "SELECT * FROM users WHERE username='".$conn->real_escape_string($username)."'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error = "用户名已存在。";
        } else {
            // 插入新用户
            $sql = "INSERT INTO users (username, password) VALUES ('".$conn->real_escape_string($username)."', '".$conn->real_escape_string($password)."')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "注册失败: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - ACGFans</title>
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

        .password-strength-meter {
            margin-top: 10px;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .strength-text {
            font-size: 0.7em;
            margin-top: 5px;
            text-align: center;
            color: var(--text-color);
        }

        .low {
            background-color: #ff2e63;
        }

        .medium {
            background-color: #ff9c00;
        }

        .high {
            background-color: #00d26a;
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
        <h2>加入我们！</h2>
        <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码:</label>
                <input type="password" id="new_password" name="password" required>
                <div id="password-strength" class="password-strength-meter">
                    <div id="strength-bar" class="strength-bar"></div>
                    <div id="strength-text" class="strength-text"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">确认密码:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <input type="submit" value="注册">
        </form>
        <div class="link">
            <p>已有账号？<a href="login.php">立即登录</a></p>
        </div>
        <div class="footer">
            <p>© 2025 ACGFans</p>
        </div>
    </div>
</div>
<script>
    document.getElementById('new_password').addEventListener('input', function() {
        var password = this.value;
        var strengthBar = document.getElementById('strength-bar');
        var strengthText = document.getElementById('strength-text');

        var strength = 0;

        // 密码强度检测逻辑
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;

        // 更新进度条和文本
        if (strength <= 1) {
            strengthBar.style.width = '20%';
            strengthBar.className = 'strength-bar low';
            strengthText.textContent = '低';
            strengthText.className = 'strength-text low';
        } else if (strength == 2) {
            strengthBar.style.width = '40%';
            strengthBar.className = 'strength-bar medium';
            strengthText.textContent = '中';
            strengthText.className = 'strength-text medium';
        } else if (strength == 3) {
            strengthBar.style.width = '60%';
            strengthBar.className = 'strength-bar medium';
            strengthText.textContent = '中';
            strengthText.className = 'strength-text medium';
        } else if (strength == 4) {
            strengthBar.style.width = '80%';
            strengthBar.className = 'strength-bar high';
            strengthText.textContent = '高';
            strengthText.className = 'strength-text high';
        } else if (strength == 5) {
            strengthBar.style.width = '100%';
            strengthBar.className = 'strength-bar high';
            strengthText.textContent = '极高';
            strengthText.className = 'strength-text high';
        }
    });
</script>
</body>
</html>