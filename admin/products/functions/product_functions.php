<?php

require_once "../../db.php";



function clean_price($price_string) {

    $price_string = trim((string)$price_string);

    if ($price_string === '') {
        return false;
    }

    $cleaned = str_replace('.', '', $price_string);
    $cleaned = str_replace(',', '.', $cleaned);

    return is_numeric($cleaned) ? floatval($cleaned) : false;
}


// SINH MÃ SẢN PHẨM TỰ ĐỘNG
function generate_product_code($category, $conn) {

    $prefixes = [
        'Tai nghe' => 'TN',
        'Cáp sạc' => 'CS',
        'Ốp lưng' => 'OL',
        'Kính cường lực' => 'KC'
    ];

    $prefix = $prefixes[$category] ?? 'SP';

    for ($i = 0; $i < 5; $i++) {

        $code = $prefix . random_int(1000, 9999);

        $stmt = $conn->prepare(
            "SELECT id FROM products WHERE product_code = ?"
        );

        if (!$stmt) {
            throw new Exception(
                "Không thể kiểm tra mã sản phẩm."
            );
        }

        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();
            return $code;
        }

        $stmt->close();
    }

    return $prefix . strtoupper(substr(uniqid(), -6));
}


// ĐỌC DANH SÁCH MÀU
function load_colors() {

    $file = __DIR__ . '/colors_config.php';

    if (!file_exists($file)) {
        return [];
    }

    $colors = include $file;

    return is_array($colors) ? $colors : [];
}


// LƯU DANH SÁCH MÀU
function save_colors($colors) {

    $file = __DIR__ . '/colors_config.php';

    $content = "<?php\nreturn "
        . var_export(array_values($colors), true)
        . ";\n";

    return file_put_contents(
        $file,
        $content,
        LOCK_EX
    ) !== false;
}

?>