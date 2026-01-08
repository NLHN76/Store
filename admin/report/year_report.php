<?php

require_once "../../db.php" ;
require_once "function/report_year.php";

?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống Kê Doanh Thu Theo Năm</title>
    <link rel="icon" href="uploads/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
     <link rel="stylesheet" href="css/report_year.css">
</head>

<body>
    <a class="back-button" href="admin_report.php" title="Quay lại trang quản trị">
        <img src="../uploads/exit.jpg" alt="Quay lại"> </a>


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
