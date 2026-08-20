<?php

$keyword = trim($_GET['keyword'] ?? '');

$sql = "SELECT user_code, name, email FROM users";

if ($keyword !== '') {

    $sql .= "
        WHERE user_code LIKE ?
        OR name LIKE ?
        OR email LIKE ?
    ";

    $stmt = $conn->prepare($sql);

    $search = "%$keyword%";

    $stmt->bind_param(
        "sss",
        $search,
        $search,
        $search
    );

} else {

    $stmt = $conn->prepare($sql);

}

$stmt->execute();
$result = $stmt->get_result();

?>