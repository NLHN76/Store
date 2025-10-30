<?php
// Kết nối đến cơ sở dữ liệu 
$dsn = 'mysql:host=localhost;dbname=store;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Kết nối không thành công: ' . $e->getMessage());
    die('Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
}


function clean_price($price_string) {
    $cleaned_price = str_replace('.', '', $price_string);
    $cleaned_price = str_replace(',', '.', $cleaned_price);
    if (!is_numeric($cleaned_price)) {
        return 0;
    }
    return floatval($cleaned_price);
}
function generate_product_code($category, $pdo) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category), 0, 2));
    if (empty($prefix)) {
        $prefix = 'SP';
    }
    $max_tries = 5;
    for ($i = 0; $i < $max_tries; $i++) {
        $code = $prefix . rand(1000, 9999);
        $stmt = $pdo->prepare("SELECT id FROM products WHERE product_code = :code");
        $stmt->execute(['code' => $code]);
        if ($stmt->fetch() === false) {
            return $code;
        }
    }
    return $prefix . uniqid();
}



// Xử lý sửa sản phẩm 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    
    
     $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
     $name = trim(filter_input(INPUT_POST, 'product_name', FILTER_SANITIZE_STRING));
     $price_input = $_POST['product_price'] ?? '0';
     $category = trim(filter_input(INPUT_POST, 'product_category', FILTER_SANITIZE_STRING));

     $price = clean_price($price_input); // Sử dụng hàm clean_price

     $allowed_categories = ['Tai nghe', 'Cáp sạc', 'Ốp lưng', 'Kính cường lực']; // Danh sách loại hợp lệ

     // Kiểm tra dữ liệu cơ bản
     if (!$id || empty($name) || $price < 0 || empty($category) || !in_array($category, $allowed_categories)) {
         $edit_error = "Dữ liệu sửa sản phẩm không hợp lệ.";
     
     } else {
         $current_image = null;
         $stmt_current = $pdo->prepare("SELECT image FROM products WHERE id = :id");
         $stmt_current->execute(['id' => $id]);
         $current_product = $stmt_current->fetch(PDO::FETCH_ASSOC);
         if ($current_product) {
             $current_image = $current_product['image'];
         }

         $image_sql_part = '';
         $params_update = ['name' => $name, 'price' => $price, 'category' => $category, 'id' => $id];

         if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
             $file_info = $_FILES['product_image'];
             $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
             $max_size = 2 * 1024 * 1024;

             if (in_array($file_info['type'], $allowed_types) && $file_info['size'] <= $max_size) {
                 $upload_dir = 'uploads/';
                 $extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                 $new_filename = uniqid('prod_', true) . '.' . strtolower($extension);
                 $target_path = $upload_dir . $new_filename;

                 if (!is_dir($upload_dir)) {
                     mkdir($upload_dir, 0775, true);
                 }

                 if (move_uploaded_file($file_info['tmp_name'], $target_path)) {
                    
                     $image_sql_part = ', image = :image';
                     $params_update['image'] = $new_filename;
                 } else {
                     $edit_error = "Có lỗi khi di chuyển file ảnh mới.";
                 }
             } else {
                  $edit_error = "Loại file không hợp lệ hoặc kích thước quá lớn.";
             }
         } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
             
              $image_sql_part = ', image = :image';
              $params_update['image'] = null;
         }


         if (empty($edit_error)) {
             $sql_update = "UPDATE products SET name = :name, price = :price, category = :category $image_sql_part WHERE id = :id";
             $stmt_update = $pdo->prepare($sql_update);
             if ($stmt_update->execute($params_update)) {
                  header("Location: {$_SERVER['PHP_SELF']}?status=edited");
                  exit;
             } else {
                  $edit_error = "Có lỗi khi cập nhật sản phẩm.";
                  error_log("Lỗi cập nhật sản phẩm ID $id: " . implode(", ", $stmt_update->errorInfo()));
             }
         }
         // Nếu có lỗi $edit_error, không redirect, để form hiển thị lại với lỗi
     }
}



