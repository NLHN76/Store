<?php

require_once "../../db.php";


?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản Lý Đơn Hàng</title>
<a href="../admin_interface.php" class="btn-back">
  <img src="../uploads/exit.jpg" alt="Quay lại">
  <span>Quay lại</span>
</a>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link rel="stylesheet" href="css/online.css">
<script>
function deleteOrder(id){
    if(confirm("Bạn có chắc chắn muốn xóa đơn #" + id + "?")){
        $.post("admin_orders_backend.php", {delete_id:id}, function(){
            loadOrders();
        });
    }
}

function updateStatus(order_id, select){
    var new_status = select.value;
    $.post("admin_orders_backend.php", {action:"update_status", order_id:order_id, new_status:new_status}, function(data){
        if(data=="success") loadOrders();
        else alert("Lỗi: "+data);
    });
}

function loadOrders(){
    $.get("admin_orders_backend.php?ajax_load=1&keyword="+encodeURIComponent($("input[name=keyword]").val()), function(html){
        $("#ordersTableBody").html(html);
    });
}

$(document).ready(function(){
    loadOrders(); // load lần đầu
    setInterval(loadOrders, 5000); // tự động refresh
});
</script>
</head>
<body>



<h1>Quản Lý Đơn Hàng</h1>


<form method="GET" style="margin-bottom:10px;">
    <input type="text" name="keyword" placeholder="Tìm theo Mã đơn">
    <button type="submit" onclick="loadOrders(); return false;">Tìm kiếm</button>
</form>

<table>
<thead>
<tr>
<th>Mã đơn</th><th>Ngày Đặt</th><th>Tên KH</th><th>Email</th><th>SĐT</th><th>Địa Chỉ</th>
<th>Mã SP</th><th>Sản Phẩm</th><th>Loại SP</th><th>Màu Sắc</th><th>Số Lượng</th><th>Tổng Tiền</th>
<th>Mã KH</th><th>Trạng Thái</th><th>Shipper</th><th>Hành Động</th>
</tr>
</thead>
<tbody id="ordersTableBody">
<tr><td colspan="16" style="text-align:center;">Đang tải...</td></tr>
</tbody>
</table>


</body>
</html>
