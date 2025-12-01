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

// Lấy sản phẩm từ CSDL
$sql = "SELECT product_code, name, category, price FROM products ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

function format_price($price) {
    return number_format($price, 0, ',', '.') . " VNĐ";
}

// Xử lý form submit để trả về file HTML báo giá download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['products'])) {
    $customer_name = $_POST['customer_name'] ?? '';
    $quote_date = $_POST['quote_date'] ?? date('Y-m-d');
    $terms = $_POST['terms'] ?? '';
    $products_json = $_POST['products'];

    $products_post = json_decode($products_json, true);
    if (!is_array($products_post)) $products_post = [];

    $total_price = 0;
    foreach ($products_post as &$p) {
        $p['quantity'] = intval($p['quantity'] ?? 1);
        $p['price'] = floatval($p['price'] ?? 0);
        $p['subtotal'] = $p['price'] * $p['quantity'];
        $total_price += $p['subtotal'];
    }

    // Gửi header để trình duyệt tải file HTML báo giá về
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="bao_gia.html"');
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8" />
        <title>Báo giá sản phẩm</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #333; padding: 8px; text-align: center; }
            th { background-color: #f0f0f0; }
            p.info { margin: 5px 0; }
            .note { margin-top: 20px; font-style: italic; }
        </style>
    </head>
    <body>
        <h1>BÁO GIÁ SẢN PHẨM</h1>

        <div style="text-align: center;">
            <p><strong>MOBILE GEAR</strong></p>
            <p>Địa chỉ: Số 254 Tây Sơn - P. Trung Liệt - Q. Đống Đa - TP. Hà Nội</p>
            <p>Điện thoại: 0587.911.287</p>
            <p>Email: mobilegear@gmail.com</p>
        </div>

        <p class="info"><strong>Khách hàng:</strong> <?= htmlspecialchars($customer_name) ?></p>
        <p class="info"><strong>Ngày báo giá:</strong> <?= date('d/m/Y', strtotime($quote_date)) ?></p>

        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá (VNĐ)</th>
                    <th>Thành tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products_post as $i => $product): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= $product['quantity'] ?></td>
                        <td><?= format_price($product['price']) ?></td>
                        <td><?= format_price($product['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4"><strong>Tổng cộng</strong></td>
                    <td><strong><?= format_price($total_price) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <p class="note"><strong>Điều khoản:</strong></p>
        <p><?= nl2br(htmlspecialchars($terms)) ?></p>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Chỉnh sửa Báo giá sản phẩm</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        form { max-width: 800px; margin: auto; }
        label { display: block; margin: 15px 0 5px; }
        input[type=text], input[type=date], textarea {
            width: 100%; padding: 8px; box-sizing: border-box;
            font-size: 14px;
        }
        textarea { resize: vertical; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #eee; }
        button { padding: 8px 15px; margin-top: 15px; cursor: pointer; }
        input[type=number] { width: 60px; }
    </style>
</head>
<body>
    <h1>Chỉnh sửa Báo giá sản phẩm</h1>
    <form method="post" id="quote-form">
        <label for="customer_name">Tên khách hàng:</label>
        <input type="text" name="customer_name" id="customer_name" required>

        <label for="quote_date">Ngày báo giá:</label>
        <input type="date" name="quote_date" id="quote_date" value="<?= date('Y-m-d') ?>" required>
         
        <p class="note"><strong>Điều khoản:</strong></p>
        <textarea name="terms" id="terms" rows="4">Báo giá có hiệu lực trong 7 ngày kể từ ngày lập. 
Đã bao gồm VAT và chưa tính thêm phí vận chuyển.</textarea>

        <h3>Chọn sản phẩm báo giá:</h3>
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Loại</th>
                    <th>Giá (VNĐ)</th>
                    <th>Số lượng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products_db as $prod): ?>
                <tr>
                    <td><input type="checkbox" class="product-checkbox" data-name="<?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>" data-price="<?= $prod['price'] ?>"></td>
                    <td><?= htmlspecialchars($prod['product_code']) ?></td>
                    <td><?= htmlspecialchars($prod['name']) ?></td>
                    <td><?= htmlspecialchars($prod['category']) ?></td>
                    <td><?= format_price($prod['price']) ?></td>
                    <td><input type="number" class="product-qty" value="1" min="1" disabled></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" name="products" id="products-input">

        <button type="submit">Xuất Báo giá </button>
    </form>

    
    <script>
    const form = document.getElementById('quote-form');
    const productsInput = document.getElementById('products-input');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const qtyInputs = document.querySelectorAll('.product-qty');

    function updateSelectedProducts() {
        let products = [];
        checkboxes.forEach((chk, i) => {
            if (chk.checked) {
                const qty = parseInt(qtyInputs[i].value) || 1;
                products.push({
                    name: chk.dataset.name,
                    price: parseFloat(chk.dataset.price),
                    quantity: qty
                });
            }
        });
        productsInput.value = JSON.stringify(products);
    }

    checkboxes.forEach((chk, i) => {
        chk.addEventListener('change', () => {
            qtyInputs[i].disabled = !chk.checked;
            if (!chk.checked) qtyInputs[i].value = 1;
            updateSelectedProducts();
        });
    });

    qtyInputs.forEach((qty, i) => {
        qty.addEventListener('input', updateSelectedProducts);
    });

    // Cập nhật ban đầu
    updateSelectedProducts();
    </script>
</body>
</html>
