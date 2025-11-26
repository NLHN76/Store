document.addEventListener("DOMContentLoaded", function() {
    // Format giá
    function formatNumberInput(input) {
        let value = input.value.replace(/[^0-9]/g,'');
        let number = parseInt(value,10);
        input.value = isNaN(number)?'':number.toLocaleString('vi-VN');
    }
    document.querySelectorAll('input[name="product_price"]').forEach(input=>{
        if(input.value) formatNumberInput(input);
        input.addEventListener('input',()=>formatNumberInput(input));
        input.addEventListener('blur',()=>formatNumberInput(input));
    });

    // Thông báo trạng thái ẩn sau 3.5s
    const statusMessage = document.getElementById('status-message');
    if(statusMessage){
        setTimeout(()=>{statusMessage.style.opacity='0'; setTimeout(()=>statusMessage.remove(),500);},3500);
    }

    // Xác nhận bật/tắt sản phẩm
    document.querySelectorAll('.toggle-form').forEach(form=>{
        form.addEventListener('submit',function(e){
            const button = form.querySelector('button');
            const name = form.closest('.product-box').querySelector('h4').textContent;
            const isActive = button.textContent.includes('TẮT');
            if(!confirm(`Bạn có chắc muốn ${isActive?'TẮT':'BẬT'} sản phẩm "${name}"?`)) e.preventDefault();
        });
    });

    // Modal thêm sản phẩm
    const toggleBtn=document.getElementById('toggle-add-form-btn');
    const modal=document.getElementById('add-product-modal');
    const overlay=document.getElementById('modal-overlay');
    const closeBtn=document.getElementById('close-modal-btn');
    if(toggleBtn&&modal&&overlay){
        toggleBtn.addEventListener('click',()=>{
            overlay.style.display='block'; modal.style.display='block';
        });
        closeBtn.addEventListener('click',()=>{modal.style.display='none'; overlay.style.display='none';});
        overlay.addEventListener('click', e=>{if(e.target===overlay){modal.style.display='none'; overlay.style.display='none';}});
        document.addEventListener('keydown', e=>{if(e.key==='Escape'&&modal.style.display==='block'){modal.style.display='none'; overlay.style.display='none';}});
    }

    // Toggle panel màu sắc
    const colorBtn=document.getElementById('toggle-color-panel');
    const colorPanel=document.getElementById('color-panel');
    if(colorBtn&&colorPanel){
        colorBtn.addEventListener('click',()=>{
            const isOpen=colorPanel.style.display==='block';
            colorPanel.style.display=isOpen?'none':'block';
            colorBtn.textContent=isOpen?'⚙ Mở Quản Lý Màu Sắc':'❌ Đóng Quản Lý Màu Sắc';
        });
    }
});

