<?php
global $conn;
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    header("Location: login.php");
    exit();
}

// 处理头像上传
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["avatar"])) {
    $target_dir = "uploads/";
    
    // 确保 uploads 目录存在，如果不存在则创建
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target_file = $target_dir . basename($_FILES["avatar"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // 检查文件是否为实际图片
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["avatar"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "文件不是图片。";
            $uploadOk = 0;
        }
    }

    // 检查文件大小
    if ($_FILES["avatar"]["size"] > 500000) {
        $error = "对不起，您的文件太大。";
        $uploadOk = 0;
    }

    // 允许的文件格式
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $error = "对不起，只允许 JPG, JPEG, PNG & GIF 文件。";
        $uploadOk = 0;
    }

    // 检查 $uploadOk 是否被设置为 0，如果是则中断上传
    if ($uploadOk == 0) {
        $error = "对不起，您的文件未上传。";
    } else {
        // 如果一切就绪，尝试上传文件
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            // 更新数据库中的头像路径
            $sql = "UPDATE users SET avatar='$target_file' WHERE username='$username'";
            if ($conn->query($sql) === TRUE) {
                header("Location: user_profile.php");
                exit();
            } else {
                $error = "更新头像失败: " . $conn->error;
            }
        } else {
            $error = "对不起，上传文件时出错。";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户账号</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .user-info {
            margin-bottom: 20px;
        }
        .user-info img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .user-info p {
            margin: 5px 0;
            font-size: 16px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .action-buttons a {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .action-buttons a:hover {
            background-color: #45a049;
        }
        /* 响应式设计 */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .user-info img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>用户账号</h2>
        <div class="user-info">
            <img src="<?php echo $user['avatar']; ?>" alt="用户头像">
            <p>用户名: <?php echo $user['username']; ?></p>
        </div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <input type="file" name="avatar" id="avatar" accept="image/*">
            <input type="submit" value="上传头像" name="submit">
        </form>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <div class="action-buttons">
            <a href="change_password.php">修改密码</a>
            <a href="logout.php">退出登录</a>
        </div>
    </div>
</body>
</html>