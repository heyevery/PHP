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

// 获取公告信息
$announcements = [];
$sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

// 获取推荐内容
$recommended_content = [];
$types = ['anime', 'comic', 'game', 'community'];
foreach ($types as $type) {
    $sql = "SELECT * FROM content WHERE type = '".$conn->real_escape_string($type)."' ORDER BY created_at DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $recommended_content[] = $row;
        }
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

// 关闭数据库连接
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
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
                <h2>关于ACG文化</h2>
                <p>ACG是动画（Animation）、漫画（Comic）和游戏（Game）的缩写，代表了一种流行的文化现象，尤其在亚洲地区广受欢迎。这种文化不仅限于作品本身，还包括相关的周边产品、同人创作、漫展活动等丰富多彩的内容。</p>
                
                <p>从手冢治虫的《铁臂阿童木》到宫崎骏的吉卜力工作室作品，从《周刊少年Jump》的热血漫画到电子游戏界的《最终幻想》，ACG文化不断发展创新，成为全球范围内数亿爱好者共同的精神家园。</p>
                
                <p>随着互联网的发展，ACG文化已经突破地域限制，形成了全球性的交流网络。无论是在日本的秋叶原、中国的上海CCG Expo，还是在美国的Anime Central，都能看到ACG爱好者的热情与创造力。</p>
                
                <div class="features home-features">
                    <div class="feature-card">
                        <h3><a href="anime.php" class="feature-button">动画</a></h3>
                        <p>探索经典与现代动画作品，了解动漫产业的最新动态和发展趋势。</p>
                    </div>
                    <div class="feature-card">
                        <h3><a href="comic.php" class="feature-button">漫画</a></h3>
                        <p>发现新晋漫画家和他们的创新作品，参与同人创作和讨论。</p>
                    </div>
                    <div class="feature-card">
                        <h3><a href="game.php" class="feature-button">游戏</a></h3>
                        <p>体验最新游戏资讯，分享游戏心得，结识游戏好友。</p>
                    </div>
                    <div class="feature-card">
                        <h3><a href="community.php" class="feature-button">社区</a></h3>
                        <p>加入我们的ACG社区，与其他爱好者交流互动，分享你的创意和想法。</p>
                    </div>
                </div>
            </div>
            
            <!-- 右侧边栏移到这里 -->
            <div class="sidebar">
                <h3>管理员の通知</h3>
                <?php if (!empty($announcements)): ?>
                    <ul>
                        <?php foreach ($announcements as $announcement): ?>
                            <li><?php echo date('Y年n月d日', strtotime($announcement['created_at'])) . '：' . $announcement['title']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>暂时没有公告呀喵</p>
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