<?php

require_once "../db.php"; // sửa đường dẫn nếu cần

// Lấy ID liên hệ lớn nhất đã xem
$last_seen_id = isset($_SESSION['last_seen_contact_id']) ? $_SESSION['last_seen_contact_id'] : 0;

// Lấy số liên hệ mới
$new_count = 0;
if ($conn) {
    $sql_new = "SELECT COUNT(*) AS new_count FROM contact WHERE id > ?";
    $stmt_new = $conn->prepare($sql_new);
    if ($stmt_new) {
        $stmt_new->bind_param("i", $last_seen_id);
        $stmt_new->execute();
        $result_new = $stmt_new->get_result();
        if ($row = $result_new->fetch_assoc()) {
            $new_count = $row['new_count'];
        }
        $stmt_new->close();
    }
}


?>