// Xử lý xóa sản phẩm 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header("Location: {$_SERVER['PHP_SELF']}?status=deleted");
            exit;
        } catch (PDOException $e) {
             echo "Lỗi khi xóa sản phẩm."; 
             error_log("Lỗi xóa sản phẩm ID $id: " . $e->getMessage());
        }
    } else {
        echo "ID sản phẩm không hợp lệ."; 
    }
}





// Xử lý thêm sản phẩm 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
   
     $name = trim(filter_input(INPUT_POST, 'product_name', FILTER_SANITIZE_STRING));
     $price_input = $_POST['product_price'] ?? '0';
     $category = trim(filter_input(INPUT_POST, 'product_category', FILTER_SANITIZE_STRING));
     $price = clean_price($price_input);
     $allowed_categories = ['Tai nghe', 'Cáp sạc', 'Ốp lưng', 'Kính cường lực'];
     $add_error = ''; // Biến lưu lỗi thêm mới

     if (empty($name) || $price < 0 || empty($category) || !in_array($category, $allowed_categories)) {
          $add_error = "Dữ liệu thêm sản phẩm không hợp lệ.";
     } else {
         $image_name = null;
         if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
             $file_info = $_FILES['product_image'];
             $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
             $max_size = 2 * 1024 * 1024;

             if (in_array($file_info['type'], $allowed_types) && $file_info['size'] <= $max_size) {
                 $upload_dir = 'uploads/';
                 $extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                 $new_filename = uniqid('prod_', true) . '.' . strtolower($extension);
                 $target_path = $upload_dir . $new_filename;

                 if (!is_dir($upload_dir)) {
                     mkdir($upload_dir, 0775, true);
                 }

                 if (move_uploaded_file($file_info['tmp_name'], $target_path)) {
                     $image_name = $new_filename;
                 } else {
                     $add_error = "Có lỗi khi tải ảnh lên.";
                 }
             } else {
                 $add_error = "Loại file không hợp lệ hoặc kích thước quá lớn khi thêm.";
             }
         }

         if (empty($add_error)) {
             $product_code = generate_product_code($category, $pdo);
             try {
                 $sql_insert = "INSERT INTO products (name, price, category, image, product_code) VALUES (:name, :price, :category, :image, :code)"; // is_active sẽ tự động là 1 (default)
                 $stmt_insert = $pdo->prepare($sql_insert);
                 $stmt_insert->execute([
                     'name' => $name,
                     'price' => $price,
                     'category' => $category,
                     'image' => $image_name,
                     'code' => $product_code
                 ]);

                 header("Location: {$_SERVER['PHP_SELF']}?status=added");
                 exit;
             } catch (PDOException $e) {
                 $add_error = "Có lỗi khi thêm sản phẩm vào cơ sở dữ liệu.";
                 error_log("Lỗi thêm sản phẩm: " . $e->getMessage());
                 if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'product_code') !== false) {
                      $add_error .= " Mã sản phẩm bị trùng, vui lòng thử lại.";
                 }
             }
         }
     }
      // Nếu có lỗi $add_error, không redirect, để form hiển thị lại với lỗi
}




// ---  Xử lý bật/tắt sản phẩm ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($id) {
        try {
            // Lấy trạng thái hiện tại
            $stmt_current = $pdo->prepare("SELECT is_active FROM products WHERE id = :id");
            $stmt_current->execute(['id' => $id]);
            $current_status = $stmt_current->fetchColumn();

            if ($current_status !== false) {
                // Đảo ngược trạng thái (1 thành 0, 0 thành 1)
                $new_status = $current_status == 1 ? 0 : 1;

                // Cập nhật trạng thái mới
                $stmt_toggle = $pdo->prepare("UPDATE products SET is_active = :new_status WHERE id = :id");
                if ($stmt_toggle->execute(['new_status' => $new_status, 'id' => $id])) {
                    // Thêm tham số trạng thái mới vào URL để thông báo chính xác hơn
                    $status_text = $new_status == 1 ? 'enabled' : 'disabled';
                    header("Location: {$_SERVER['PHP_SELF']}?status=toggled&new_state=" . $status_text);
                    exit;
                } else {
                    header("Location: {$_SERVER['PHP_SELF']}?status=toggle_error");
                    exit;
                }
            } else {
                 header("Location: {$_SERVER['PHP_SELF']}?status=not_found");
                 exit;
            }
        } catch (PDOException $e) {
            error_log("Lỗi toggle status sản phẩm ID $id: " . $e->getMessage());
            header("Location: {$_SERVER['PHP_SELF']}?status=toggle_error");
            exit;
        }
    } else {
        header("Location: {$_SERVER['PHP_SELF']}?status=invalid_id");
        exit;
    }
}




