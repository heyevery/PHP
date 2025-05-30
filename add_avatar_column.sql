-- 在users表中添加avatar字段
ALTER TABLE users
ADD COLUMN avatar VARCHAR(255) DEFAULT 'default_avatar.png';