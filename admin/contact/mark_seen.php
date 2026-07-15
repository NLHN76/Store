<?php
require_once "../../db.php";

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE contact SET is_new = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo 'ok';
    }
    $stmt->close();
}
$conn->close();
?>
