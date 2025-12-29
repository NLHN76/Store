function loadColors(productId) {
    const colorSelect = document.getElementById('color');

    // Reset select màu
    colorSelect.innerHTML = '<option value="">Chọn màu</option>';

    if (!productId) return;

    const productOption = document.querySelector(`#product_id option[value="${productId}"]`);
    if (!productOption) return;

    const colorsRaw = productOption.dataset.colors || '';
    if (!colorsRaw) return;

    // Tách màu, loại bỏ trùng
    const colors = Array.from(new Set(
        colorsRaw.split(/[,;\n|]+/).map(c => c.trim()).filter(c => c)
    ));

    // Thêm option màu
    colors.forEach(color => {
        const opt = document.createElement('option');
        opt.value = color;
        opt.text = color;
        colorSelect.appendChild(opt);
    });
}


function updateHidden(productId, colorKey) {
    const stockInput = document.getElementById(`stock_${productId}_${colorKey}`);
    const hiddenInput = document.getElementById(`hidden_${productId}_${colorKey}`);

    if (!stockInput || !hiddenInput) {
        console.error("Không tìm thấy input lưu tồn kho");
        return;
    }

    hiddenInput.value = stockInput.value;
}




document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".low-stock-link").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            const rowKey = this.dataset.target;
            const row = document.querySelector(`[data-row-id="${rowKey}"]`);
            if (!row) return;

            const collapse = row.closest(".collapse");
            if (collapse && !collapse.classList.contains("show")) {
                new bootstrap.Collapse(collapse, { show: true });
            }

            setTimeout(() => {
                row.scrollIntoView({ behavior: "smooth", block: "center" });
                row.classList.add("table-warning");
                setTimeout(() => row.classList.remove("table-warning"), 2000);
            }, 300);
        });
    });
});
