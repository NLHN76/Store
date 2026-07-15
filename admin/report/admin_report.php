<?php
require_once "../../db.php" ;
require_once "function/report_day.php" ;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Doanh Thu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
       <link rel="stylesheet" href="css/report_day.css">
</head>

<body>
    <h1>Thống Kê Doanh Thu Theo Ngày</h1>
    <li><a href="client_report.php"><i class="fas fa-chart-bar"></i> Thống kê chi tiêu Khách hàng</a></li>
    <li><a href="year_report.php"><i class="fas fa-chart-bar"></i> Thống kê doanh thu năm  </a></li>
    <a href="../admin_interface.php" class="back-button" title="Quay lại trang quản trị">
            <img src="../uploads/exit.jpg" alt="Quay lại"> 
        </a>
    <form method="GET" class="filter-form">
        <label for="start_date">Từ ngày:</label>
        <input type="date" id="start_date" name="start_date" value="<?= $start_date; ?>">
        <label for="end_date">Đến ngày:</label>
        <input type="date" id="end_date" name="end_date" value="<?= $end_date; ?>">
        <button type="submit">Lọc</button>
    </form>

    <button onclick="exportPDF()">Xuất PDF</button>

    <div id="export-content">
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Số Lượng Đơn Hàng</th>
                    <th>Doanh Thu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_dates as $index => $order_date): ?>
                <tr>
                    <td><?= $order_date; ?></td>
                    <td><?= number_format($order_counts[$index], 0, ',', '.'); ?></td>
                    <td><?= number_format($total_revenues[$index], 0, ',', '.'); ?> VNĐ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Không có đơn hàng nào.</p>
    <?php endif; ?>

    <canvas id="barChart"></canvas>



    <div class='invoice-box'>
        <div class='contact-info'>
            <p class='company-name'>MOBILE GEAR</p>
        </div>
    </div>


    


    <script>
        const labels = <?= json_encode($order_dates); ?>;
        const totalRevenues = <?= json_encode($total_revenues); ?>;

        const ctx = document.getElementById('barChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh Thu (VNĐ)',
                    data: totalRevenues,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    barThickness: 100,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

    <?php $conn->close(); ?>
    
<script src="js/report_day.js"></script>
</body>
</html>
