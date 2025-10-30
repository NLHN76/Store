<?php
// --- 1. Kết nối CSDL ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Lỗi kết nối đến cơ sở dữ liệu.");
}
$conn->set_charset("utf8mb4");

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



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống Kê Doanh Thu Theo Năm</title>
    <link rel="icon" href="uploads/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 30px auto; padding: 25px; background-color: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.1); border-radius: 8px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        h2 { text-align: center; color: #555; margin-top: 30px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        td:last-child { text-align: right; font-weight: bold; }
        tr:nth-child(even) td { background-color: #f9f9f9; }
        tr:hover td { background-color: #f1f1f1; }
        .chart-container { width: 100%; max-width: 850px; height: 400px; margin: 30px auto; padding: 15px; background: #fff; border: 1px solid #eee; border-radius: 5px; }
        .back-button { position: absolute; top: 15px; left: 15px; }
        .back-button img { width: 40px; height: 40px; cursor: pointer; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: 0.2s; }
        .back-button img:hover { transform: scale(1.1); }
        .no-data { text-align: center; padding: 30px; font-size: 1.1em; color: #555; background: #fff; border: 1px dashed #ccc; margin-top: 20px; }
        form { text-align: center; margin-bottom: 20px; }
        input[type="number"] { padding: 5px 10px; font-size: 1em; width: 100px; }
        button { padding: 6px 12px; font-size: 1em; cursor: pointer; }
        .clear-filter { margin-left: 10px; text-decoration: none; color: red; }
    </style>
</head>

<body>
    <a class="back-button" href="admin_report.php" title="Quay lại trang quản trị">
        <img src="uploads/exit.jpg" alt="Quay lại"> </a>


    <div class="container">
        <form method="GET">
            <label for="year">Xem doanh thu theo năm:</label>
            <input type="number" name="year" id="year" min="2000" max="2100" value="<?= htmlspecialchars($selected_year ?? '') ?>">
            <button type="submit">Tra cứu</button>
        </form>


        <h1>
            <?= $selected_year ? "Doanh Thu Năm $selected_year" : "Thống Kê Doanh Thu Năm" ?>
        </h1>
     
        <button onclick="exportPDF()">Xuất PDF</button>



        <div id="export-content">
        <?php if (!empty($revenue_data)): ?>
       
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
                   <td style="text-align: center;"><?= htmlspecialchars($row['revenue_year']); ?></td>
                   <td><?= number_format($row['total_revenue'], 0, ',', '.'); ?></td>
               </tr>
               <?php endforeach; ?>
           </tbody>
       </table>


       
    <div class='invoice-box'>
        <div class='contact-info'>
            <p style="text-align: center;" class='company-name'>MOBILE GEAR</p>
            <p style="text-align: center;">Địa chỉ: Số 254 Tây Sơn - P. Trung Liệt - Q. Đống Đa - TP. Hà Nội</p>
            <p style="text-align: center;">Điện thoại: 0587.911.287 | Email: mobilegear@gmail.com</p>
        </div>
    </div>

        </div>
    


            
        <script>
    function exportPDF() {
        const element = document.getElementById('export-content');

        // Cấu hình PDF
        const opt = {
            margin:       0.5,
            filename: 'thong_ke_doanh_thu_nam_' + (<?= json_encode($selected_year ?? 'tat_ca'); ?>) + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        // Gọi html2pdf
        html2pdf().set(opt).from(element).save();
    }
     </script>



            <script>
                const ctx = document.getElementById('revenueChart').getContext('2d');
                const chartLabels = <?= json_encode($chart_labels); ?>;
                const chartValues = <?= json_encode($chart_values); ?>;

                const data = {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Doanh Thu Năm (VNĐ)',
                        data: chartValues,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.2,
                        fill: true,
                        pointBackgroundColor: 'rgb(75, 192, 192)',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                };

                const config = {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('vi-VN') + ' VNĐ';
                                    }
                                },
                                title: { display: true, text: 'Tổng Doanh Thu' }
                            },
                            x: {
                                title: { display: true, text: 'Năm' }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            title: {
                                display: true,
                                text: 'Biểu Đồ Doanh Thu Theo Năm',
                                font: { size: 16 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' +
                                            context.parsed.y.toLocaleString('vi-VN') + ' VNĐ';
                                    }
                                }
                            }
                        }
                    }
                };

                new Chart(ctx, config);
            </script>
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
</body>
</html>
