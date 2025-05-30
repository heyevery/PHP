<?php
session_start();
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
    header("Location: login.php");
    exit();
}

// 检查密码历史
$show_password_fields = true;

// 处理密码修改
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 验证当前密码（如果是普通用户）
    if (!$user['is_admin']) {
        if ($current_password !== $user['password']) {
            $error = "当前密码错误。";
            $show_password_fields = false;
        }
    }

    // 验证新密码
    if (empty($new_password)) {
        $error = "请输入新密码。";
        $show_password_fields = false;
    } elseif ($new_password !== $confirm_password) {
        $error = "两次输入的新密码不一致。";
        $show_password_fields = false;
    } elseif (strlen($new_password) < 8) {
        $error = "密码必须至少包含8个字符。";
        $show_password_fields = false;
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "密码必须至少包含一个大写字母。";
        $show_password_fields = false;
    } elseif (!preg_match('/\d/', $new_password)) {
        $error = "密码必须至少包含一个数字。";
        $show_password_fields = false;
    } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
        $error = "密码必须至少包含一个特殊字符。";
        $show_password_fields = false;
    }

    // 如果没有错误，则更新密码
    if (!isset($error)) {
        // 更新用户密码
        $sql = "UPDATE users SET password='".$conn->real_escape_string($new_password)."' WHERE username='".$conn->real_escape_string($username)."'";
        
        if ($conn->query($sql) === TRUE) {
            $success = "密码已成功更改！";
            
            // 将旧密码添加到历史记录（如果是普通用户）
            if (!$user['is_admin']) {
                $sql = "INSERT INTO password_history (user_id, password) VALUES (".$user['id'].", '".$conn->real_escape_string(password_hash($current_password, PASSWORD_DEFAULT))."')";
                $conn->query($sql);
            }
            
            // 清除密码字段的值
            $current_password = '';
            $new_password = '';
            $confirm_password = '';
        } else {
            $error = "密码更改失败: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改密码 - ACGFans</title>
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
            font-weight: bold;
            color: var(--primary-color);
        }
        
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid var(--primary-color);
            border-radius: 4px;
            font-size: 14px;
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            transition: border-color 0.3s ease;
        }
        
        input[type="password"]:focus {
            border-color: #ad1457;
            outline: none;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        input[type="submit"]:hover {
            background-color: #ad1457;
        }
        
        .error {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
        }
        
        .link {
            text-align: center;
            margin-top: 20px;
        }
        
        .link a {
            color: var(--error-color);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        
        .link a:hover {
            text-decoration: underline;
            color: #ad1457;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="anime-character">
            <img src="uploads/load.png" alt="ACG角色">
        </div>
        <div class="content">
            <h2>修改密码</h2>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="current_password">当前密码:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">新密码:</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <div id="password-strength" class="password-strength-meter">
                        <div id="strength-bar" class="strength-bar"></div>
                        <div id="strength-text" class="strength-text"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认新密码:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <input type="submit" value="修改密码" name="change_password">
            </form>
            <div class="link">
                <p><a href="user_profile.php">返回用户账号</a></p>
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