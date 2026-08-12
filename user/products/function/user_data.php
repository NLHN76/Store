<?php
// ====== Xử lý người dùng ======
$is_logged_in = isset($_SESSION['user_id']);
$user_name = 'Khách';
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

if ($is_logged_in) {
    $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $res = $stmt_user->get_result();
    if ($res->num_rows) $user_name = $res->fetch_assoc()['name'];
    $stmt_user->close();
}
?>