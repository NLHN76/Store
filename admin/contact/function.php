<?php

require_once "../../db.php"; 

// ----------------- 1. XÓA TỰ ĐỘNG CÁC LIÊN HỆ CŨ -----------------
$days_to_keep = 1; 
$delete_old_sql = "DELETE FROM contact WHERE created_at < NOW() - INTERVAL ? DAY";
$stmt_delete_old = $conn->prepare($delete_old_sql);
$stmt_delete_old->bind_param("i", $days_to_keep);
$stmt_delete_old->execute();
$stmt_delete_old->close();

// ----------------- LẤY DANH SÁCH LIÊN HỆ -----------------
$search_query = '';
$sql = "SELECT * FROM contact";

// Nếu có tìm kiếm
if (isset($_POST['search']) && !empty($_POST['search_query'])) {
    $search_query = trim($_POST['search_query']);
    $sql .= " WHERE id LIKE ? OR name LIKE ? OR phone LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_query = "%$search_query%";
    $stmt->bind_param("sss", $like_query, $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Không tìm kiếm, lấy tất cả liên hệ
    $sql .= " ORDER BY id DESC";
    $result = $conn->query($sql);
}

// ----------------- LẤY CÁC LIÊN HỆ MỚI -----------------
$new_ids = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['is_new'] == 1) {
            $new_ids[] = $row['id'];
        }
    }
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
