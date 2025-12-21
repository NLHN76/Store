<?php

require_once "../../db.php";

/* ================== CẤU HÌNH ================== */
date_default_timezone_set('Asia/Ho_Chi_Minh');
$daysToKeep = 1;

/* ================== DỌN TIN NHẮN CŨ ================== */
$conn->query("
    DELETE FROM message 
    WHERE created_at < NOW() - INTERVAL $daysToKeep DAY
");

/* ================== THÔNG TIN USER ================== */
$user_id   = $_SESSION['user_id']   ?? 0;
$user_name = $_SESSION['user_name'] ?? 'Khách';
