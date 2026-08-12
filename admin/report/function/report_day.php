<?php

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$sql = "SELECT DATE(order_date) AS order_day,
               COUNT(id) AS order_count,
               SUM(total_price) AS total_revenue
        FROM payment";

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'";
}

$sql .= " GROUP BY DATE(order_date)
          ORDER BY order_day DESC
          LIMIT 7";

$result = $conn->query($sql);

$order_dates = [];
$order_counts = [];
$total_revenues = [];

while ($row = $result->fetch_assoc()) {
    $order_dates[] = date('d/m/Y', strtotime($row['order_day']));
    $order_counts[] = $row['order_count'];
    $total_revenues[] = $row['total_revenue'];
}
?>