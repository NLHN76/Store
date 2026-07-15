<?php
require_once "../../db.php";
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
