<?php
header("Content-Type: text/html; charset=UTF-8");

require_once "../../db.php";

// ================== CẤU HÌNH ==================
date_default_timezone_set('Asia/Ho_Chi_Minh');
$daysToKeep = 1;

// ================== XÓA TIN NHẮN CŨ ==================
$conn->query("
    DELETE FROM message 
    WHERE created_at < NOW() - INTERVAL $daysToKeep DAY
");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ================== GỬI TIN NHẮN ==================
if ($action === 'send') {
    $message = trim($_POST['message'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);

    if (!$message || !$user_id) {
        exit("Tin nhắn trống hoặc thiếu user!");
    }

    $admin_name = $_SESSION['admin_name'] ?? 'Admin';

    $stmt = $conn->prepare("
        INSERT INTO message (user_id, user_name, sender_role, content)
        VALUES (?, ?, 'admin', ?)
    ");
    $stmt->bind_param("iss", $user_id, $admin_name, $message);
    $stmt->execute();
    $stmt->close();

    exit("OK");
}

// ================== LẤY DANH SÁCH USER ==================
if ($action === 'users') {
    $res = $conn->query("
        SELECT DISTINCT user_id, user_name
        FROM message
        WHERE sender_role = 'user'
        ORDER BY user_id ASC
    ");

    $users = [];
    while ($row = $res->fetch_assoc()) {
        $users[] = [
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name']
        ];
    }

    echo json_encode($users);
    exit;
}

// ================== FETCH TIN NHẮN ==================
if ($action === 'fetch') {
    $user_id = intval($_GET['user_id'] ?? 0);
    if (!$user_id) exit("Chưa chọn user!");

    $stmt = $conn->prepare("
        SELECT id, sender_role, user_name, content, created_at
        FROM message
        WHERE user_id = ?
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $html = '';
    $last_id = 0;

    while ($row = $res->fetch_assoc()) {
        $content = nl2br(htmlspecialchars($row['content']));
        $time = date('H:i', strtotime($row['created_at']));
        $last_id = max($last_id, $row['id']);

        if ($row['sender_role'] === 'user') {
            $html .= '
            <div class="alert alert-light text-start p-2 mb-2 rounded">
                <strong>' . htmlspecialchars($row['user_name']) . ':</strong> ' . $content . '
                <div class="msg-time text-start">' . $time . '</div>
            </div>';
        } else {
            $html .= '
            <div class="alert alert-success text-end p-2 mb-2 rounded">
                <strong>Bạn:</strong> ' . $content . '
                <div class="msg-time text-end">' . $time . '</div>
            </div>';
        }
    }
    $stmt->close();

    // ===== CẬP NHẬT LAST SEEN =====
    if ($last_id > 0) {
        $stmt2 = $conn->prepare("
            INSERT INTO user_last_seen_message (user_id, last_seen_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE last_seen_id = VALUES(last_seen_id)
        ");
        $stmt2->bind_param("ii", $user_id, $last_id);
        $stmt2->execute();
        $stmt2->close();
    }

    echo $html;
    exit;
}

// ================== CHECK TIN NHẮN MỚI ==================
if ($action === 'check_new') {
    $user_id = intval($_GET['user_id'] ?? 0);
    if (!$user_id) {
        echo json_encode(['new' => 0]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM message m
        LEFT JOIN user_last_seen_message u ON m.user_id = u.user_id
        WHERE m.user_id = ?
          AND m.id > COALESCE(u.last_seen_id, 0)
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'new' => ($row['cnt'] ?? 0) > 0 ? 1 : 0
    ]);
    exit;
}

$conn->close();
