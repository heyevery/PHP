<?php
function assignUserId($username) {
    if ($username === 'Heyevery') {
        return '000000';
    }

    // 使用用户名生成固定ID
    $hash = 0;
    for ($i = 0; $i < strlen($username); $i++) {
        $hash = ord($username[$i]) + (($hash << 5) - $hash);
    }

    // 取绝对值并转换为6位数字符串
    $uniqueId = abs($hash) % 1000000;
    $uniqueId = str_pad($uniqueId, 6, '0', STR_PAD_LEFT);

    return $uniqueId;
}
?>