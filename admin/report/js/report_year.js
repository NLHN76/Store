document.addEventListener("DOMContentLoaded", () => {
    const chartData = document.getElementById("chart-data");
    const canvas = document.getElementById("revenueChart");

    if (!chartData || !canvas) return;

    const labels = JSON.parse(chartData.dataset.labels || "[]");
    const values = JSON.parse(chartData.dataset.values || "[]");

    new Chart(canvas.getContext("2d"), {
        type: "line",
        data: {
            labels,
            datasets: [{
                label: "Doanh Thu Năm (VNĐ)",
                data: values,
                borderColor: "rgb(75, 192, 192)",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                tension: 0.2,
                fill: true,
                pointBackgroundColor: "rgb(75, 192, 192)",
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => value.toLocaleString("vi-VN") + " VNĐ"
                    },
                    title: {
                        display: true,
                        text: "Tổng Doanh Thu"
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: "Năm"
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: "top"
                },
                title: {
                    display: true,
                    text: "Biểu Đồ Doanh Thu Theo Năm",
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: context =>
                            `${context.dataset.label}: ${context.parsed.y.toLocaleString("vi-VN")} VNĐ`
                    }
                }
            }
        }
    });
});