<?php

require_once "db.php";


$user_id=intval($_GET['user_id']);


$sql="
SELECT 
    email,
    phone

FROM user_profile

WHERE user_id=?
";


$stmt=$conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result=$stmt->get_result();


echo json_encode(
    $result->fetch_assoc()
);

?>