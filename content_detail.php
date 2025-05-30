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

// 获取用户信息（如果已登录）
$username = null;
$avatar = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    
    // 获取用户头像
    if ($username !== null) {
        $sql = "SELECT avatar FROM users WHERE username='".$conn->real_escape_string($username)."'";
        $result_user = $conn->query($sql);
        if ($result_user && $result_user->num_rows > 0) {
            $user_data = $result_user->fetch_assoc();
            $avatar = $user_data['avatar'];
        }
    }
}
    
// 获取用户权限信息
if ($username !== null) {
    $sql = "SELECT is_admin FROM users WHERE username='".$conn->real_escape_string($username)."'";
    $result = $conn->query($sql);
}

if ($result && $row = $result->fetch_assoc()) {
    $_SESSION['is_admin'] = $row['is_admin'];
}

// 获取内容ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$content_id = intval($_GET['id']);

// 获取内容详情
$sql = "SELECT * FROM content WHERE id = " . $conn->real_escape_string($content_id);
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    header("Location: index.php");
    exit();
}

$content = $result->fetch_assoc();

// 获取推荐内容
$related_content = [];
$sql = "SELECT * FROM content WHERE type = '".$conn->real_escape_string($content['type'])."' AND id != ".$content_id." ORDER BY RAND() LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $related_content[] = $row;
    }
}

// 获取用户头像信息
$sql = "SELECT avatar FROM users WHERE username='".$conn->real_escape_string($username)."'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $_SESSION['avatar'] = $row['avatar'];
}

// 获取用户权限信息
$sql = "SELECT is_admin FROM users WHERE username='".$conn->real_escape_string($username)."'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $_SESSION['is_admin'] = $row['is_admin'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>文章详情 - ACGFans</title>
    <link rel="stylesheet" href="style.css">
    <script src="navbar.js"></script>
</head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACGFans</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ACGFans</h1>
            <!-- 导航栏 -->
            <nav class="navbar">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link">首页</a></li>
                    <li class="nav-item"><a href="anime.php" class="nav-link">动画</a></li>
                    <li class="nav-item"><a href="comic.php" class="nav-link">漫画</a></li>
                    <li class="nav-item"><a href="game.php" class="nav-link">游戏</a></li>
                    <li class="nav-item"><a href="community.php" class="nav-link">社区</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li class="nav-item"><a href="manage_announcements.php" class="nav-link admin-link">管理公告</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- 用户头像菜单 -->
            <div class="user-menu">
                <?php if (isset($_SESSION['username'])): ?>
                    <img src="uploads/<?php echo isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'default_avatar.png'; ?>" alt="用户头像" onclick="toggleDropdown()">
                    <div class="dropdown-content" id="userDropdown">
                        <p>欢迎, <?php echo $_SESSION['username']; ?></p>
                        <a href="user_profile.php">个人资料</a>
                        <a href="upload.php">上传文章</a>
                        <a href="logout.php">退出登录</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="main-container">
            <div class="main-content">
                <h2><?php echo $content['title']; ?></h2>
                
                <div class="content-detail">
                    <img src="<?php echo $content['image']; ?>" alt="<?php echo $content['title']; ?>" class="content-image">
                    <div class="content-description">
                        <p><?php echo $content['description']; ?></p>
                        <p class="content-date">发布日期：<?php echo date('Y年n月d日', strtotime($content['created_at'])); ?></p>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <p class="content-date">最后更新：<?php echo date('Y年n月d日', strtotime($content['updated_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 推荐内容侧栏 -->
            <div class="sidebar">
                <h3>推荐阅读</h3>
                <?php if (!empty($related_content)): ?>
                    <ul>
                        <?php foreach ($related_content as $item): ?>
                            <li>
                                <div class="sidebar-content">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                                    <a href="content_detail.php?id=<?php echo $item['id']; ?>"><?php echo $item['title']; ?></a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>暂无相关内容</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 ACGFans</p>
        </div>
    </div>
    
    <script>
        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // 点击其他地方关闭下拉菜单
        window.onclick = function(event) {
            if (!event.target.matches('.user-menu img')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>