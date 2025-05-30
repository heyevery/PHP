// 新建代码
function assignUserId(username) {
    if (username === 'admin') {
        return '000000';
    }

    // 使用用户名生成固定长度的哈希值
    let hash = 0;
    for (let i = 0; i < username.length; i++) {
        hash = username.charCodeAt(i) + ((hash << 5) - hash);
    }

    // 取绝对值并转换为6位数字符串
    let uniqueId = Math.abs(hash).toString().substring(0, 6);
    
    // 如果不足6位，前面补零
    while (uniqueId.length < 6) {
        uniqueId = '0' + uniqueId;
    }

    return uniqueId;
}

// 示例用法
const users = ['admin', 'user1', 'user2'];
const userWithIds = {};

users.forEach(username => {
    const userId = assignUserId(username);
    userWithIds[username] = userId;
});

console.log(userWithIds); // 输出结果：{ admin: '000000', user1: '随机6位数', user2: '随机6位数' }