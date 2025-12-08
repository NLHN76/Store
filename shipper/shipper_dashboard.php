<?php
require_once "../db.php";
if(!isset($_SESSION['shipper_id'])){
    header("Location: shipper_login.php"); exit;
}

$shipper_id = $_SESSION['shipper_id'];
$shipper_name = $_SESSION['shipper_name'];


// --- Lấy thông tin shipper ---
$shipper = $conn->query("SELECT * FROM shipper WHERE id=$shipper_id")->fetch_assoc();
$avatar_login = $shipper['avatar'] ?? 'https://via.placeholder.com/40';

// --- Xử lý AJAX ---
if(isset($_POST['action'])){
    $action = $_POST['action'];

    // Nhận đơn
    if($action=="receive_order"){
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare("UPDATE payment SET shipper_id=?, receive_date=NOW(), status='Đang giao hàng' WHERE id=? AND shipper_id IS NULL AND status='Đang xử lý'");
        $stmt->bind_param("ii",$shipper_id,$order_id);
        $stmt->execute();
        echo $stmt->affected_rows>0?"success":"fail"; exit;
    }

    // Cập nhật trạng thái
    if($action=="update_status"){
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['new_status'];
        $check = $conn->query("SELECT status, shipper_id FROM payment WHERE id=$order_id")->fetch_assoc();
        if($check && $check['shipper_id']==$shipper_id){
            $valid_transitions = [
                'Đang xử lý'=>['Đang xử lý','Đang giao hàng','Đã giao hàng'],
                'Đang giao hàng'=>['Đang giao hàng','Đã giao hàng']
            ];
            if(in_array($new_status,$valid_transitions[$check['status']] ?? [])){
                $stmt = $conn->prepare("UPDATE payment SET status=? WHERE id=?");
                $stmt->bind_param("si",$new_status,$order_id);
                $stmt->execute(); echo "success"; exit;
            }
        }
        echo "fail"; exit;
    }

    // Cập nhật thông tin shipper
    if($action=="update_shipper_info"){
        $id = intval($_POST['shipper_id']);
        $fields = ['name','email','phone','dob','cmt']; $types='sssss'; $params=[];
        foreach($fields as $f) $params[] = $_POST[$f] ?? '';

        if(!empty($_POST['password'])){ $fields[]='password'; $types.='s'; $params[]=password_hash($_POST['password'],PASSWORD_DEFAULT); }
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error']==0){
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if(in_array($ext,['jpg','jpeg','png','gif'])){
                $avatar_path = "uploads/shipper_".$id.".".$ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'],$avatar_path);
                $fields[]='avatar'; $types.='s'; $params[]=$avatar_path;
            }
        }
        $fields_str = implode(', ',array_map(fn($f)=>"$f=?", $fields));
        $stmt = $conn->prepare("UPDATE shipper SET $fields_str WHERE id=?");
        $types.='i'; $params[]=$id;
        $stmt->bind_param($types,...$params);
        echo $stmt->execute()?"success":$conn->error; exit;
    }
}

