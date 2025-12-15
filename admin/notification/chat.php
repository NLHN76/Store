<?php
require_once "../db.php"; // sửa đường dẫn nếu cần

function countNewChat($conn) {
    $sql = "
        SELECT COUNT(DISTINCT m.user_id) AS cnt
        FROM message m
        LEFT JOIN user_last_seen_message u ON m.user_id = u.user_id
        WHERE m.sender_role='user' AND m.id > COALESCE(u.last_seen_id,0)
    ";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return intval($row['cnt'] ?? 0);
}

// Lấy số user có tin nhắn mới
$new_chat_count = countNewChat($conn);

?>