<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    echo "<h2>Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.</h2>";
    exit;
}

// Thiết lập charset UTF-8
$conn->set_charset("utf8");

 
 
$search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : '';
// Lấy loại sản phẩm cần lọc từ GET (ví dụ: your_page.php?category=Electronics)
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';

// --- Xây dựng câu truy vấn SQL động ---
$sql = "SELECT id, product_code, name, price, image, category FROM products";
$where_clauses = [];  
$params = [];       
$types = "";         

// Luôn chỉ lấy sản phẩm đang hoạt động
$where_clauses[] = "is_active = 1";

// 1. Thêm điều kiện lọc theo category nếu có
if (!empty($selected_category)) {
    $where_clauses[] = "category = ?";
    $types .= "s"; // Kiểu string
    $params[] = $selected_category; // Thêm giá trị category vào mảng params
}

// 2. Thêm điều kiện tìm kiếm nếu có
if (!empty($search_query)) {
  
    if (!empty($selected_category)) {
        $where_clauses[] = "(name LIKE ? OR product_code LIKE ?)";
        $types .= "ss"; // 2 strings
        $search_term = "%" . $search_query . "%";
        $params[] = $search_term;
        $params[] = $search_term;
    } else {
        // Nếu không lọc category, tìm kiếm rộng hơn
        $where_clauses[] = "(name LIKE ? OR product_code LIKE ? OR category LIKE ?)";
        $types .= "sss"; // 3 strings
        $search_term = "%" . $search_query . "%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
}

// Kết hợp các điều kiện WHERE
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Thêm sắp xếp 
$sql .= " ORDER BY id DESC";

// --- Thực thi truy vấn với Prepared Statements ---
$products = [];
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Gắn tham số nếu có
    // Sử dụng toán tử spread (...) để truyền mảng $params vào bind_param
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Thực thi
    $stmt->execute();

    // Lấy kết quả
    $result = $stmt->get_result();

    // Xử lý kết quả
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Xử lý đường dẫn ảnh
            if (!empty($row['image'])) {
                 $row['image'] = 'admin/uploads/' . $row['image'];
            } else {
                $row['image'] = 'admin/uploads/placeholder.png'; // Ảnh mặc định
            }
            $products[] = $row;
        }
    }

    // Đóng câu lệnh
    $stmt->close();
} else {
    // Lỗi khi chuẩn bị câu lệnh
    error_log("SQL Prepare Error: " . $conn->error . " | SQL: " . $sql); // Log cả câu SQL để dễ debug
    echo "<h2>Có lỗi xảy ra trong quá trình xử lý yêu cầu.</h2>";
    $conn->close();
    exit;
}

// Đóng kết nối
$conn->close();

// --- Hiển thị kết quả HTML ---
if (!empty($products)) {
    foreach ($products as $product) {
        $product_code = urlencode($product['product_code']);
        echo '
            <div class="product" data-name="' . htmlspecialchars($product['name']) . '" data-price="' . $product['price'] . '" data-code="' . htmlspecialchars($product['product_code']) . '" data-id="' . $product['id'] .'">
                <img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '" onerror="this.onerror=null; this.src=\'admin/uploads/placeholder.png\';">
                <h3>' . htmlspecialchars($product['name']) . '</h3>
                <p><strong>Mã sản phẩm:</strong> ' . htmlspecialchars($product['product_code']) . '</p>
                <p><strong>Loại sản phẩm:</strong> ' . htmlspecialchars($product['category']) . '</p>
                <p>Giá: ' . number_format($product['price'], 0, ',', '.') . ' VNĐ</p>
                <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
                <a href="product_detail.php?code=' . $product_code . '">
                    <button>Xem chi tiết</button>
                </a>
            </div>';
    }
}
else {
    // Thông báo không tìm thấy phù hợp hơn
    if (!empty($selected_category) && !empty($search_query)) {
        echo '<h2>Không tìm thấy sản phẩm nào thuộc loại "' . htmlspecialchars($selected_category) . '" phù hợp với "' . htmlspecialchars($search_query) . '"!</h2>';
    } elseif (!empty($selected_category)) {
        echo '<h2>Không có sản phẩm nào thuộc loại "' . htmlspecialchars($selected_category) . '".</h2>';
    } elseif (!empty($search_query)) {
         echo '<h2>Không tìm thấy sản phẩm nào phù hợp với "' . htmlspecialchars($search_query) . '"!</h2>';
    } else {
        // Không lọc, không tìm kiếm, mà vẫn không có sản phẩm
        echo '<h2>Hiện tại cửa hàng chưa có sản phẩm nào (hoặc không có sản phẩm đang hoạt động).</h2>';
    }
}
?>