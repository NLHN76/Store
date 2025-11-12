<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Lỗi kết nối: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// --- Xử lý AJAX cập nhật trạng thái ---
if(isset($_POST['action'])){
    $action = $_POST['action'];

    if($action=="update_status"){
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['new_status'];
        $stmt = $conn->prepare("UPDATE payment SET status=? WHERE id=?");
        $stmt->bind_param("si",$new_status,$order_id);
        echo $stmt->execute() ? "success" : $conn->error;
        exit;
    }
}

// --- Xử lý xóa đơn hàng ---
if(isset($_POST['delete_id'])){
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM payment WHERE id=?");
    $stmt->bind_param("i",$delete_id);
    $stmt->execute();
    exit;
}

// --- Xử lý xuất hóa đơn HTML ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_html_id'])) {
    $order_id = filter_input(INPUT_POST, 'export_html_id', FILTER_VALIDATE_INT);
    if ($order_id) {
        $stmt = $conn->prepare("SELECT * FROM payment WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                ob_start();
                ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hóa Đơn - Mã <?= htmlspecialchars($row["id"]) ?></title>
<style>
body { font-family: 'DejaVu Sans', Arial, sans-serif; line-height: 1.5; margin: 0; padding: 15px; font-size: 11px; background-color: #fff; color: #333; }
.invoice-box { width: 100%; max-width: 700px; margin: auto; padding: 20px; border: 1px solid #eee; box-shadow: 0 0 8px rgba(0,0,0,0.1); background-color: #fff; }
h1 { text-align: center; color: #222; font-size: 1.8em; margin-bottom: 15px; text-transform: uppercase; }
.label { font-weight: bold; display: inline-block; min-width: 120px; }
.line { border-top: 1px dashed #ccc; margin: 15px 0; }
.total-section p { font-size: 1.1em; font-weight: bold; text-align:right; }
.company-name { font-size: 1.4em; font-weight: bold; text-align: center; margin-bottom: 5px; }
.footer { text-align: center; margin-top: 25px; font-size: 0.95em; color: #666; border-top: 1px solid #eee; padding-top: 15px; }
</style>
</head>
<body>
<div class='invoice-box'>
<div class='contact-info'>
<p class='company-name'>MOBILE GEAR</p>
<p style="text-align: center;">Địa chỉ: Số 254 Tây Sơn - P. Trung Liệt - Q. Đống Đa - TP. Hà Nội</p>
<p style="text-align: center;">Điện thoại: 0587.911.287 | Email: mobilegear@gmail.com</p>
</div>
<h1>Hóa Đơn Thanh Toán</h1>
<div class='invoice-details'>
<p><span class='label'>Mã Hóa Đơn:</span> <?= htmlspecialchars($row["id"]) ?></p>
<p><span class='label'>Ngày Đặt Hàng:</span> <?= date('d/m/Y H:i', strtotime($row["order_date"])) ?></p>
<p><span class='label'>Xuất Hóa Đơn Lúc:</span> <?= date('d/m/Y H:i') ?></p>
</div>
<div class='customer-details'>
<p><span class='label'>Khách Hàng:</span> <?= htmlspecialchars($row["customer_name"]) ?></p>
<p><span class='label'>Mã Khách Hàng:</span> <?= htmlspecialchars($row["user_code"]) ?: 'N/A' ?></p>
<p><span class='label'>Email:</span> <?= htmlspecialchars($row["customer_email"]) ?></p>
<p><span class='label'>Điện Thoại:</span> <?= htmlspecialchars($row["customer_phone"]) ?></p>
<p><span class='label'>Địa Chỉ Giao Hàng:</span> <?= htmlspecialchars($row["customer_address"]) ?></p>
</div>
<div class='product-details'>
<p><span class='label'>Mã Sản Phẩm:</span> <?= htmlspecialchars($row["product_code"]) ?></p>
<p><span class='label'>Sản Phẩm:</span> <?= htmlspecialchars($row["product_name"]) ?></p>
<p><span class='label'>Loại Sản Phẩm:</span> <?= htmlspecialchars($row["category"]) ?></p>
<p><span class='label'>Màu Sắc:</span> <?= htmlspecialchars($row["color"] ?: '-') ?></p>
<p><span class='label'>Số Lượng:</span> <?= htmlspecialchars($row["product_quantity"]) ?></p>
</div>
<div class='line'></div>
<div class='total-section'>
<p><span class='label'>Tổng Tiền Thanh Toán:</span> <?= number_format($row["total_price"], 0, ',', '.') ?> VNĐ</p>
</div>
<div class='footer'>
<p>Xin chân thành cảm ơn Quý khách đã tin tưởng và mua hàng!</p>
</div>
</div>
</body>
</html>
<?php
                $html_content = ob_get_clean();
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=hoa_don_" . $order_id . ".html");
                echo $html_content;
                exit;
            }
            $stmt->close();
        }
    }
}

// --- Tìm kiếm ---
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// --- Lấy danh sách đơn hàng ---
$sql = "SELECT p.*, s.name AS shipper_name, s.email AS shipper_email, s.phone AS shipper_phone
        FROM payment p
        LEFT JOIN shipper s ON p.shipper_id = s.id";

if($keyword!==''){
    if(ctype_digit($keyword)){
        $sql .= " WHERE p.id=".intval($keyword);
    } else $sql .= " WHERE 0";
}

$sql .= " ORDER BY p.order_date DESC";
$result = $conn->query($sql);

// --- Định nghĩa màu sắc trạng thái ---
$status_colors = [
    "Chờ xử lý"=>"#f8f9fa", 
    "Chờ thanh toán"=>"#fff3cd",
    "Đã thanh toán"=>"#d1ecf1",
    "Đang xử lý"=>"#fff3cd",
    "Đang giao hàng"=>"#cce5ff",
    "Đã giao hàng"=>"#d6d8d9",
    "Đã hủy"=>"#f8d7da"
];
$all_statuses = array_keys($status_colors);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<a class="back-button" href="admin_interface.php" title="Quay lại trang quản trị">
  <img src="uploads/exit.jpg" alt="Quay lại" style="width:30px; height:50px; object-fit:cover; border-radius:5px;">
</a>
<title>Quản Lý Đơn Hàng</title>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
table{width:100%;border-collapse:collapse; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1);}
th,td{border:1px solid #ccc;padding:8px;text-align:left; font-size:14px;}
th{background:#007BFF; color:#fff; text-align:center;}
input, select, button{padding:5px; margin:2px;}
.status-select{width:100%;}
button{cursor:pointer;}
</style>
<script>
function deleteOrder(id){
    if(confirm("Bạn có chắc chắn muốn xóa đơn #" + id + "?")){
        $.post("", {delete_id:id}, function(){
            loadOrders();
        });
    }
}

function loadOrders(){
    $.get("", function(html){
        var tbody = $(html).find("#ordersTableBody").html();
        $("#ordersTableBody").html(tbody);
    });
}

function updateStatus(order_id, select){
    var new_status = select.value;
    $.post("", {action:"update_status", order_id:order_id, new_status:new_status}, function(data){
        if(data=="success") loadOrders();
        else alert("Lỗi: "+data);
    });
}

$(document).ready(function(){
    setInterval(loadOrders, 5000);
});
</script>
</head>
<body>
<h1>Quản Lý Đơn Hàng</h1>
<form method="GET" style="margin-bottom:10px;">
<input type="text" name="keyword" placeholder="Tìm theo Mã đơn" value="<?=htmlspecialchars($keyword)?>">
<button type="submit">Tìm kiếm</button>
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
<?php if($result && $result->num_rows>0):
while($row=$result->fetch_assoc()):
    $bgcolor = $status_colors[$row['status']] ?? '#fff';
?>
<tr style="background-color:<?=$bgcolor?>">
<td><?=$row['id']?></td>
<td><?=date('d/m/Y H:i',strtotime($row['order_date']))?></td>
<td><?=htmlspecialchars($row['customer_name'])?></td>
<td><?=htmlspecialchars($row['customer_email'])?></td>
<td><?=htmlspecialchars($row['customer_phone'])?></td>
<td><?=htmlspecialchars($row['customer_address'])?></td>
<td><?=htmlspecialchars($row['product_code'])?></td>
<td><?=htmlspecialchars($row['product_name'])?></td>
<td><?=htmlspecialchars($row['category'])?></td>
<td><?=htmlspecialchars($row['color'] ?: '-')?></td>
<td><?=$row['product_quantity']?></td>
<td><?=number_format($row['total_price'],0,",",".")?></td>
<td><?=htmlspecialchars($row['user_code']?:'-')?></td>
<td>
<select onchange="updateStatus(<?=$row['id']?>,this)" class="status-select">
<?php
foreach($all_statuses as $s){
    $sel=($row['status']==$s)?"selected":""; 
    echo "<option value=\"$s\" $sel>$s</option>";
}
?>
</select>
</td>
<td>
<?php if(!empty($row['shipper_id'])): ?>
<p><strong><?= htmlspecialchars($row['shipper_name']) ?></strong></p>
<p>Email: <?= htmlspecialchars($row['shipper_email']) ?></p>
<p>SĐT: <?= htmlspecialchars($row['shipper_phone']) ?></p>
<?php else: echo "Chưa nhận"; endif;?>
</td>
<td>
<button onclick="deleteOrder(<?=$row['id']?>)">Xóa</button>
<form method="POST" style="display:inline;">
<input type="hidden" name="export_html_id" value="<?=$row['id']?>">
<button type="submit">Xuất hóa đơn</button>
</form>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="16" style="text-align:center;">Không có đơn hàng nào</td></tr>
<?php endif;?>
</tbody>
</table>
</body>
</html>
<?php $conn->close(); ?>
