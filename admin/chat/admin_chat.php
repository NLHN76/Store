<?php
require_once "../../db.php";
require_once "function.php";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hỗ trợ khách hàng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/chat.css">
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



<script src= "chat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
