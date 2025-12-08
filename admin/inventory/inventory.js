function loadColors(productId){
    const colorSelect = document.getElementById('color');
    colorSelect.innerHTML = '<option value="">Chọn màu</option>';
    if(!productId) return;
    const productOption = document.querySelector('#product_id option[value="'+productId+'"]');
    if(!productOption) return;
    let raw = productOption.dataset.colors || '';
    raw = raw.trim();
    if(raw==='') return;
    let parts = raw.split(/[,;\n]+/).map(s=>s.trim()).filter(s=>s!=='');

    const seen = new Set();
    parts.forEach(c=>{
        if(!seen.has(c)){
            const opt = document.createElement('option');
            opt.value = c; opt.text = c;
            colorSelect.appendChild(opt);
            seen.add(c);
        }
    });
}

// Cập nhật giá trị input vào hidden trước khi submit form
function updateHidden(productId,color){
    const val = document.getElementById(`stock_${productId}_${color}`).value;
    document.getElementById(`hidden_${productId}_${color}`).value = val;
}

document.querySelectorAll('.low-stock-link').forEach(link => {
    link.addEventListener('click', function(e){
        e.preventDefault();
        const targetId = this.dataset.target;
        const targetRow = document.querySelector(targetId);
        if(targetRow){
            // Mở collapse parent nếu đang ẩn
            const collapseEl = bootstrap.Collapse.getOrCreateInstance(targetRow);
            collapseEl.show();

            // Scroll tới dòng
            targetRow.scrollIntoView({behavior: "smooth", block: "center"});

            // Highlight nhẹ để dễ nhận biết
            targetRow.style.transition = "background-color 0.5s";
            const originalBg = targetRow.style.backgroundColor;
            targetRow.style.backgroundColor = "#fff3cd"; // màu vàng nhạt
            setTimeout(()=>{ targetRow.style.backgroundColor = originalBg; }, 1500);
        }
    });
});