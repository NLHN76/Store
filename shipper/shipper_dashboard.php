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

    // Nhận đơn
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

    // Cập nhật trạng thái đơn
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

    // Cập nhật thông tin shipper
    if($action=="update_shipper_info"){
        $id = intval($_POST['shipper_id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? '';
        $dob = $_POST['dob'] ?? NULL;
        $cmt = $_POST['cmt'] ?? '';
        $password = $_POST['password'] ?? '';

        // Upload avatar
        $avatar_path = '';
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error']==0){
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatar_path = "uploads/shipper_".$id.".".$ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
        }

        // Build SQL
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

// Lấy danh sách đơn hàng với avatar shipper
$sql = "SELECT p.*, s.name AS shipper_name, s.avatar AS shipper_avatar, s.email AS shipper_email, s.phone AS shipper_phone, p.receive_date
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

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Khách hàng</th><th>Điện thoại</th><th>Địa chỉ</th>
                <th>Sản phẩm</th><th>Số lượng</th><th>Tổng tiền</th><th>Ngày đặt</th>
                <th>Trạng thái</th><th>Shipper</th><th>Hành động</th>
            </tr>
        </thead>
        <tbody>
<?php if($result->num_rows>0): ?>
    <?php while($row=$result->fetch_assoc()): ?>
        <?php
            $status_class = '';
            if($row['status']=='Đang xử lý') $status_class='status-dangxuly';
            elseif($row['status']=='Đang giao hàng') $status_class='status-danggiaohang';
            elseif($row['status']=='Đã giao hàng') $status_class='status-dagiao';

            // Xác định trạng thái có thể chỉnh
            $editable_statuses = [];
            if($row['shipper_id'] == $shipper_id){
                if($row['status']=='Đang xử lý'){
                    $editable_statuses = ['Đang xử lý','Đang giao hàng','Đã giao hàng'];
                } elseif($row['status']=='Đang giao hàng'){
                    $editable_statuses = ['Đang giao hàng','Đã giao hàng'];
                }
            }
        ?>
        <tr class="<?= $status_class ?>">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
            <td><?= htmlspecialchars($row['customer_address']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= $row['product_quantity'] ?></td>
            <td><?= number_format($row['total_price'],0,",",".") ?>₫</td>
            <td><?= $row['order_date'] ?></td>

            <!-- Trạng thái -->
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

            <!-- Shipper -->
            <td>
            <?php if($row['shipper_id']): ?>
                <div class="d-flex align-items-center gap-2">
                    <img src="<?= htmlspecialchars($row['shipper_avatar'] ?? 'https://via.placeholder.com/30') ?>" 
                         class="avatar-order" data-bs-toggle="tooltip" data-bs-html="true"
                         title="
                            <b>Shipper:</b> <?= htmlspecialchars($row['shipper_name']) ?><br>
                            <b>Email:</b> <?= htmlspecialchars($row['shipper_email']) ?><br>
                            <b>SĐT:</b> <?= htmlspecialchars($row['shipper_phone']) ?><br>
                            <b>Nhận đơn:</b> <?= $row['receive_date'] ? date('d/m/Y H:i', strtotime($row['receive_date'])) : '' ?>
                         ">
                    <span><?= htmlspecialchars($row['shipper_name']) ?></span>
                </div>
            <?php else: ?>
                Chưa nhận
            <?php endif; ?>
            </td>

            <!-- Hành động -->
            <td>
            <?php if($row['status']=='Đang xử lý' && is_null($row['shipper_id'])): ?>
                <button class="btn btn-success btn-sm receive-btn" data-id="<?= $row['id'] ?>">Nhận đơn</button>
            <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="11" class="text-center">Không có đơn hàng</td></tr>
<?php endif; ?>
</tbody>
    </table>
</div>

<!-- Modal chỉnh sửa thông tin shipper -->
<div class="modal fade" id="editShipperModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editShipperForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Chỉnh sửa thông tin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="shipper_id" value="<?= $shipper_id ?>">
          <div class="mb-3 text-center">
            <img id="avatarPreview" src="<?= htmlspecialchars($avatar_login) ?>" 
                 style="width:80px; height:80px; border-radius:50%; object-fit:cover; margin-bottom:10px;">
          </div>
          <div class="mb-3">
            <label>Ảnh đại diện</label>
            <input type="file" name="avatar" class="form-control" id="avatarInput">
          </div>
          <div class="mb-3"><label>Tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($shipper_name) ?>" required></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($avatar_row['email']) ?>" required></div>
          <div class="mb-3"><label>Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($avatar_row['phone']) ?>"></div>
          <div class="mb-3"><label>Ngày sinh</label><input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($avatar_row['dob'] ?? '') ?>"></div>
          <div class="mb-3"><label>CMND/CCCD</label><input type="text" name="cmt" class="form-control" value="<?= htmlspecialchars($avatar_row['cmt'] ?? '') ?>"></div>
          <div class="mb-3"><label>Mật khẩu mới (nếu muốn)</label><input type="password" name="password" class="form-control"></div>
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
$(document).ready(function(){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el,{html:true}));

    $(".avatar-login").click(()=>$("#editShipperModal").modal('show'));

    $("#avatarInput").change(function(){
        const file = this.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = e=>$("#avatarPreview").attr("src", e.target.result);
            reader.readAsDataURL(file);
        }
    });

    $("#editShipperForm").submit(function(e){
        e.preventDefault();
        var formData = new FormData(this);
        formData.append("action","update_shipper_info");
        $.ajax({
            url:'shipper_dashboard.php',
            type:'POST',
            data: formData,
            contentType:false,
            processData:false,
            success: function(data){
                if(data=="success"){ alert("Cập nhật thông tin thành công!"); location.reload(); }
                else alert("Lỗi: "+data);
            }
        });
    });

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
