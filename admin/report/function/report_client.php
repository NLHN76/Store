<?php


// Lấy Top 10 khách hàng chi tiêu nhiều nhất
$sql = "SELECT
            user_code,
            MAX(customer_name) AS customer_name,
            COUNT(*) AS order_count,
            SUM(total_price) AS total_spent
        FROM
            payment
        WHERE
            user_code IS NOT NULL AND user_code <> ''
        GROUP BY
            user_code
        ORDER BY
            total_spent DESC, order_count DESC
        LIMIT 10"; 

$result = $conn->query($sql);

$chart_labels = []; 
$chart_data = [];   
$customer_table_data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customer_table_data[] = $row;

        // Dữ liệu cho chart
        $chart_labels[] = $row['customer_name'] . ' (' . $row['user_code'] . ')'; 
        $chart_data[] = $row['total_spent'];
    }
}
?>