// Tìm kiếm sản phẩm
$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM products";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE name LIKE :search OR product_code LIKE :search OR category LIKE :search";
    $params['search'] = "%$search%";
}

$sql .= " ORDER BY is_active DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);






// Lấy thông báo trạng thái từ URL (Cập nhật)
$status_message = '';
$status_type = 'success'; 
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $status_message = "Sản phẩm đã được thêm thành công!"; break;
        case 'edited': $status_message = "Sản phẩm đã được cập nhật thành công!"; break;
        case 'deleted': $status_message = "Sản phẩm đã được xóa thành công!"; break;
        case 'toggled':
            $new_state = $_GET['new_state'] ?? '';
            if ($new_state == 'enabled') {
                $status_message = "Sản phẩm đã được BẬT thành công!";
            } elseif ($new_state == 'disabled') {
                $status_message = "Sản phẩm đã được TẮT thành công!";
            } else {
                $status_message = "Trạng thái sản phẩm đã được thay đổi!";
            }
            break;
        // Các trường hợp lỗi
        case 'toggle_error':
            $status_message = "Lỗi: Không thể thay đổi trạng thái sản phẩm.";
            $status_type = 'error';
            break;
        case 'not_found':
            $status_message = "Lỗi: Không tìm thấy sản phẩm để thay đổi trạng thái.";
            $status_type = 'error';
            break;
         case 'invalid_id':
             $status_message = "Lỗi: ID sản phẩm không hợp lệ.";
             $status_type = 'error';
             break;
        // Thêm các trường hợp lỗi từ các action khác nếu cần
         case 'add_error':
             $status_message = $add_error ?? "Lỗi khi thêm sản phẩm."; // Lấy lỗi từ biến $add_error nếu có
             $status_type = 'error';
             break;
         case 'edit_error':
             $status_message = $edit_error ?? "Lỗi khi cập nhật sản phẩm."; // Lấy lỗi từ biến $edit_error nếu có
             $status_type = 'error';
             break;
    }
}
// Xử lý lỗi từ POST nếu không redirect
if (!empty($add_error) && !isset($_GET['status'])) {
    $status_message = $add_error;
    $status_type = 'error';
}
if (!empty($edit_error) && !isset($_GET['status'])) {
    $status_message = $edit_error;
    $status_type = 'error';
}
?>






<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÍ SẢN PHẨM</title>
    <link rel="stylesheet" href="css/products.css">
     
       
</head>

<body>
    <div class="container">
        <a href="admin_interface.php" class="back-button" title="Quay lại trang quản trị">
            <img src="uploads/exit.jpg" alt="Quay lại"> 
        </a>
        <h2>QUẢN LÍ SẢN PHẨM</h2>
        <!--Trạng thái -->
        <?php if (!empty($status_message)): ?>
            <div id="status-message" class="status-message <?= $status_type  ?>">
                <?= htmlspecialchars($status_message) ?>
            </div>
        <?php endif; ?>
        
        <ul class="center-button">
  <li>
    <a href="admin_products-detail.php" class="btn-add">
      <i class="fas fa-list-ul" style="margin-right:6px;"></i>
      Thêm chi tiết sản phẩm
    </a>
  </li>
