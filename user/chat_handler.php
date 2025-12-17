<?php

require_once "../db.php";

/* ================== CẤU HÌNH ================== */
date_default_timezone_set('Asia/Ho_Chi_Minh');
$daysToKeep = 1;

/* ================== DỌN TIN NHẮN CŨ ================== */
$conn->query("
    DELETE FROM message 
    WHERE created_at < NOW() - INTERVAL $daysToKeep DAY
");

/* ================== LẤY ACTION ================== */
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$user_id   = $_SESSION['user_id']   ?? 0;
$user_name = $_SESSION['user_name'] ?? 'Khách';

/* ================== GỬI TIN NHẮN ================== */
if ($action === 'send') {

    if (!$user_id) {
        exit("Chưa đăng nhập!");
    }

    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        exit("Tin nhắn trống!");
    }

    $stmt = $conn->prepare("
        INSERT INTO message (user_id, user_name, sender_role, content)
        VALUES (?, ?, 'user', ?)
    ");
    $stmt->bind_param("iss", $user_id, $user_name, $message);
    $stmt->execute();
    $stmt->close();

    exit("OK");
}

/* ================== FETCH TIN NHẮN ================== */
if ($action === 'fetch') {

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
    exit;
}

/* ================== KẾT THÚC ================== */
$conn->close();
