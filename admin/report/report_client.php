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
            total_spent DESC, order_count DESC";

$result = $conn->query($sql);


$chart_labels = []; 
$chart_data = [];   
$customer_table_data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
       
        $customer_table_data[] = $row;

      
        $chart_labels[] = $row['customer_name'] . ' (' . $row['user_code'] . ')'; 
        $chart_data[] = $row['total_spent'];
    }
   
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Chi Tiêu Khách Hàng</title>
   
   <a class="back-button" href="admin_report.php" title="Quay lại trang quản trị">
        <img src="../uploads/exit.jpg" alt="Quay lại">
    </a>
   
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <link rel="stylesheet" href="css/report_client.css">
</head>
<body>

<button onclick="exportPDF()">Xuất PDF</button>

     
<div id="export-content">
    <h1>Thống Kê Chi Tiêu Khách Hàng</h1>

    <?php if (!empty($customer_table_data)): ?>

        <!-- --- Chart Canvas Container --- -->
   
        <div class="chart-container">
            <canvas id="customerSpendChart"></canvas>
        </div>

        <!-- --- Data Table --- -->
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
                $first_row = true;
                $max_spent = 0;
                if (!empty($customer_table_data)) {
                    $max_spent = $customer_table_data[0]['total_spent']; 
                }

                foreach ($customer_table_data as $row):
                    $row_class = ($row["total_spent"] == $max_spent) ? 'highlight' : '';
                ?>
                <tr class="<?= $row_class ?>">
                    <td><?= htmlspecialchars($row["user_code"]); ?></td>
                    <td><?= htmlspecialchars($row["customer_name"]); ?></td>
                    <td><?= htmlspecialchars($row["order_count"]); ?></td>
                    <td><?= number_format($row["total_spent"], 0, ',', '.'); ?></td>
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
            filename:     'thong_ke_chi_tieu_khach_hang.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        // Gọi html2pdf
        html2pdf().set(opt).from(element).save();
    }
</script>




        <script>
            const ctx = document.getElementById('customerSpendChart').getContext('2d');
            const chartLabels = <?php echo json_encode($chart_labels); ?>;
            const chartDataPoints = <?php echo json_encode($chart_data); ?>;
            const data = {
              labels: chartLabels,
              datasets: [{ 
                label: 'Tổng Chi Tiêu (VNĐ)',
                data: chartDataPoints,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                hoverBackgroundColor: 'rgba(75, 192, 192, 0.8)',
                barPercentage: 0.2,       

              }]
            };

            const config = {
              type: 'bar',
              data: data,
              options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: {
                       callback: function(value, index, values) {
                           return value.toLocaleString('vi-VN') + ' VNĐ';
                       }
                    }
                  },
                  x: {
                     ticks: {
                     
                    }
                  }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Tổng Chi Tiêu Của Từng Khách Hàng',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('vi-VN') + ' VNĐ';
                                }
                                return label;
                            }
                        }
                    }
                }
              }
            };

            
            const customerSpendChart = new Chart(ctx, config);
        </script>

    <?php else: ?>
        <p class="no-data">Không có dữ liệu thống kê khách hàng để hiển thị.</p>
    <?php endif; ?>

    <?php
    
    $conn->close();
    ?>

</body>
</html>