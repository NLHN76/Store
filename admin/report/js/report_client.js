function exportPDF() {

  
    replaceChartWithImage();

    setTimeout(() => {
        const element = document.getElementById('export-content');

        const opt = {
            margin: 10,
            filename: 'Thong_ke_chi_tieu_khach_hang.pdf',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: {
                scale: 3,
                useCORS: true
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            },
            pagebreak: {
                mode: ['css', 'legacy']
            }
        };

        html2pdf().set(opt).from(element).save();
    }, 300);
}


function replaceChartWithImage() {
    const canvas = document.getElementById('customerSpendChart');
    if (!canvas) return;

    const img = new Image();
    img.src = canvas.toDataURL("image/png", 1);
    img.style.width = '100%';
    img.style.height = 'auto';

    const wrapper = document.getElementById('chart-wrapper');
    wrapper.innerHTML = '';
    wrapper.appendChild(img);
}
