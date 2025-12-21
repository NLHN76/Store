<?php
if (!isset($conn)) {
    require_once "../../db.php";
}

// ================== CẤU HÌNH ==================
date_default_timezone_set('Asia/Ho_Chi_Minh');
$daysToKeep = 1;

// ================== DỌN TIN NHẮN CŨ ==================
$conn->query("
    DELETE FROM message 
    WHERE created_at < NOW() - INTERVAL $daysToKeep DAY
");
