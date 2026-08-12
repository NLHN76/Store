<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Chi Tiêu Khách Hàng</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <link rel="stylesheet" href="css/report_client.css">
</head>

<body>

<a href="day.php"
   class="back-button"
   title="Quay lại trang quản trị">
    <img src="../uploads/exit.jpg" alt="Quay lại">
</a>


<div id="export-content">

    <h1>Thống Kê Chi Tiêu Khách Hàng</h1>

    <?php if (!empty($customer_table_data)): ?>

        <!-- Biểu đồ -->
        <div class="chart-container">
            <canvas id="customerSpendChart"></canvas>
        </div>

        <!-- Bảng dữ liệu -->
        <h2>Bảng Dữ Liệu Chi Tiết</h2>

        <table>
            <thead>
                <tr>
                    <th>Mã Khách Hàng</th>
                    <th>Tên Khách Hàng</th>
                    <th>Số Lượng Đơn Hàng</th>
                    <th>Tổng Chi Tiêu (VNĐ)</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $max_spent = $customer_table_data[0]['total_spent'] ?? 0;

                foreach ($customer_table_data as $row):
                    $highlight = ($row['total_spent'] == $max_spent)
                        ? 'highlight'
                        : '';
                ?>

                    <tr class="<?= $highlight ?>">
                        <td><?= htmlspecialchars($row['user_code']); ?></td>

                        <td><?= htmlspecialchars($row['customer_name']); ?></td>

                        <td><?= htmlspecialchars($row['order_count']); ?></td>

                        <td>
                            <?= number_format(
                                $row['total_spent'],
                                0,
                                ',',
                                '.'
                            ); ?>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Thông tin cửa hàng -->
        <div class="invoice-box">
            <div class="contact-info">
                <p class="company-name">MOBILE GEAR</p>
            </div>
        </div>

        <!-- Dữ liệu truyền sang JavaScript -->
        <div id="chart-data"
             data-labels='<?= htmlspecialchars(
                 json_encode(
                     $chart_labels,
                     JSON_UNESCAPED_UNICODE
                 ),
                 ENT_QUOTES,
                 "UTF-8"
             ); ?>'
             data-values='<?= htmlspecialchars(
                 json_encode($chart_data),
                 ENT_QUOTES,
                 "UTF-8"
             ); ?>'>
        </div>

    <?php else: ?>

        <p class="no-data">
            Không có dữ liệu thống kê khách hàng để hiển thị.
        </p>

    <?php endif; ?>

</div>

<?php $conn->close(); ?>

<script src="js/report_client.js"></script>

</body>
</html>