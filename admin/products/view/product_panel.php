<!-- Nút thêm chi tiết sản phẩm -->
<ul class="center-button">
    <li>
        <a href="../products_detail/admin_products-detail.php" class="btn-add">
            <i class="fas fa-list-ul" style="margin-right:6px;"></i>
            Thêm chi tiết sản phẩm
        </a>
    </li>
</ul>

<!-- Box tìm kiếm -->
<div class="search-box">
    <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>">
        <input type="text" name="search" placeholder="Tìm theo loại,tên hoặc mã sản phẩm..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">🔍</button>
    </form>
</div>

<!-- Quản lý màu sắc -->
<button id="toggle-color-panel">⚙ Mở Quản Lý Màu Sắc</button>
<div id="color-panel" style="display:none; margin-top:15px; padding:15px; border:1px solid #ccc; border-radius:8px;">
    <!-- Thêm màu -->
    <form method="POST">
        <input type="hidden" name="action" value="add_color">
        <input type="text" name="new_color" placeholder="Nhập tên màu" required>
        <button type="submit">✅ Lưu màu</button>
    </form>

    <!-- Xóa màu -->
    <?php if (!empty($available_colors)): ?>
        <ul>
        <?php foreach ($available_colors as $color): ?>
            <li>
                <?= htmlspecialchars($color) ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_color">
                    <input type="hidden" name="delete_color" value="<?= htmlspecialchars($color) ?>">
                    <button type="submit">❌ Xóa</button>
                </form>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>⚠️ Chưa có màu nào để xóa.</p>
    <?php endif; ?>
</div>

<!-- Nút thêm sản phẩm & modal -->
<button id="toggle-add-form-btn">Thêm Sản Phẩm</button>

<div id="modal-overlay" class="modal-overlay" style="display:none;"></div>
<div id="add-product-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <button id="close-modal-btn">×</button>
        <form method="POST" enctype="multipart/form-data">

            <label>Chọn màu sắc:</label>
            <div class="color-options">
                <?php if (!empty($available_colors)): ?>
                    <?php foreach ($available_colors as $color): ?>
                        <label>
                            <input type="checkbox" name="product_colors[]" value="<?= htmlspecialchars($color) ?>">
                            <?= htmlspecialchars($color) ?>
                        </label><br>
                    <?php endforeach; ?>
                <?php endif; ?>
                <label>
                    <input type="checkbox" name="product_colors[]" value="Mặc định">
                    Màu mặc định
                </label>
            </div>

            <input type="text" name="product_name" placeholder="Tên sản phẩm" required>

            <!-- Thêm trường thương hiệu -->
            <input type="text" name="product_brand" placeholder="Thương hiệu" required>

            <input type="text" name="product_price" placeholder="Chỉ nhập số" required>

            <select name="product_category" required>
                <option value="" disabled selected>-- Chọn loại sản phẩm --</option>
                <option value="Tai nghe">Tai nghe</option>
                <option value="Cáp sạc">Cáp sạc</option>
                <option value="Ốp lưng">Ốp lưng</option>
                <option value="Kính cường lực">Kính cường lực</option>
            </select>

            <input type="file" name="product_image" accept="image/*">

            <input type="hidden" name="action" value="add">

            <button type="submit">Thêm sản phẩm</button>
        </form>
    </div>
</div>

<!-- Báo giá -->
<form action="quote/product_quote.php" method="get" target="_blank" style="margin-top:10px;">
    <button type="submit">Báo Giá Sản Phẩm</button>
</form>
