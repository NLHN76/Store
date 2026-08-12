document.addEventListener("DOMContentLoaded", () => {

    // ==============================
    // DỮ LIỆU
    // ==============================
    const reportData = document.getElementById("report-data");

    if (!reportData) return;

    const labels = JSON.parse(
        reportData.dataset.dates || "[]"
    );

    const revenues = JSON.parse(
        reportData.dataset.revenues || "[]"
    );


    // ==============================
    // BIỂU ĐỒ
    // ==============================
    const canvas = document.getElementById("barChart");

    if (!canvas) return;

    new Chart(canvas.getContext("2d"), {

        type: "bar",

        data: {
            labels,

            datasets: [{
                label: "Doanh Thu (VNĐ)",
                data: revenues,

                backgroundColor: "rgba(255, 99, 132, 0.6)",
                borderColor: "rgba(255, 99, 132, 1)",
                borderWidth: 1,

                barThickness: 100,
                maxBarThickness: 30
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

});