</ul>

           <!--Box tìm kiếm-->
        <div class="search-box">
            <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>">
                <input type="text" name="search" placeholder="Tìm theo loại,tên hoặc mã sản phẩm..." value="<?= htmlspecialchars($search) ?>" aria-label="Ô tìm kiếm sản phẩm">
                <button type="submit" aria-label="Tìm kiếm">🔍</button>
            </form>
        </div>
       

        <!--Nút thêm sản phẩm-->
        <div style="text-align: center; margin-top: 30px; margin-bottom: 20px;">
             <button id="toggle-add-form-btn" type="button" aria-controls="add-product-modal" aria-expanded="false" style="padding: 12px 25px; font-size: 1em; background-color: #17a2b8; border: none; border-radius: 6px; color: white; cursor: pointer;">
                 Thêm Sản Phẩm
             </button>
        </div>

       <!-- Nút báo giá sản phẩm -->
      <form action="product_quote.php" method="get" target="_blank" style="margin-bottom: 15px; text-align: center;">
        <button type="submit">Báo Giá Sản Phẩm</button>
      </form>




            <!--Tìm kiếm khi trạng thái bật-tắt sản phẩm -->
        <div class="product-container">
            <?php if (empty($products)): ?>
                <p class="no-products">
                    <?php if (!empty($search)): ?>
                         Không tìm thấy sản phẩm nào phù hợp với "<?= htmlspecialchars($search) ?>".
                    <?php else: ?>
                         Chưa có sản phẩm nào trong cửa hàng.
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php $is_active = $product['is_active'] == 1; ?>
                    <div class="product-box <?= !$is_active ? 'inactive' : '' /* Thêm class nếu không active */ ?>">

                        <!--  Thông báo trạng thái nếu không active -->
                        <?php if (!$is_active): ?>
                            <div class="inactive-overlay">ĐÃ TẮT</div>
                        <?php endif; ?>

                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p class="product-code">Mã: <?= htmlspecialchars($product['product_code'] ?? 'N/A') ?></p>
                        <p>Loại: <?= htmlspecialchars($product['category']) ?></p>
                        <?php
                            $image_path = 'uploads/' . ($product['image'] ?? '');
                            if (!empty($product['image']) && file_exists($image_path)):
                        ?>
                            <img src="<?= htmlspecialchars($image_path) ?>" alt="Ảnh <?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                             <img src="uploads/placeholder.png" alt="Ảnh mẫu">
                        <?php endif; ?>
                        <p class="product-price"><?= number_format($product['price'], 0, ',', '.') ?> VNĐ</p>





                        <!--  Form Bật/Tắt Sản Phẩm -->
                        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="toggle-form">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="action" value="toggle_status">
                            <?php if ($is_active): ?>
                                <button type="submit" class="toggle-button disable" title="Tắt sản phẩm này (sẽ bị ẩn)">TẮT SẢN PHẨM</button>
                            <?php else: ?>
                                <button type="submit" class="toggle-button enable" title="Bật sản phẩm này (sẽ hiển thị lại)">BẬT SẢN PHẨM</button>
                            <?php endif; ?>
                        </form>






                        <!-- Form Sửa/Xóa Sản Phẩm -->
                        <form method="POST" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF'] ?>" class="edit-form">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="text" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Tên sản phẩm" required aria-label="Tên sản phẩm">
                            <input type="text" name="product_price" value="<?= number_format($product['price'], 0, ',', '.') ?>" placeholder="Giá (VD: 20.000)" required aria-label="Giá sản phẩm">
                            <select name="product_category" required aria-label="Loại sản phẩm">
                                <?php
                                $categories = ['Tai nghe', 'Cáp sạc', 'Ốp lưng', 'Kính cường lực'];
                                foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= ($product['category'] ?? '') == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="file" name="product_image" accept="image/jpeg, image/png, image/gif" aria-label="Chọn ảnh mới (không bắt buộc)">
                           
                            <?php if (!empty($product['image'])): ?>
                               
                            <?php endif; ?>
                           
                            <div class="form-actions">
                                <button type="submit" name="action" value="edit">Lưu</button>
                                <button type="submit" name="action" value="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm \'<?= htmlspecialchars(addslashes($product['name'])) ?>\'?\n');">Xóa</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

               


    <div id="modal-overlay" class="modal-overlay" aria-hidden="true"></div> 

