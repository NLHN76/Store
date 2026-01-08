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