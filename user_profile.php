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

// 引入外部定义的用户ID分配逻辑
require_once 'user_id_assigner.php';

$userId = assignUserId($username);

// 设置默认头像
$defaultAvatar = 'default_avatar.jpg'; // 默认头像文件名

// 处理头像上传
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查是否有上传的文件或裁剪后的数据
    if (isset($_FILES["avatar"]) || !empty($_POST["avatar_cropped"])) {
        $target_dir = "uploads/";

        // 确保 uploads 目录存在，如果不存在则创建
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (!empty($_POST["avatar_cropped"])) {
            // 处理裁剪后的图片数据
            $imageData = $_POST["avatar_cropped"];
            
            // 移除Data URL前缀
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageData = base64_decode($imageData);
            
            // 生成唯一的文件名
            $fileName = uniqid() . '.png';
            $target_file = $target_dir . $fileName;
            
            // 将图片写入文件
            file_put_contents($target_file, $imageData);
            
            // 更新数据库中的头像路径
            $sql = "UPDATE users SET avatar='".$conn->real_escape_string(basename($fileName))."' WHERE username='".$conn->real_escape_string($username)."'";
            if ($conn->query($sql) === TRUE) {
                header("Location: user_profile.php");
                exit();
            } else {
                $error = "更新头像失败: " . $conn->error;
            }
        } elseif (isset($_FILES["avatar"])) {
            // 处理普通上传的图片
            $target_file = $target_dir . basename($_FILES["avatar"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // 检查文件是否为实际图片
            if (isset($_POST["submit"])) {
                if (!empty($_FILES["avatar"]["tmp_name"])) {
                    $check = getimagesize($_FILES["avatar"]["tmp_name"]);
                    if ($check !== false) {
                        $uploadOk = 1;
                    } else {
                        $error = "文件不是图片。";
                        $uploadOk = 0;
                    }
                } else {
                    $error = "请选择要上传的图片。";
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
                    $sql = "UPDATE users SET avatar='".$conn->real_escape_string(basename($target_file))."' WHERE username='".$conn->real_escape_string($username)."'";
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
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户账号 - ACGFans</title>
    <!-- 引入Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        :root {
            --primary-color: #ff6ec7;
            --secondary-color: #1a1a2e;
            --accent-color: #0f3460;
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

        input[type="file"],
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 0.9em;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="file"]:focus,
        input[type="submit"]:focus {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 8px rgba(255, 110, 199, 0.4);
        }

        input[type="submit"] {
            background: linear-gradient(45deg, var(--primary-color), #ff9cda);
            cursor: pointer;
            letter-spacing: 1px;
        }

        input[type="submit"]:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 110, 199, 0.5);
        }

        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .user-info img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--primary-color);
            transition: transform 0.3s ease;
        }

        .user-info img:hover {
            transform: rotate(360deg) scale(1.1);
        }

        .user-info p {
            margin: 8px 0;
            font-size: 1em;
            color: var(--text-color);
        }

        .action-buttons {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 20px;
            gap: 10px;
        }

        .action-button {
            flex: 1 1 45%;
            min-width: 120px;
            padding: 10px 20px;
            background: linear-gradient(45deg, var(--accent-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.8em;
            letter-spacing: 1px;
        }

        .action-button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(255, 110, 199, 0.4);
        }

        .error {
            color: var(--error-color);
            font-size: 0.8em;
            margin-top: 10px;
            text-align: center;
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

            .user-info img {
                width: 100px;
                height: 100px;
            }

            .action-button {
                flex: 1 1 100%;
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

        /* 新增的小型裁剪模态框样式 */
        .small-crop-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            width: 90%;
            max-width: 300px;
            max-height: 80vh;
            overflow-y: auto;
            display: none;
            flex-direction: column;
            align-items: center;
        }

        .small-crop-modal .modal-header {
            width: 100%;
            text-align: right;
            font-size: 20px;
            color: #666;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .small-crop-modal .crop-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .small-crop-modal .crop-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .small-crop-modal .crop-actions {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .small-crop-modal .btn-action {
            flex: 1;
            padding: 8px 0;
            margin: 0 5px;
            text-align: center;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .small-crop-modal .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }

        .small-crop-modal .btn-crop {
            background: var(--primary-color);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="anime-character">
            <img src="uploads/load.png"; alt="用户头像">
        </div>
        
        <!-- 头像裁剪框（小型弹窗） -->
        <div id="cropModal" class="small-crop-modal">
            <div class="modal-header" id="cropModalClose">&times;</div>
            <div class="crop-container">
                <img id="cropImage" src="">
            </div>
            <div class="crop-actions">
                <div class="btn-action btn-cancel" id="cropModalCancel">取消</div>
                <div class="btn-action btn-crop" id="confirmCrop">确认</div>
            </div>
        </div>
        
        <!-- 用户信息部分 -->
        <div class="user-info">
            <img id="avatarPreview" src="<?php echo isset($user['avatar']) ? 'uploads/'.$user['avatar'] : (file_exists('uploads/'.$defaultAvatar)); ?>" alt="用户头像">
            <p>用户名: <?php echo htmlspecialchars($user['username']); ?></p>
            <p>ID: <?php echo htmlspecialchars($userId); ?></p>
        </div>
        
        <!-- 修改头像按钮 -->
        <div class="form-group">
            <input type="file" id="avatarInput" name="avatar" accept="image/*">
        </div>
        
        <!-- 表单提交和其他按钮 -->
        <form method="post" action="user_profile.php" enctype="multipart/form-data">
            <input type="hidden" name="avatar_cropped" id="avatarCropped">
            <input type="submit" value="上传新头像" name="submit">
        </form>
        
        <div class="action-buttons">
            <a href="change_password.php" class="action-button">修改密码</a>
            <a href="logout.php" class="action-button">退出登录</a>
            <a href="index.php" class="action-button">返回主页面</a>
            <?php if ($_SESSION['is_admin']): ?>
                <a href="manage_announcements.php" class="action-button">管理公告</a>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        // 处理头像选择和裁剪功能
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatarInput');
            const cropModal = document.getElementById('cropModal');
            const cropImage = document.getElementById('cropImage');
            const closeBtn = document.getElementById('cropModalClose');
            const cancelBtn = document.getElementById('cropModalCancel');
            const avatarPreview = document.getElementById('avatarPreview');
            let cropper;
            
            // 当选择图片时
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        cropImage.src = event.target.result;
                        cropModal.style.display = 'flex';
                        
                        // 初始化裁剪器
                        if (cropper) {
                            cropper.destroy();
                        }
                        cropper = new Cropper(cropImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            preview: '.preview'
                        });
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
            
            // 关闭模态框
            closeBtn.onclick = function() {
                cropModal.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                }
            };
            
            // 取消按钮
            cancelBtn.onclick = function() {
                cropModal.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                }
            };
            
            // 确认裁剪
            document.getElementById('confirmCrop').addEventListener('click', function() {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({
                        width: 200,
                        height: 200
                    });
                    if (canvas) {
                        canvas.toBlob(function(blob) {
                            // 将裁剪后的图片显示在预览中
                            const reader = new FileReader();
                            reader.onload = function() {
                                avatarPreview.src = reader.result;
                                // 将裁剪后的图片数据放入隐藏输入域
                                document.getElementById('avatarCropped').value = reader.result;
                                // 提交表单
                                document.querySelector('form').submit();
                            };
                            reader.readAsDataURL(blob);
                            
                            // 关闭模态框
                            cropModal.style.display = 'none';
                        }, 'image/png');
                    }
                }
            });
            
            // 点击模态框外部关闭
            window.onclick = function(event) {
                if (event.target == cropModal) {
                    cropModal.style.display = 'none';
                    if (cropper) {
                        cropper.destroy();
                    }
                }
            };
        });
    </script>
</body>
</html>