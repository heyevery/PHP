// 创建导航栏HTML
var navbarHTML = `
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">ACGFans</a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">首页</a></li>
            <li><a href="anime.php">动漫</a></li>
            <li><a href="comic.php">漫画</a></li>
            <li><a href="game.php">游戏</a></li>
            <li><a href="community.php">社区</a></li>
            <li><a href="user_profile.php">个人中心</a></li>
            <li><a href="login.php">登录</a></li>
            <li><a href="register.php">注册</a></li>
            <li><a href="logout.php">退出</a></li>
        </ul>
    </nav>
`;

// 插入导航栏到页面
var navbarContainer = document.getElementById('navbar');
if (navbarContainer) {
    navbarContainer.innerHTML = navbarHTML;
}