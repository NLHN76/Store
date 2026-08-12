<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống Kê Doanh Thu Theo Năm</title>
    <link rel="icon" href="uploads/favicon.ico">
    <link rel="stylesheet" href="css/report_year.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <a class="back-button" href="day.php" title="Quay lại trang quản trị">
        <img src="../uploads/exit.jpg" alt="Quay lại">
    </a>

    <div class="container">
        <form method="GET">
            <label for="year">Xem doanh thu theo năm:</label>
            <input type="number" name="year" id="year" min="2000" max="2100"
                   value="<?= htmlspecialchars($selected_year ?? '') ?>">
            <button type="submit">Tra cứu</button>
        </form>

        <h1><?= $selected_year ? "Doanh Thu Năm $selected_year" : "Thống Kê Doanh Thu Năm" ?></h1>

        <?php if (!empty($revenue_data)): ?>

            <div id="chart-data"
                 data-labels='<?= htmlspecialchars(json_encode($chart_labels, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                 data-values='<?= htmlspecialchars(json_encode($chart_values), ENT_QUOTES, "UTF-8") ?>'>
            </div>

            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>

            <h2>Bảng Dữ Liệu Chi Tiết</h2>

            <table>
                <thead>
                    <tr>
                        <th>Năm</th>
                        <th>Tổng Doanh Thu (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenue_data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['revenue_year']) ?></td>
                            <td><?= number_format($row['total_revenue'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="invoice-box">
                <div class="contact-info">
                    <p class="company-name">MOBILE GEAR</p>
                </div>
            </div>

        <?php else: ?>
            <p class="no-data">Không có dữ liệu doanh thu để hiển thị.</p>
        <?php endif; ?>
    </div>

    <?php
    if ($result instanceof mysqli_result) {
        $result->free();
    }
    $conn->close();
    ?>

    <script src="js/report_year.js"></script>
</body>
</html>