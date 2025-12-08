<?php
require_once "db.php";

// ===== Xóa tin nhắn cũ sau n ngày =====
$daysToKeep = 1;
$conn->query("DELETE FROM message WHERE created_at < NOW() - INTERVAL $daysToKeep DAY");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'Khách';
$admin_id = 1; // admin cố định

/* === Gửi tin nhắn === */
if($action==='send'){
    $message = trim($_POST['message'] ?? '');
    if(!$message) exit("Tin nhắn trống!");
    if(!$user_id) exit("Chưa đăng nhập!");

    $stmt = $conn->prepare("INSERT INTO message (user_id, user_name, sender_role, content) VALUES (?, ?, 'user', ?)");
    $stmt->bind_param("iss", $user_id, $user_name, $message);
    $stmt->execute();
    $stmt->close();
    exit("OK");
}

/* === Lấy tin nhắn với admin === */
if($action==='fetch'){
    if(!$user_id) exit("Chưa đăng nhập!");
    $stmt = $conn->prepare("
        SELECT sender_role, content 
        FROM message 
        WHERE user_id = ? 
           OR (sender_role='admin' AND user_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $html = '';
    while($row = $res->fetch_assoc()){
        $content = nl2br(htmlspecialchars($row['content']));
        if($row['sender_role']==='user'){
            $html .= '<div class="user-message"><strong>Bạn:</strong> '.$content.'</div>';
        } else {
            $html .= '<div class="bot-message"><strong>Admin:</strong> '.$content.'</div>';
        }
    }
    echo $html;
    exit;
}

$conn->close();
?>
