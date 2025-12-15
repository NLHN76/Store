<div class="product-container">
<?php if (empty($products)): ?>
    <p class="no-products">
        <?= !empty($search) ? 'Không tìm thấy sản phẩm phù hợp với "'.htmlspecialchars($search).'"' : 'Chưa có sản phẩm nào trong cửa hàng.' ?>
    </p>
<?php else: ?>
    <?php foreach ($products as $product): 
        $is_active = $product['is_active'] == 1;
        $product_colors = explode(',', $product['color'] ?? '');
        $img = '../uploads/' . ($product['image'] ?? '');
    ?>
        <div class="product-box <?= !$is_active ? 'inactive' : '' ?>">
            <?php if (!$is_active): ?><div class="inactive-overlay">ĐÃ TẮT</div><?php endif; ?>
            <h4><?= htmlspecialchars($product['name']) ?></h4>
            <p>Mã: <?= htmlspecialchars($product['product_code'] ?? 'N/A') ?></p>
            <p>Loại: <?= htmlspecialchars($product['category']) ?></p>
            <img src="<?= file_exists($img) ? htmlspecialchars($img) : 'uploads/placeholder.png' ?>" alt="Ảnh <?= htmlspecialchars($product['name']) ?>">
            <p><?= number_format($product['price'],0,',','.') ?> VNĐ</p>

            <!-- Toggle trạng thái -->
            <form method="POST" class="toggle-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="action" value="toggle_status">
                <button type="submit"><?= $is_active ? 'TẮT SẢN PHẨM' : 'BẬT SẢN PHẨM' ?></button>
            </form>

            <!-- Form edit/delete -->
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="text" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
                <input type="text" name="product_price" value="<?= number_format($product['price'],0,',','.') ?>" required>

                <label>Chọn màu sắc:</label>
                <div class="color-options">
                    <?php foreach($available_colors as $color): ?>
                        <label><input type="checkbox" name="product_colors[]" value="<?= htmlspecialchars($color) ?>" <?= in_array($color,$product_colors)?'checked':'' ?>><?= htmlspecialchars($color) ?></label><br>
                    <?php endforeach; ?>
                    <label><input type="checkbox" name="product_colors[]" value="Mặc định" <?= in_array('Mặc định',$product_colors)?'checked':'' ?>>Màu mặc định</label>
                </div>

                <select name="product_category" required>
                    <?php foreach(['Tai nghe','Cáp sạc','Ốp lưng','Kính cường lực'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $product['category']==$cat?'selected':'' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="file" name="product_image" accept="image/*">

                <div class="form-actions">
                    <button type="submit" name="action" value="edit">Lưu</button>
                    <button type="submit" name="action" value="delete" onclick="return confirm('Bạn có chắc muốn xóa <?= htmlspecialchars(addslashes($product['name'])) ?>?');">Xóa</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>
