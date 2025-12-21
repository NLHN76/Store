<?php
require_once "config.php";

if (!$user_id) {
    exit("Chưa đăng nhập!");
}

$stmt = $conn->prepare("
    SELECT sender_role, content, created_at
    FROM message
    WHERE user_id = ?
    ORDER BY created_at ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$html = '';
while ($row = $result->fetch_assoc()) {

    $content = nl2br(htmlspecialchars($row['content']));
    $time = date('H:i', strtotime($row['created_at']));

    if ($row['sender_role'] === 'user') {
        $html .= '
        <div class="user-message">
            <strong>Bạn:</strong> ' . $content . '
            <div class="msg-time">' . $time . '</div>
        </div>';
    } else {
        $html .= '
        <div class="admin-message">
            <strong>Admin:</strong> ' . $content . '
            <div class="msg-time">' . $time . '</div>
        </div>';
    }
}

echo $html;
$conn->close();
