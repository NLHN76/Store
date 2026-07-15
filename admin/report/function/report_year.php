<?php
// --- 1. Kết nối CSDL ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- 2. Xử lý truy vấn theo năm ---
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : null;

if ($selected_year) {
    $sql = "SELECT
                YEAR(order_date) AS revenue_year,
                SUM(total_price) AS total_revenue
            FROM
                payment
            WHERE
                YEAR(order_date) = $selected_year
            GROUP BY
                revenue_year";
} else {
    $sql = "SELECT
                YEAR(order_date) AS revenue_year,
                SUM(total_price) AS total_revenue
            FROM
                payment
            WHERE
                order_date IS NOT NULL
            GROUP BY
                revenue_year
            ORDER BY
                revenue_year ASC";
}

$result = $conn->query($sql);
$revenue_data = [];
$chart_labels = [];
$chart_values = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $revenue_data[] = $row;
        $chart_labels[] = $row['revenue_year'];
        $chart_values[] = $row['total_revenue'];
    }
} elseif (!$result) {
    error_log("Error fetching yearly revenue: " . $conn->error);
}
?>
