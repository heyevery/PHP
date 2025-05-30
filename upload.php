<?php
// upload.php
global $conn;
session_start();
include 'db_connect.php';

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 获取用户权限信息
$username = $_SESSION['username'];
$sql = "SELECT is_admin FROM users WHERE username='".$conn->real_escape_string($username)."'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $is_admin = $row['is_admin'];
} else {
    $is_admin = 0;
}

// 处理上传逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $content = $_POST['content'];
    
    // 处理图片上传
    $uploadedImages = [];

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = 'uploads/';
        $totalFiles = count($_FILES['images']['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            $imagePath = $uploadDir . basename($_FILES['images']['name'][$i]);

            // 检查文件是否已经存在
            if (file_exists($imagePath)) {
                $error = "抱歉，文件 " . basename($_FILES['images']['name'][$i]) . " 已存在。";
                break;
            } elseif ($_FILES['images']['size'][$i] > 500000) { // 限制文件大小为500KB
                $error = "抱歉，您的文件 " . basename($_FILES['images']['name'][$i]) . " 太大。";
                break;
            } else {
                if (!move_uploaded_file($_FILES['images']['tmp_name'][$i], $imagePath)) {
                    $error = "图片 " . basename($_FILES['images']['name'][$i]) . " 上传失败";
                    break;
                } else {
                    $uploadedImages[] = $imagePath;
                }
            }
        }
    } else {
        $uploadedImages[] = 'default_image.jpg';
    }
    
    // 验证数据
    if (empty($title) || empty($type) || empty($content)) {
        $error = "所有字段都是必填的";
    } elseif (isset($error)) {
        // 已有错误（如图片上传失败），不继续执行
    } else {
        // 插入数据库
        $stmt = $conn->prepare("INSERT INTO content (title, type, description, image, url) VALUES (?, ?, ?, ?, 'content_detail.php?id=0')");
        
        if ($stmt === false) {
            die('MySQL 准备语句失败: ' . htmlspecialchars($conn->error));
        }
        
        $imagePaths = implode(',', $uploadedImages); // 将多个图片路径用逗号分隔存储
        $stmt->bind_param("ssss", $title, $type, $content, $imagePaths);
        
        if (!$stmt->execute()) {
            $error = "插入数据失败: " . htmlspecialchars($stmt->error);
        } else {
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上传文章 - ACGFans</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>上传文章</h2>
        
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <label for="title">文章标题:</label>
            <input type="text" id="title" name="title" required><br>

            <label for="type">文章类型:</label>
            <select id="type" name="type" required>
                <option value="anime">动漫</option>
                <option value="comic">漫画</option>
                <option value="game">游戏</option>
            </select><br>

            <label for="content">文章内容:</label><br>
            <textarea id="content" name="content" rows="10" cols="50" required></textarea><br>

            <label for="images">选择图片:</label>
            <input type="file" id="images" name="images[]" accept="image/*" multiple><br>

            <input type="submit" value="上传文章">

        </form>
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="text-decoration: none; color: white; background-color: #ff6ec7; padding: 10px 20px; border-radius: 5px;">返回主页</a>
        </div>
    </div>
</body>
</html>