<div id="add-product-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-content">
        <button id="close-modal-btn" class="close-button" aria-label="Đóng">×</button>



      


        <div class="add-product-form">
            <h2 id="modal-title">Thêm Sản Phẩm Mới</h2>
            <form method="POST" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF'] ?>">
               
                <label for="add_product_name_modal" style="display: block; text-align: left; margin-top: 10px; font-weight: 500;">Tên sản phẩm:</label>
                <input type="text" id="add_product_name_modal" name="product_name" placeholder="Tên sản phẩm" required aria-label="Tên sản phẩm mới">

                <label for="add_product_price_modal" style="display: block; text-align: left; margin-top: 10px; font-weight: 500;">Giá sản phẩm:</label>
                <input type="text" id="add_product_price_modal" name="product_price" placeholder="Chỉ nhập số, VD: 150.000" required aria-label="Giá sản phẩm mới" inputmode="numeric">
                <small style="display: block; text-align: left; margin-top: 3px; color: #6c757d; font-size: 0.85em;">
                    
                </small>

                <label for="add_product_category_modal" style="display: block; text-align: left; margin-top: 15px; font-weight: 500;">Loại sản phẩm:</label>
                <select id="add_product_category_modal" name="product_category" required aria-label="Loại sản phẩm mới">
                    <option value="" disabled selected>-- Chọn loại sản phẩm --</option>
                    <option value="Tai nghe">Tai nghe</option>
                    <option value="Cáp sạc">Cáp sạc</option>
                    <option value="Ốp lưng">Ốp lưng</option>
                    <option value="Kính cường lực">Kính cường lực</option>
                </select>

                <label for="add_product_image_modal" style="display: block; text-align: left; margin-top: 15px; font-weight: 500;"></label>
                <input type="file" id="add_product_image_modal" name="product_image" accept="image/jpeg, image/png, image/gif" aria-label="Chọn ảnh sản phẩm ">

                <input type="hidden" name="action" value="add">
                <button type="submit" style="margin-top: 20px; width: 100%;">Thêm sản phẩm</button>
            </form>
        </div>
        

    </div> 
</div> 

</body>
</html>





<script>
    function formatNumberInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value.length > 1 && value.startsWith('0')) {
            value = value.substring(1);
        }
        let number = parseInt(value, 10);
        if (isNaN(number)) {
            input.value = '';
        } else {
            input.value = number.toLocaleString('vi-VN');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Định dạng giá sản phẩm (edit + modal)
        const priceInputs = document.querySelectorAll('input[name="product_price"]');
        priceInputs.forEach(input => {
            if (input.value) formatNumberInput(input);
            input.addEventListener('input', () => formatNumberInput(input));
            input.addEventListener('blur', () => formatNumberInput(input));
        });

        // Thông báo trạng thái (ẩn sau 3.5s)
        const statusMessage = document.getElementById('status-message');
        if (statusMessage) {
            setTimeout(() => {
                statusMessage.style.transition = 'opacity 0.5s ease';
                statusMessage.style.opacity = '0';
                setTimeout(() => statusMessage.remove(), 500);
            }, 3500);
        }

        // Xác nhận khi bật/tắt sản phẩm
        document.querySelectorAll('.toggle-form').forEach(form => {
            form.addEventListener('submit', function (event) {
                const button = form.querySelector('.toggle-button');
                const productName = form.closest('.product-box').querySelector('h4').textContent;
                const isActive = button.classList.contains('disable');
                const actionText = isActive ? 'TẮT' : 'BẬT';
                if (!confirm(`Bạn có chắc muốn ${actionText} sản phẩm "${productName}"?`)) {
                    event.preventDefault();
                }
            });
        });

        // Modal thêm sản phẩm
        const toggleBtn = document.getElementById('toggle-add-form-btn');
        const modal = document.getElementById('add-product-modal');
        const overlay = document.getElementById('modal-overlay');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const modalPriceInput = document.getElementById('add_product_price_modal');

        function openModal() {
            overlay.style.display = 'block';
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            overlay.setAttribute('aria-hidden', 'false');
            const firstInput = modal.querySelector('input[name="product_name"]');
            if (firstInput) firstInput.focus();
            if (modalPriceInput && modalPriceInput.value) {
                formatNumberInput(modalPriceInput);
            }
        }

        function closeModal() {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            overlay.setAttribute('aria-hidden', 'true');
            if (toggleBtn) toggleBtn.focus();
        }

        if (toggleBtn) toggleBtn.addEventListener('click', openModal);
        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (overlay) {
            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) closeModal();
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && modal.style.display === 'block') {
                closeModal();
            }
        });

       
    });
</script>
