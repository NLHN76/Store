<?php
header("Content-Type: text/html; charset=UTF-8");


$daysToKeep = 1; 
$conn->query("DELETE FROM message WHERE created_at < NOW() - INTERVAL $daysToKeep DAY");

$role = $_SESSION['role'] ?? 'admin'; 
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if($action === 'send'){
    $message = trim($_POST['message'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);
    if(!$message || !$user_id) exit("Tin nhắn trống hoặc chưa chọn user!");
    $admin_name = $_SESSION['admin_name'] ?? 'Admin';
    $stmt = $conn->prepare("INSERT INTO message (user_id, user_name, sender_role, content) VALUES (?, ?, 'admin', ?)");
    $stmt->bind_param("iss",$user_id,$admin_name,$message);
    $stmt->execute();
    $stmt->close();
    exit("OK");
}

if($action === 'users'){
    $result = $conn->query("SELECT DISTINCT user_id, user_name FROM message WHERE sender_role = 'user' ORDER BY user_id ASC");
    $users = [];
    while($row = $result->fetch_assoc()){
        $users[] = ['user_id'=>$row['user_id'],'user_name'=>$row['user_name'],'last_message'=>''];
    }
    echo json_encode($users);
    exit;
}

if($action === 'fetch'){
    $user_id = intval($_GET['user_id'] ?? 0);
    if(!$user_id) exit("Chưa chọn user!");
    $stmt = $conn->prepare("SELECT sender_role, user_name, content FROM message WHERE user_id=? ORDER BY created_at ASC");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $html = '';
    while($row = $res->fetch_assoc()){
        $content = nl2br(htmlspecialchars($row['content']));
        if($row['sender_role']==='user'){
            $html .= '<div class="alert alert-light text-start p-2 mb-2 rounded"><strong>'.htmlspecialchars($row['user_name']).':</strong> '.$content.'</div>';
        } else {
            $html .= '<div class="alert alert-success text-end p-2 mb-2 rounded"><strong>Bạn:</strong> '.$content.'</div>';
        }
    }
    echo $html;
    exit;
}

$conn->close();
?>