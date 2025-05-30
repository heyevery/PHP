<?php
// comic.php
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

// 检查并创建公告表（如果不存在）
function checkAndCreateAnnouncementsTable($conn) {
    // 检查表是否存在
    $table_exists = $conn->query("SHOW TABLES LIKE 'announcements'");
    
    // 如果表不存在，则创建它
    if ($table_exists === FALSE) {
        die('检查公告表存在性失败: ' . $conn->error);
    }
    
    if ($table_exists->num_rows == 0) {
        // 定义create_table_sql变量
        $create_table_sql = "CREATE TABLE IF NOT EXISTS announcements (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL DEFAULT '',
            content VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
        if ($conn->query($create_table_sql) === TRUE) {
            // 表创建成功后插入示例数据
            $insert_sql = "INSERT INTO announcements (title, content) VALUES 
                ('欢迎来到ACGFans漫画板块', '这是漫画板块公告，您可以从管理面板修改。', NOW())";
                
            if ($conn->query($insert_sql) === FALSE) {
                die('插入示例数据失败: ' . $conn->error);
            }
        } else {
            die('创建公告表失败: ' . $conn->error);
        }
    } else {
        // 表存在，但可能需要检查结构
        $result = $conn->query("DESCRIBE announcements");
        if (!$result) {
            die('无法描述公告表结构: ' . $conn->error);
        }
        
        // 检查是否有必要的字段
        $has_title = false;
        $has_content = false;
        $has_created_at = false;
            
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] == 'title') $has_title = true;
            if ($row['Field'] == 'content') $has_content = true;
            if ($row['Field'] == 'created_at') $has_created_at = true;
        }
            
        // 如果缺少必要字段或字段类型不匹配，删除表并重新创建
        if (!$has_title || !$has_content || !$has_created_at) {
            $drop_sql = "DROP TABLE IF EXISTS announcements";
            if ($conn->query($drop_sql) === TRUE) {
                // 重新创建表
                $create_table_sql = "CREATE TABLE IF NOT EXISTS announcements (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL DEFAULT '',
                    content VARCHAR(255) NOT NULL DEFAULT '',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                    
                if ($conn->query($create_table_sql) === TRUE) {
                    // 插入示例数据
                    $insert_sql = "INSERT INTO announcements (title, content) VALUES 
                        ('欢迎来到ACGFans漫画板块', '这是漫画板块公告，您可以从管理面板修改。', NOW())";
                    $conn->query($insert_sql);
                } else {
                    die('重建公告表失败: ' . $conn->error);
                }
            } else {
                die('删除无效公告表失败: ' . $conn->error);
            }
        }
    }
}

// 调用函数检查并创建表
checkAndCreateAnnouncementsTable($conn);

// 获取公告信息
$announcements = [];
$sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $announcements[] = $row;
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

// 获取推荐内容
$recommended_content = [];
$sql = "SELECT * FROM content WHERE type = 'comic' ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recommended_content[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漫画板块 - ACGfans</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ACGfans</h1>
            
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
                        <a href="logout.php">退出登录</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="main-container">
            <div class="main-content">
                <h2>漫画板块</h2>
                <p>这里是漫画相关内容。</p>
                
                <ul class="content-list">
                    <?php foreach ($recommended_content as $item): ?>
                        <li>
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                            <div class="content-info">
                                <h3><?php echo $item['title']; ?></h3>
                                <p><?php echo $item['description']; ?></p>
                                <a href="content_detail.php?id=<?php echo $item['id']; ?>" class="read-more">查看详情</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- 右侧边栏移到这里 -->
            <div class="sidebar">
                <h3>推荐阅读</h3>
                <?php if (!empty($recommended_content)): ?>
                    <ul>
                        <?php foreach ($recommended_content as $item): ?>
                            <li>
                                <div class="sidebar-content">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                                    <a href="content_detail.php?id=<?php echo $item['id']; ?>">详情</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>暂无相关内容</p>
                <?php endif; ?>
                
                <h3>公告栏</h3>
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