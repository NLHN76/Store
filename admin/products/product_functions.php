<?php
// ================= KẾT NỐI DATABASE ==================
$dsn = 'mysql:host=localhost;dbname=store;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// ================== HÀM DÙNG CHUNG ====================
function clean_price($price_string) {
    $cleaned = str_replace('.', '', $price_string);
    $cleaned = str_replace(',', '.', $cleaned);
    return is_numeric($cleaned) ? floatval($cleaned) : 0;
}

function generate_product_code($category, $pdo) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category), 0, 2));
    if (empty($prefix)) $prefix = 'SP';

    for ($i = 0; $i < 5; $i++) {
        $code = $prefix . rand(1000, 9999);
        $stmt = $pdo->prepare("SELECT id FROM products WHERE product_code = :code");
        $stmt->execute(['code' => $code]);
        if (!$stmt->fetch()) return $code;
    }
    return $prefix . uniqid();
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
