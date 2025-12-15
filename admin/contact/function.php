<?php

require_once "../../db.php"; // Kết nối DB

// ----------------- 1. XÓA TỰ ĐỘNG CÁC LIÊN HỆ CŨ -----------------
$days_to_keep = 1; // giữ lại liên hệ trong 1 ngày
$delete_old_sql = "DELETE FROM contact WHERE created_at < NOW() - INTERVAL ? DAY";
$stmt_delete_old = $conn->prepare($delete_old_sql);
$stmt_delete_old->bind_param("i", $days_to_keep);
$stmt_delete_old->execute();
$stmt_delete_old->close();

// ----------------- 2. LẤY CÁC LIÊN HỆ MỚI -----------------
$last_seen_id = isset($_SESSION['last_seen_contact_id']) ? $_SESSION['last_seen_contact_id'] : 0;

$sql_new = "SELECT id FROM contact WHERE id > ? ORDER BY id ASC";
$stmt_new = $conn->prepare($sql_new);
$stmt_new->bind_param("i", $last_seen_id);
$stmt_new->execute();
$result_new = $stmt_new->get_result();

$new_ids = [];
while ($row = $result_new->fetch_assoc()) {
    $new_ids[] = $row['id'];
}
$new_count = count($new_ids);

// Cập nhật last_seen_id sau khi load xong
if (!empty($new_ids)) {
    $_SESSION['last_seen_contact_id'] = max($new_ids);
}

// ----------------- 3. XÓA THỦ CÔNG -----------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = intval($_POST['id']); // đảm bảo là số nguyên
    $delete_sql = "DELETE FROM contact WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa thành công!'); window.location.href='';</script>";
        exit;
    } else {
        echo "<script>alert('Xóa không thành công.');</script>";
    }
}

// ----------------- 4. TÌM KIẾM LIÊN HỆ  -----------------
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);

    if (is_numeric($search_query)) {
        // Nếu là số => tìm theo id hoặc phone
        $sql = "SELECT id, user_id, name, email, phone, message, created_at 
                FROM contact 
                WHERE id = ? OR phone LIKE ?
                ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $like_query = "%$search_query%";
        $stmt->bind_param("is", $search_query, $like_query);
    } else {
        // Nếu là chữ => tìm theo name hoặc email
        $sql = "SELECT id, user_id, name, email, phone, message, created_at 
                FROM contact 
                WHERE name LIKE ? OR email LIKE ?
                ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $like_query = "%$search_query%";
        $stmt->bind_param("ss", $like_query, $like_query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT id, user_id, name, email, phone, message, created_at 
            FROM contact 
            ORDER BY id DESC";
    $result = $conn->query($sql);
}


?>
