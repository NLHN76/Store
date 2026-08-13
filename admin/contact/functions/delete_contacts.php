<?php

require_once "db.php";

$id = intval($_POST['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM contact WHERE id = ?");
$stmt->bind_param("i", $id);

echo $stmt->execute() ? "ok" : "error";

$stmt->close();

?>