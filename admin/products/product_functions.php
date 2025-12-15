<?php
require_once "../../db.php";

// ================== HÀM DÙNG CHUNG ====================
function clean_price($price_string) {
    $cleaned = str_replace('.', '', $price_string);
    $cleaned = str_replace(',', '.', $cleaned);
    return is_numeric($cleaned) ? floatval($cleaned) : 0;
}

/**
 * Sinh mã sản phẩm KHÔNG TRÙNG (mysqli)
 */
function generate_product_code($category, $conn) {

    // Lấy 2 ký tự chữ đầu
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category), 0, 2));
    if (empty($prefix)) $prefix = 'SP';

    for ($i = 0; $i < 5; $i++) {

        $code = $prefix . rand(1000, 9999);

        // ⚠️ mysqli dùng ?
        $sql = "SELECT id FROM products WHERE product_code = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            return $code;
        }
    }

    // fallback nếu trùng liên tục
    return $prefix . strtoupper(substr(uniqid(), -6));
}

// =========== XỬ LÝ MÀU – ĐỌC FILE ===============
function load_colors() {
    $file = __DIR__ . '/colors_config.php';
    return file_exists($file) ? include $file : [];
}

function save_colors($colors) {
    $file = __DIR__ . '/colors_config.php';
    $content = "<?php\nreturn " . var_export($colors, true) . ";\n";
    file_put_contents($file, $content);
}