// --- Lấy danh sách đơn ---
$orders = $conn->query("SELECT p.*, s.name AS shipper_name, s.avatar AS shipper_avatar FROM payment p LEFT JOIN shipper s ON p.shipper_id=s.id ORDER BY p.order_date ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Shipper Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        .avatar-login, .avatar-order {
            border-radius: 50%;
            object-fit: cover;
        }
        .avatar-login { width: 40px; height: 40px; cursor: pointer; }
        .avatar-order { width: 30px; height: 30px; }
        .status-dangxuly { background: #fff3cd; }
        .status-danggiaohang { background: #cce5ff; }
        .status-dagiao { background: #d6d8d9; }
        .collapse-row { background: #f8f9fa; }
    </style>
</head>
<body>
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Shipper Dashboard</h3>
        <div class="d-flex align-items-center gap-2">
            <img src="<?= htmlspecialchars($avatar_login) ?>" class="avatar-login" title="Click để chỉnh sửa thông tin">
            <span>Xin chào, <?= htmlspecialchars($shipper_name) ?></span>
            <a href="shipper_logout.php" class="btn btn-sm btn-danger ms-3">Đăng xuất</a>
        </div>
    </div>

    <!-- Bảng đơn hàng -->
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
            <?php if($orders->num_rows > 0): ?>
                <?php while($row = $orders->fetch_assoc()): 
                    $status_class = match($row['status']) {
                        'Đang xử lý' => 'status-dangxuly',
                        'Đang giao hàng' => 'status-danggiaohang',
                        'Đã giao hàng' => 'status-dagiao',
                        default => ''
                    };
                    $editable_statuses = [];
                    if($row['shipper_id'] == $shipper_id){
                        $editable_statuses = match($row['status']) {
                            'Đang xử lý' => ['Đang xử lý','Đang giao hàng','Đã giao hàng'],
                            'Đang giao hàng' => ['Đang giao hàng','Đã giao hàng'],
                            default => []
                        };
                    }
                ?>
                <tr class="<?= $status_class ?>" data-bs-toggle="collapse" data-bs-target="#order<?= $row['id'] ?>" style="cursor:pointer;">
                    <td>#<?= $row['id'] ?></td>
                    <td>
                        <b><?= htmlspecialchars($row['customer_name']) ?></b><br>
                        <small><?= htmlspecialchars($row['customer_phone']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td><?= number_format($row['total_price'],0,",",".") ?>₫</td>
                    <td>
                        <?php if($editable_statuses): ?>
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

                <!-- Chi tiết đơn hàng -->
                <tr class="collapse-row">
                    <td colspan="8" class="p-0">
                        <div id="order<?= $row['id'] ?>" class="collapse p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><b>📞 Điện thoại:</b> <?= htmlspecialchars($row['customer_phone']) ?></p>
                                    <p><b>🏠 Địa chỉ:</b> <?= htmlspecialchars($row['customer_address']) ?></p>
                                    <p><b>📅 Ngày đặt:</b> <?= htmlspecialchars($row['order_date']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><b>📦 Sản phẩm:</b> <?= htmlspecialchars($row['product_name']) ?></p>
                                    <p><b>🔢 Số lượng:</b> <?= $row['product_quantity'] ?></p>
                                    <p><b>🎨 Màu sắc:</b> <?= htmlspecialchars($row['color']) ?></p>
                                    <p><b>💰 Tổng tiền:</b> <?= number_format($row['total_price'],0,",",".") ?>₫</p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">Không có đơn hàng</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal chỉnh sửa thông tin Shipper -->
<div class="modal fade" id="shipperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="shipperForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_shipper_info">
                    <input type="hidden" name="shipper_id" value="<?= $shipper_id ?>">

                    <div class="mb-2">
                        <label>Tên</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($shipper['name']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($shipper['email']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($shipper['phone']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Ngày sinh</label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($shipper['dob']) ?>">
                    </div>
                    <div class="mb-2">
                        <label>CMT/CCCD</label>
                        <input type="text" name="cmt" class="form-control" value="<?= htmlspecialchars($shipper['cmt']) ?>">
                    </div>
                    <div class="mb-2">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>Avatar</label>
                        <input type="file" name="avatar" accept="image/*" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    // Mở modal khi click avatar
    $(".avatar-login").click(() => {
        new bootstrap.Modal(document.getElementById('shipperModal')).show();
    });

    // Submit form thông tin shipper
    $("#shipperForm").submit(function(e){
        e.preventDefault();
        var fd = new FormData(this);
        $.ajax({
            url: "shipper_dashboard.php",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res=="success"){
                    alert("Cập nhật thành công!");
                    location.reload();
                } else {
                    alert("Lỗi: "+res);
                }
            }
        });
    });

    // Nhận đơn
    $(".receive-btn").click(function(){
        let id = $(this).data("id");
        $.post("shipper_dashboard.php", {action:"receive_order", order_id:id}, d => {
            alert(d=="success"?"Bạn đã nhận đơn!":"Đơn đã có shipper khác nhận!");
            if(d=="success") location.reload();
        });
    });

    // Cập nhật trạng thái
    $(".status-select").change(function(){
        let id = $(this).data("id"),
            s = $(this).val();
        $.post("shipper_dashboard.php", {action:"update_status", order_id:id, new_status:s}, d => {
            alert(d=="success"?"Cập nhật trạng thái thành công!":"Chỉ được chỉnh trạng thái hợp lệ!");
            if(d=="success") location.reload();
        });
    });
});
</script>
</body>
</html>
