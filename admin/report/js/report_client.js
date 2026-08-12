document.addEventListener("DOMContentLoaded", () => {

    const chartData = document.getElementById("chart-data");
    const canvas = document.getElementById("customerSpendChart");

    if (!chartData || !canvas) return;

    const labels = JSON.parse(chartData.dataset.labels || "[]");
    const values = JSON.parse(chartData.dataset.values || "[]");

    new Chart(canvas.getContext("2d"), {
        type: "bar",

        data: {
            labels,

            datasets: [{
                label: "Tổng Chi Tiêu (VNĐ)",
                data: values,
                backgroundColor: "rgba(75, 192, 192, 0.6)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 1,
                hoverBackgroundColor: "rgba(75, 192, 192, 0.8)",
                barPercentage: 0.2
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,

            scales: {
                y: {
                    beginAtZero: true,

                    ticks: {
                        callback: value =>
                            value.toLocaleString("vi-VN") + " VNĐ"
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
                    text: "Tổng Chi Tiêu Của Từng Khách Hàng",
                    font: {
                        size: 16
                    }
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