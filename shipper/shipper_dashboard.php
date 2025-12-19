<?php
require_once "../db.php";
require_once "function.php";

// Lấy ID đơn lớn nhất từ bảng payment
$result_last = $conn->query("SELECT MAX(id) as last_id FROM payment");
$lastOrderId = ($row = $result_last->fetch_assoc()) ? intval($row['last_id']) : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Shipper Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="shipper.css">

</head>
<body>
<div class="container mt-4">

    <div id="newOrderBanner" class="alert alert-success fixed-top text-center d-none" style="z-index:9999;">
        📦 Có đơn hàng mới!
    </div>

    <audio id="newOrderSound" src="notification.mp3" preload="auto"></audio>

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

                    // Các trạng thái shipper có thể chỉnh
                    $editable_statuses = [];
                    if($row['shipper_id'] == $shipper_id){
                        $editable_statuses = match($row['status']) {
                            'Đang xử lý' => ['Đang xử lý','Đang giao hàng','Đã giao hàng'],
                            'Đang giao hàng' => ['Đang giao hàng','Đã giao hàng'],
                            default => []
                        };
                    }
                ?>
                
                <tr class="<?= $status_class ?>" 
                    <?= $row['status'] !== 'Đã giao hàng' ? 'data-bs-toggle="collapse" data-bs-target="#order'.$row['id'].'" style="cursor:pointer;"' : '' ?>>
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

                <?php if($row['status'] !== 'Đã giao hàng'): ?>
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
                <?php endif; ?>

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
<script src="shipper.js"></script>
</body>
</html>