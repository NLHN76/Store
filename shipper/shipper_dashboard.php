<?php
session_start();
if(!isset($_SESSION['shipper_id'])){
    header("Location: shipper_login.php");
    exit;
}

$shipper_id = $_SESSION['shipper_id'];
$shipper_name = $_SESSION['shipper_name'];

$conn = new mysqli("localhost","root","","store");
if($conn->connect_error) die("Kết nối thất bại: ".$conn->connect_error);

// Lấy thông tin shipper đăng nhập
$avatar_result = $conn->query("SELECT * FROM shipper WHERE id=$shipper_id");
$avatar_row = $avatar_result->fetch_assoc();
$avatar_login = $avatar_row['avatar'] ?? 'https://via.placeholder.com/40';

// Xử lý AJAX
if(isset($_POST['action'])){
    $action = $_POST['action'];

    if($action=="receive_order"){
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare("
            UPDATE payment 
            SET shipper_id=?, receive_date=NOW(), status='Đang giao hàng'
            WHERE id=? AND shipper_id IS NULL AND status='Đang xử lý'
        ");
        $stmt->bind_param("ii",$shipper_id,$order_id);
        $stmt->execute();
        echo $stmt->affected_rows>0?"success":"fail";
        exit;
    }

    if($action=="update_status"){
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['new_status'];
        $check = $conn->query("SELECT status, shipper_id FROM payment WHERE id=$order_id")->fetch_assoc();
        if($check['shipper_id']==$shipper_id){
            $valid_transitions = [
                'Đang xử lý'=>['Đang xử lý','Đang giao hàng','Đã giao hàng'],
                'Đang giao hàng'=>['Đang giao hàng','Đã giao hàng']
            ];
            if(isset($valid_transitions[$check['status']]) && in_array($new_status,$valid_transitions[$check['status']])){
                $stmt = $conn->prepare("UPDATE payment SET status=? WHERE id=?");
                $stmt->bind_param("si",$new_status,$order_id);
                $stmt->execute();
                echo "success";
            } else echo "fail";
        } else echo "fail";
        exit;
    }

    if($action=="update_shipper_info"){
        $id = intval($_POST['shipper_id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? '';
        $dob = $_POST['dob'] ?? NULL;
        $cmt = $_POST['cmt'] ?? '';
        $password = $_POST['password'] ?? '';

        $avatar_path = '';
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error']==0){
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatar_path = "uploads/shipper_".$id.".".$ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
        }

        $fields = "name=?, email=?, phone=?, dob=?, cmt=?";
        $types = "sssss";
        $params = [$name,$email,$phone,$dob,$cmt];

        if(!empty($password)){
            $fields.=", password=?";
            $types.="s";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        if($avatar_path){
            $fields.=", avatar=?";
            $types.="s";
            $params[] = $avatar_path;
        }

        $stmt = $conn->prepare("UPDATE shipper SET $fields WHERE id=?");
        $types.="i";
        $params[] = $id;
        $stmt->bind_param($types, ...$params);
        echo $stmt->execute()?"success":$conn->error;
        exit;
    }
}

// Lấy danh sách đơn hàng kèm thông tin shipper
$sql = "SELECT p.*, s.name AS shipper_name, s.avatar AS shipper_avatar, s.email AS shipper_email, s.phone AS shipper_phone 
        FROM payment p
        LEFT JOIN shipper s ON p.shipper_id = s.id
        ORDER BY p.order_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Shipper Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.avatar-login { width:40px; height:40px; border-radius:50%; object-fit:cover; cursor:pointer; }
.avatar-order { width:30px; height:30px; border-radius:50%; object-fit:cover; }
.table td, .table th { vertical-align: middle; }
.status-dangxuly { background-color: #fff3cd; }
.status-danggiaohang { background-color: #cce5ff; }
.status-dagiao { background-color: #d6d8d9; }
.color-box { width:30px; height:20px; display:inline-block; border:1px solid #ccc; vertical-align:middle; margin-left:5px; }
.collapse-row { background-color:#f8f9fa; }
</style>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Shipper Dashboard</h3>
        <div class="d-flex align-items-center gap-2">
            <img src="<?= htmlspecialchars($avatar_login) ?>" class="avatar-login" data-bs-toggle="tooltip" title="Click để chỉnh sửa thông tin">
            <span>Xin chào, <?= htmlspecialchars($shipper_name) ?></span>
            <a href="shipper_logout.php" class="btn btn-sm btn-danger ms-3">Đăng xuất</a>
        </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Sản phẩm</th>
            <th>Màu sắc</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Shipper</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php
                    $status_class = '';
                    if($row['status']=='Đang xử lý') $status_class='status-dangxuly';
                    elseif($row['status']=='Đang giao hàng') $status_class='status-danggiaohang';
                    elseif($row['status']=='Đã giao hàng') $status_class='status-dagiao';

                    $editable_statuses = [];
                    if($row['shipper_id'] == $shipper_id){
                        if($row['status']=='Đang xử lý'){
                            $editable_statuses = ['Đang xử lý','Đang giao hàng','Đã giao hàng'];
                        } elseif($row['status']=='Đang giao hàng'){
                            $editable_statuses = ['Đang giao hàng','Đã giao hàng'];
                        }
                    }
                ?>
                <tr class="<?= $status_class ?>" data-bs-toggle="collapse" data-bs-target="#order<?= $row['id'] ?>" style="cursor:pointer;">
                    <td>#<?= $row['id'] ?></td>
                    <td>
                        <b><?= htmlspecialchars($row['customer_name']) ?></b><br>
                        <small class="text-muted"><?= htmlspecialchars($row['customer_phone']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['color']) ?>
                       
                    </td>
                    <td><?= number_format($row['total_price'],0,",",".") ?>₫</td>
                    <td>
                        <?php if(!empty($editable_statuses)): ?>
                        <select class="form-select form-select-sm status-select" data-id="<?= $row['id'] ?>">
                            <?php foreach($editable_statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $row['status']==$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <span class="text-muted"><?= htmlspecialchars($row['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['shipper_id']): ?>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= htmlspecialchars($row['shipper_avatar'] ?? 'https://via.placeholder.com/30') ?>" class="avatar-order">
                            <span><?= htmlspecialchars($row['shipper_name']) ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-secondary">Chưa nhận</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['status']=='Đang xử lý' && is_null($row['shipper_id'])): ?>
                            <button class="btn btn-success btn-sm receive-btn" data-id="<?= $row['id'] ?>">Nhận đơn</button>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Collapse row hiển thị chi tiết đơn hàng -->
                <tr class="collapse-row">
                    <td colspan="8" class="p-0">
                        <div id="order<?= $row['id'] ?>" class="collapse">
                            <div class="p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><b>📞 Điện thoại:</b> <?= htmlspecialchars($row['customer_phone']) ?></p>
                                        <p><b>🏠 Địa chỉ:</b> <?= htmlspecialchars($row['customer_address']) ?></p>
                                        <p><b>📅 Ngày đặt:</b> <?= htmlspecialchars($row['order_date']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><b>📦 Sản phẩm:</b> <?= htmlspecialchars($row['product_name']) ?></p>
                                        <p><b>🔢 Số lượng:</b> <?= $row['product_quantity'] ?></p>
                                        <p><b>🎨 Màu sắc:</b> <?= htmlspecialchars($row['color']) ?> </p>
                                        <p><b>💰 Tổng tiền:</b> <?= number_format($row['total_price'],0,",",".") ?>₫</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted">Không có đơn hàng</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    $(".receive-btn").click(function(){
        var order_id = $(this).data("id");
        $.post("shipper_dashboard.php",{action:"receive_order",order_id:order_id},function(data){
            if(data=="success"){ alert("Bạn đã nhận đơn!"); location.reload(); }
            else alert("Đơn đã có shipper khác nhận!");
        });
    });

    $(".status-select").change(function(){
        var order_id = $(this).data("id");
        var new_status = $(this).val();
        $.post("shipper_dashboard.php",{action:"update_status",order_id:order_id,new_status:new_status},function(data){
            if(data=="success"){ alert("Cập nhật trạng thái thành công!"); location.reload(); }
            else alert("Chỉ được chỉnh trạng thái đơn bạn nhận và hợp lệ!");
        });
    });
});
</script>
</body>
</html>
