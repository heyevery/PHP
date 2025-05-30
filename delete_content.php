<?php
// delete_content.php
session_start();
include 'db_connect.php';

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
global $conn;
// 获取用户权限信息
$username = $_SESSION['username'];
$sql = "SELECT is_admin FROM users WHERE username='".$conn->real_escape_string($username)."'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $is_admin = $row['is_admin'];
} else {
    $is_admin = 0;
}

// 只有管理员可以删除内容
if (!$is_admin) {
    die("您没有权限执行此操作。");
}

// 清空内容表
$sql = "DELETE FROM content";
if ($conn->query($sql) === TRUE) {
    echo "内容表已成功清空！";
} else {
    echo "错误：" . $conn->error;
}
?>