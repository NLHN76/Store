<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$sql = "SELECT DATE(order_date) AS order_day, 
               COUNT(id) AS order_count, 
               SUM(total_price) AS total_revenue 
        FROM payment";

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'";
}

$sql .= " GROUP BY DATE(order_date) ORDER BY order_day DESC";
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Doanh Thu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>


    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .filter-form { margin-bottom: 20px; }
        canvas { width: 100% !important; height: 500px !important; margin: auto; }
        .back-button {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-button img {
            width: 40px;
            height: 40px;
            cursor: pointer;
        }

    </style>
</head>

<body>
    <h1>Thống Kê Doanh Thu Theo Ngày</h1>
    <li><a href="report_client.php"><i class="fas fa-chart-bar"></i> Thống kê chi tiêu Khách hàng</a></li>
    <li><a href="report_year.php"><i class="fas fa-chart-bar"></i> Thống kê doanh thu năm  </a></li>
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
            <p style="text-align: center;">Địa chỉ: Số 254 Tây Sơn - P. Trung Liệt - Q. Đống Đa - TP. Hà Nội</p>
            <p style="text-align: center;">Điện thoại: 0587.911.287 | Email: mobilegear@gmail.com</p>
        </div>
    </div>


    
    <script>
  function exportPDF() {
    const element = document.getElementById('export-content');

    // Lấy ngày hiện tại dạng YYYYMMDD
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0'); // tháng từ 0-11 nên +1
    const day = String(now.getDate()).padStart(2, '0');
    const dateStr = `${year}${month}${day}`;

    // Tạo tên file theo yêu cầu
    const filename = `thong_ke_doanh_thu_theo_ngay_${dateStr}.pdf`;

    // Cấu hình PDF
    const opt = {
        margin: 0.5,
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    // Gọi html2pdf
    html2pdf().set(opt).from(element).save();
}
</script>


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
</body>
</html>
