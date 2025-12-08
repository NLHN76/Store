<?php
require_once "../../db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);



// --- Xử lý AJAX cập nhật trạng thái ---
if(isset($_POST['action']) && $_POST['action']=="update_status"){
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE payment SET status=? WHERE id=?");
    $stmt->bind_param("si",$new_status,$order_id);
    echo $stmt->execute() ? "success" : $conn->error;
    exit;
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
if(isset($_POST['export_html_id'])){
    $order_id = filter_input(INPUT_POST, 'export_html_id', FILTER_VALIDATE_INT);
    if ($order_id) {
        $stmt = $conn->prepare("SELECT * FROM payment WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            ob_start();
            include 'invoice_template.php'; // tách HTML hóa đơn vào file riêng
            $html_content = ob_get_clean();
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=hoa_don_" . $order_id . ".html");
            echo $html_content;
        }
        exit;
    }
}

// --- Lấy danh sách đơn hàng (cho AJAX loadOrders) ---
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
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

// Nếu gọi AJAX loadOrders, trả về tbody HTML
if(isset($_GET['ajax_load']) && $_GET['ajax_load']==1){
    ob_start();
    foreach($result as $row){
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
                    <?php foreach($all_statuses as $s){
                        $sel=($row['status']==$s)?"selected":""; 
                        echo "<option value=\"$s\" $sel>$s</option>";
                    } ?>
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
             <form method="POST" action="admin_orders_backend.php" style="display:inline;">
                 <input type="hidden" name="export_html_id" value="<?=$row['id']?>">
                <button type="submit">Xuất hóa đơn</button>
              </form>

            </td>
        </tr>
        <?php
    }
    echo ob_get_clean();
    exit;
}

$conn->close();
?>
