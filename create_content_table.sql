-- 创建内容表（如果不存在）
CREATE TABLE IF NOT EXISTS content (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, -- 类型：anime, comic, game, community
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入示例数据
INSERT INTO content (type, title, description, image, url) VALUES
('anime', '进击的巨人 最终季', '《进击的巨人》最终季正式上线，揭开巨人世界真相。', 'R-C.jpg', 'content_detail.php?id=1'),
('anime', '咒术回战 2期', '最强咒术师虎杖再次归来，宿傩之战全面升级！', 'QQ图片20220716205253.jpg', 'content_detail.php?id=2'),
('anime', '间谍过家家 Season3', '阿尼亚家族最新冒险，全新任务等你解锁！', 'background.jpg', 'content_detail.php?id=3'),
('comic', '鬼灭之刃 游郭篇', '炭治郎深入花街，揭开堕姬的秘密！', 'QQ图片20220716205253.jpg', 'content_detail.php?id=4'),
('comic', '我的英雄学院 7期', '英雄辈出的时代，绿谷出久继续成长之旅！', 'R-C.jpg', 'content_detail.php?id=5'),
('comic', 'ONE PIECE 105话', '草帽一伙新篇章开启，路飞挑战新海域！', 'background.jpg', 'content_detail.php?id=6'),
('game', '原神 3.3版本', '新角色、新地图、新剧情全面更新！', 'QQ图片20220716205253.jpg', 'content_detail.php?id=7'),
('game', '崩坏：星穹铁道', '银河列车即将启程，踏上星际冒险之旅！', 'R-C.jpg', 'content_detail.php?id=8'),
('game', '鸣潮 3.0版本', '全新地区「无光之域」开放，沉浸式探索体验！', 'background.jpg', 'content_detail.php?id=9'),
('community', 'ACG音乐节 2025', '与千万ACG爱好者共赴夏日音乐盛宴！', 'QQ图片20220716205253.jpg', 'content_detail.php?id=10'),
('community', '同人创作大赛', '发挥你的创意，赢取万元奖金及官方出版机会！', 'R-C.jpg', 'content_detail.php?id=11'),
('community', '线下漫展邀请', '加入我们的线下漫展，与偶像画师面对面交流！', 'background.jpg', 'content_detail.php?id=12');