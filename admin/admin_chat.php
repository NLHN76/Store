<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");

$conn = new mysqli("localhost", "root", "", "store");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

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

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Chat - Mobile Gear</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f0f2f5; height:100vh; display:flex; justify-content:center; align-items:center; }
.chat-container { width:95%; max-width:1000px; height:80vh; display:flex; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.user-list { width:30%; border-end:1px solid #dee2e6; background:#fff; display:flex; flex-direction:column; }
.user-list h5 { background:#198754; color:#fff; padding:12px; text-align:center; margin:0; }
.user-item { cursor:pointer; padding:10px 12px; border-bottom:1px solid #eee; transition:background 0.2s; }
.user-item.active { background:#e2f0e8; }
.chat-box { flex:1; display:flex; flex-direction:column; background:#f8f9fa; }
.chat-header { padding:12px; background:#fff; border-bottom:1px solid #dee2e6; font-weight:bold; font-size:16px; }
.chat-messages { flex:1; padding:12px; overflow-y:auto; display:flex; flex-direction:column; gap:6px; }
.chat-input { padding:10px; border-top:1px solid #dee2e6; background:#fff; }
.chat-input .form-control { border-radius:50px; }
.chat-input button { border-radius:50px; }
</style>
</head>
<body>

<div class="chat-container shadow-sm">
    <div class="user-list">
        <h5>Người dùng</h5>
        <div id="users" class="flex-grow-1 overflow-auto"></div>
    </div>
    <div class="chat-box d-flex flex-column">
        <div class="chat-header" id="chat-header">Chọn người dùng để chat</div>
        <div class="chat-messages" id="chat-messages"></div>
        <div class="chat-input d-flex">
            <input type="text" id="admin-input" class="form-control me-2" placeholder="Nhập tin nhắn...">
            <button id="send-admin" class="btn btn-success">Gửi</button>
        </div>
    </div>
</div>

<script>
let selectedUserId = null;

function loadUsers(){
    fetch('?action=users').then(res=>res.json()).then(users=>{
        const usersDiv = document.getElementById('users');
        usersDiv.innerHTML='';
        users.forEach(u=>{
            const div = document.createElement('div');
            div.className='user-item';
            div.innerHTML = `<strong>${u.user_name}</strong>`;
            div.onclick = ()=>{
                selectedUserId = u.user_id;
                document.getElementById('chat-header').textContent = u.user_name;
                document.querySelectorAll('.user-item').forEach(d=>d.classList.remove('active'));
                div.classList.add('active');
                loadMessages();
            };
            usersDiv.appendChild(div);
        });
    });
}

function loadMessages(){
    if(!selectedUserId) return;
    fetch('?action=fetch&user_id='+selectedUserId).then(res=>res.text()).then(html=>{
        const chatBox = document.getElementById('chat-messages');
        chatBox.innerHTML = html;
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

function sendMessage(){
    const msg = document.getElementById('admin-input').value.trim();
    if(!msg || !selectedUserId) return;
    const data = new URLSearchParams();
    data.append('message', msg);
    data.append('user_id', selectedUserId);
    fetch('?action=send',{ method:'POST', body:data }).then(res=>res.text()).then(res=>{
        if(res==='OK'){ document.getElementById('admin-input').value=''; loadMessages(); }
        else alert(res);
    });
}

document.getElementById('send-admin').addEventListener('click', sendMessage);
document.getElementById('admin-input').addEventListener('keypress', e=>{ if(e.key==='Enter'){ e.preventDefault(); sendMessage(); } });

setInterval(loadUsers,5000);
setInterval(loadMessages,2000);
loadUsers();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
