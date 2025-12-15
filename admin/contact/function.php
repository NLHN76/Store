<?php

// Xử lý xóa dữ liệu trước (để load lại danh sách)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $delete_sql = "DELETE FROM contact WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa thành công!'); window.location.href = '';</script>";
        exit;
    } else {
        echo "<script>alert('Xóa không thành công.');</script>";
    }
}

// Xử lý tìm kiếm
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);

    $sql = "SELECT id, user_id, name, email, phone, message, created_at 
            FROM contact 
            WHERE name LIKE ? 
               OR email LIKE ?
               OR phone LIKE ? 
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("sss", $like_query, $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    // Truy vấn mặc định lấy toàn bộ dữ liệu
    $sql = "SELECT id, user_id, name, email, phone, message, created_at 
            FROM contact 
            ORDER BY id DESC";

    $result = $conn->query($sql);
}
?>