<?php
session_start(); // Bắt buộc để dùng session
$is_logged_in = isset($_SESSION['user_id']); // Kiểm tra người dùng đã đăng nhập

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    echo "<h2>Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.</h2>";
    exit;
}
$conn->set_charset("utf8");

// Lấy dữ liệu tìm kiếm
$search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : '';
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';

// --- Xây dựng câu truy vấn SQL động ---
$sql = "SELECT id, product_code, name, price, image, category FROM products";
$where_clauses = ["is_active = 1"];
$params = [];
$types = "";

// Lọc theo category
if (!empty($selected_category)) {
    $where_clauses[] = "category = ?";
    $types .= "s";
    $params[] = $selected_category;
}

// Tìm kiếm
if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";
    if (!empty($selected_category)) {
        $where_clauses[] = "(name LIKE ? OR product_code LIKE ?)";
        $types .= "ss";
        $params[] = $search_term;
        $params[] = $search_term;
    } else {
        $where_clauses[] = "(name LIKE ? OR product_code LIKE ? OR category LIKE ?)";
        $types .= "sss";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY id DESC";

// --- Thực thi truy vấn ---
$products = [];
$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['image'] = !empty($row['image']) ? 'admin/uploads/' . $row['image'] : 'admin/uploads/placeholder.png';
        $products[] = $row;
    }

    $stmt->close();
} else {
    error_log("SQL Prepare Error: " . $conn->error . " | SQL: " . $sql);
    echo "<h2>Có lỗi xảy ra trong quá trình xử lý yêu cầu.</h2>";
    $conn->close();
    exit;
}

$conn->close();

// --- Hiển thị kết quả ---
if (!empty($products)) {
    foreach ($products as $product) {
        $product_code = urlencode($product['product_code']);
        $detail_link = $is_logged_in ? "product_detail.php?code={$product_code}" : "no_feedback.php";

        echo '
            <div class="product" data-name="' . htmlspecialchars($product['name']) . '" data-price="' . $product['price'] . '" data-code="' . htmlspecialchars($product['product_code']) . '" data-id="' . $product['id'] .'">
                <img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '" onerror="this.onerror=null; this.src=\'admin/uploads/placeholder.png\';">
                <h3>' . htmlspecialchars($product['name']) . '</h3>
                <p><strong>Mã sản phẩm:</strong> ' . htmlspecialchars($product['product_code']) . '</p>
                <p><strong>Loại sản phẩm:</strong> ' . htmlspecialchars($product['category']) . '</p>
                <p>Giá: ' . number_format($product['price'], 0, ',', '.') . ' VNĐ</p>
                <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
                <a href="' . $detail_link . '">
                    <button>Xem chi tiết</button>
                </a>
            </div>';
    }
} else {
    if (!empty($selected_category) && !empty($search_query)) {
        echo '<h2>Không tìm thấy sản phẩm nào thuộc loại "' . htmlspecialchars($selected_category) . '" phù hợp với "' . htmlspecialchars($search_query) . '"!</h2>';
    } elseif (!empty($selected_category)) {
        echo '<h2>Không có sản phẩm nào thuộc loại "' . htmlspecialchars($selected_category) . '".</h2>';
    } elseif (!empty($search_query)) {
        echo '<h2>Không tìm thấy sản phẩm nào phù hợp với "' . htmlspecialchars($search_query) . '"!</h2>';
    } else {
        echo '<h2>Hiện tại cửa hàng chưa có sản phẩm nào (hoặc không có sản phẩm đang hoạt động).</h2>';
    }
}
?>
