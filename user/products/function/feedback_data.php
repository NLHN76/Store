<?php
// ====== Xử lý đánh giá (gửi/xóa) ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_logged_in) {
        echo "<script>alert('Vui lòng đăng nhập để thực hiện thao tác.');</script>";
    } else {
        if (isset($_POST['delete_feedback'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $delete_id, $user_id);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Đánh giá đã được xóa!'); window.location.href=window.location.href;</script>";
            exit;
        } elseif (isset($_POST['rating'])) {
            $rating = intval($_POST['rating']);
            $message = trim($_POST['message']);
            $stmt = $conn->prepare("INSERT INTO feedback (product_code, user_id, rating, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siis", $product['product_code'], $user_id, $rating, $message);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Cảm ơn bạn đã gửi đánh giá!'); window.location.href=window.location.href;</script>";
            exit;
        }
    }
}

// ====== Lấy danh sách đánh giá ======
// ====== Phân trang đánh giá ======
$limit = 6; // Số đánh giá mỗi trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Tổng số đánh giá
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM feedback WHERE product_code = ?");
$stmt_count->bind_param("s", $product['product_code']);
$stmt_count->execute();
$total_feedback = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_feedback / $limit);

// Lấy đánh giá cho từng trang
$stmt_fb = $conn->prepare("
    SELECT f.*, u.name AS user_name
    FROM feedback f 
    JOIN users u ON f.user_id = u.id
    WHERE f.product_code = ?
    ORDER BY f.created_at DESC
    LIMIT ?, ?
");
$stmt_fb->bind_param("sii", $product['product_code'], $offset, $limit);
$stmt_fb->execute();
$feedbacks = $stmt_fb->get_result();
$stmt_fb->close();


?>