<?php
require_once "../../db.php";

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

$last_id = 0;
?>

<?php while ($row = $res->fetch_assoc()): ?>

    <?php
        $content = nl2br(htmlspecialchars($row['content']));
        $time    = date('H:i', strtotime($row['created_at']));
        $last_id = max($last_id, $row['id']);
    ?>

    <?php if ($row['sender_role'] === 'user'): ?>
        <div class="alert alert-light text-start p-2 mb-2 rounded">
            <strong><?= htmlspecialchars($row['user_name']) ?>:</strong>
            <?= $content ?>
            <div class="msg-time text-start"><?= $time ?></div>
        </div>
    <?php else: ?>
        <div class="alert alert-success text-end p-2 mb-2 rounded">
            <strong>Bạn:</strong>
            <?= $content ?>
            <div class="msg-time text-end"><?= $time ?></div>
        </div>
    <?php endif; ?>

<?php endwhile; ?>

<?php
$stmt->close();

// ===== UPDATE LAST SEEN =====
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

$conn->close();
exit;
?>
