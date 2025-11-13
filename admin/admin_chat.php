<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");

$conn = new mysqli("localhost", "root", "", "store");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// ======= Cấu hình số ngày giữ tin nhắn =======
$daysToKeep = 1; 
$conn->query("DELETE FROM message WHERE created_at < NOW() - INTERVAL $daysToKeep DAY");

// Quyền admin
$role = $_SESSION['role'] ?? 'admin'; 
$action = $_GET['action'] ?? $_POST['action'] ?? '';

/* === Gửi tin nhắn === */
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

/* === Lấy danh sách user + tin nhắn mới nhất === */
if($action === 'users'){
    $result = $conn->query("
        SELECT DISTINCT user_id, user_name
        FROM message
        WHERE sender_role = 'user'
        ORDER BY user_id ASC
    ");

    $users = [];
    while($row = $result->fetch_assoc()){
        $users[] = [
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'],
            'last_message' => '' 
        ];
    }
    echo json_encode($users);
    exit;
}

/* === Lấy tin nhắn của 1 user === */
if($action === 'fetch'){
    $user_id = intval($_GET['user_id'] ?? 0);
    if(!$user_id) exit("Chưa chọn user!");

    $stmt = $conn->prepare("SELECT sender_role, user_name, content FROM message WHERE user_id=? ORDER BY created_at ASC");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $html = '';
    while($row = $res->fetch_assoc()){
        $content = nl2br(htmlspecialchars($row['content'])); // giữ xuống dòng
        if($row['sender_role']==='user'){
            $html .= '<div class="user-message"><strong>'.htmlspecialchars($row['user_name']).':</strong> '.$content.'</div>';
        } else {
            $html .= '<div class="bot-message"><strong>Bạn:</strong> '.$content.'</div>';
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
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family:'Arial',sans-serif; }
body { display: flex; height: 80vh; max-width: 900px; margin: 20px auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; background: #f0f2f5; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.user-list { width: 30%; background: #fff; border-right: 1px solid #ddd; display: flex; flex-direction: column; overflow-y: auto; }
.user-list h3 { text-align: center; padding: 12px 0; background: #4caf50; color: #fff; font-size: 16px; }
.user-item { padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #eee; transition: background 0.2s; }
.user-item:hover { background: #f1f1f1; }
.user-item.active { background: #e0f7e0; }
.user-item strong { display: block; font-size: 14px; color: #333; }
.user-item span { font-size: 12px; color: #666; margin-top: 3px; }
.chat-box { flex: 1; display: flex; flex-direction: column; }
.chat-header { padding: 12px; background: #fff; border-bottom: 1px solid #ddd; font-weight: bold; color: #333; font-size: 14px; }
.chat-messages { flex: 1; padding: 10px; overflow-y: auto; background: #f9f9f9; display: flex; flex-direction: column; gap: 8px; }
.user-message { background: #e2e2e2; color: #000; text-align: left; align-self: flex-start; border-radius: 12px; padding: 8px 12px; max-width: 70%; word-wrap: break-word; font-size: 13px; }
.bot-message { background: #4caf50; color: #fff; text-align: right; align-self: flex-end; border-radius: 12px; padding: 8px 12px; max-width: 70%; word-wrap: break-word; font-size: 13px; }
.chat-input { display: flex; padding: 10px; border-top: 1px solid #ddd; background: #fff; }
.chat-input input { flex: 1; padding: 8px 10px; border-radius: 20px; border: 1px solid #ccc; outline: none; font-size: 13px; }
.chat-input button { margin-left: 8px; padding: 8px 16px; background: #4caf50; border: none; color: #fff; border-radius: 20px; cursor: pointer; font-size: 13px; transition: background 0.2s; }
.chat-input button:hover { background: #45a049; }
.chat-messages::-webkit-scrollbar { width: 5px; }
.chat-messages::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
</style>
</head>
<body>

<div class="user-list">
    <h3>Người dùng</h3>
    <div id="users"></div>
</div>

<div class="chat-box">
    <div class="chat-header" id="chat-header">Chọn người dùng để chat</div>
    <div class="chat-messages" id="chat-messages"></div>
    <div class="chat-input">
        <input type="text" id="admin-input" placeholder="Nhập tin nhắn...">
        <button id="send-admin">Gửi</button>
    </div>
</div>

<script>
let selectedUserId = null;

// Load danh sách user
function loadUsers(){
    fetch('?action=users')
    .then(res => res.json())
    .then(users => {
        const usersDiv = document.getElementById('users');
        usersDiv.innerHTML='';
        users.forEach(u => {
            const div = document.createElement('div');
            div.className='user-item';
            div.innerHTML = `<strong>${u.user_name}</strong><span>${u.last_message || ''}</span>`;
            div.onclick = function(){
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

// Load tin nhắn
function loadMessages(){
    if(!selectedUserId) return;
    fetch('?action=fetch&user_id='+selectedUserId)
    .then(res=>res.text())
    .then(html=>{
        const chatBox = document.getElementById('chat-messages');
        chatBox.innerHTML = html;
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

// Gửi tin nhắn
function sendMessage(){
    const msg = document.getElementById('admin-input').value.trim();
    if(!msg || !selectedUserId) return;
    const data = new URLSearchParams();
    data.append('message', msg);
    data.append('user_id', selectedUserId);

    fetch('?action=send',{ method:'POST', body:data })
    .then(res=>res.text())
    .then(res=>{
        if(res==='OK'){
            document.getElementById('admin-input').value='';
            loadMessages();
        }else{ alert(res); }
    });
}

document.getElementById('send-admin').addEventListener('click', sendMessage);
document.getElementById('admin-input').addEventListener('keypress', function(e){
    if(e.key==='Enter'){ e.preventDefault(); sendMessage(); }
});

// Auto load
setInterval(loadUsers,5000);
setInterval(loadMessages,2000);
loadUsers();
</script>

</body>
</html>
