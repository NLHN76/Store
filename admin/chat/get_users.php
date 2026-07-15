<?php
require_once "../../db.php";
$res = $conn->query("
    SELECT DISTINCT user_id, user_name
    FROM message
    WHERE sender_role = 'user'
    ORDER BY user_id ASC
");

$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = [
        'user_id'   => $row['user_id'],
        'user_name' => $row['user_name']
    ];
}

echo json_encode($users);
exit;
