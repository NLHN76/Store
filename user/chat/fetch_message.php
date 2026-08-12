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
?>

<?php while ($row = $result->fetch_assoc()): ?>

    <?php
        $content = nl2br(htmlspecialchars($row['content']));
        $time = date('H:i', strtotime($row['created_at']));
    ?>

    <?php if ($row['sender_role'] === 'user'): ?>
        <div class="user-message">
            <strong>Bạn:</strong> <?= $content ?>
            <div class="msg-time"><?= $time ?></div>
        </div>
    <?php else: ?>
        <div class="admin-message">
            <strong>Admin:</strong> <?= $content ?>
            <div class="msg-time"><?= $time ?></div>
        </div>
    <?php endif; ?>

<?php endwhile; ?>

<?php
$stmt->close();
$conn->close();
?>
