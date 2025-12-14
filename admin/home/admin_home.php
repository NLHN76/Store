<?php
require_once "../../db.php"; 
require_once "home_functions.php";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Banner & Khuyến Mãi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../css/home.css">
</head>

<body>
<div class="container">
    
    <h2>Quản lý Trang Chủ</h2>

    <!-- ================= BANNER ================= -->
    <div class="window">
        <h3>Banner Trang Chủ</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="banner_action" value="update">
            <input type="text" name="banner_title"
                   value="<?= htmlspecialchars($banner['title']??'') ?>"
                   placeholder="Tiêu đề banner">

            <textarea name="banner_description"
                      placeholder="Mô tả banner"><?= htmlspecialchars($banner['description']??'') ?></textarea>

            <input type="file" name="banner_image" accept=".jpg">

            <?php if(!empty($banner['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($banner['image']) ?>" alt="Banner">
            <?php endif; ?>

            <button type="submit">Cập nhật Banner</button>
        </form>
    </div>


    <!-- ================= KHUYẾN MÃI ================= -->
    <div class="window">
        <h3>Danh Sách Khuyến Mãi</h3>
        <button id="togglePromoForm">Thêm Khuyến Mãi</button>

        <!-- Popup Form -->
        <div id="promoFormModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Thêm Khuyến Mãi</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="promo_action" value="add">
                    <input type="text" name="promo_title" placeholder="Tiêu đề">
                    <textarea name="promo_description" placeholder="Mô tả"></textarea>
                    <input type="text" name="promo_link" placeholder="Link chi tiết">
                    <input type="file" name="promo_image" accept=".jpg">
                    <button type="submit">Thêm Khuyến Mãi</button>
                </form>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Tiêu đề</th><th>Mô tả</th>
                    <th>Hình ảnh</th><th>Link</th><th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($promotions as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars($p['description']) ?></td>
                    <td>
                        <?php if(!empty($p['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" width="80">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['link']) ?></td>
                    <td>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="promo_id" value="<?= $p['id'] ?>">
                            <input type="text" name="promo_title" value="<?= htmlspecialchars($p['title']) ?>">
                            <textarea name="promo_description"><?= htmlspecialchars($p['description']) ?></textarea>
                            <input type="text" name="promo_link" value="<?= htmlspecialchars($p['link']) ?>">
                            <input type="file" name="promo_image" accept=".jpg">
                            <button type="submit" name="promo_action" value="edit">Sửa</button>
                            <button type="submit" name="promo_action" value="delete"
                                    onclick="return confirm('Xóa khuyến mãi này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <!-- ================= SẢN PHẨM NỔI BẬT ================= -->
     <div class="window" id="featured-section">
        <h3>Sản Phẩm Nổi Bật</h3>

        <!-- Thêm sản phẩm nổi bật -->
        <form method="POST" class="featured-form">
            <input type="hidden" name="featured_action" value="add">

            <select name="product_id" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach($products as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> -
                        <?= number_format($p['price']) ?>đ
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">➕ Thêm</button>
        </form>

        <!-- Danh sách sản phẩm nổi bật -->
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($featured_products as $fp): ?>
                <tr>
                    <td>
                        <img src="../uploads/<?= htmlspecialchars($fp['image']) ?>" width="80">
                    </td>
                    <td><?= htmlspecialchars($fp['name']) ?></td>
                    <td><?= number_format($fp['price']) ?>đ</td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="featured_action" value="delete">
                            <input type="hidden" name="product_id" value="<?= $fp['id'] ?>">
                            <button onclick="return confirm('Xóa sản phẩm nổi bật?')">❌</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>





<script src="home.js"></script>
</body>
</html>
