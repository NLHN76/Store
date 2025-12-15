
// ================= GIỎ HÀNG =================
function addToCart(btn) {
    const p = btn.parentElement;
    const name = p.dataset.name;
    const price = parseFloat(p.dataset.price);
    const code = p.dataset.code;
    const image = p.querySelector('img').src;
    const color = p.querySelector('.color-select')?.value || 'Không có màu';

    const product = allProducts.find(p => p.product_code === code);
    const stockQty = product?.stockByColor[color] || 0;

    const existing = cart.find(i => i.name === name && i.color === color);
    if (existing && existing.quantity >= stockQty) { 
        alert('Số lượng vượt quá tồn kho!'); 
        return; 
    }
    if (existing) existing.quantity++; 
    else if (stockQty > 0) cart.push({ name, color, price, quantity:1, image });
    else { 
        alert('Sản phẩm này đã hết hàng!'); 
        return; 
    }

    showNotification(`Đã thêm "${name}" (${color}) vào giỏ hàng!`);
    updateCartDisplay();
    showSection('cart');
}

function showNotification(text) {
    notification.textContent = text;
    notification.style.display = 'block';
    clearTimeout(notification.timer);
    notification.timer = setTimeout(() => notification.style.display = 'none', 1500);
}

function updateCartDisplay() {
    const cartDiv = document.getElementById('cart-items');
    cartDiv.innerHTML = '';
    let count = 0, total = 0;

    if (cart.length === 0) {
        cartDiv.innerHTML = '<p>Giỏ hàng trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';
        document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: 0';
        document.getElementById('total-price').textContent = '0 VNĐ';
        return;
    }

    cart.forEach(item => {
        const div = document.createElement('div');
        div.classList.add('cart-item');

        div.innerHTML = `<img src="${item.image}" style="width:100px; height:100px; margin-right:10px;">
                         <span>${item.name} - <em>${item.color}</em> (x${item.quantity}): ${(item.price*item.quantity).toLocaleString('vi-VN')} VNĐ</span>`;
        
        ['-', '+', 'Xóa'].forEach(action => {
            const btn = document.createElement('button');
            btn.textContent = action;

            btn.onclick = () => {
                const product = allProducts.find(p => p.name === item.name);
                const stockQty = product?.stockByColor[item.color] ?? Infinity;

                if (action === '+') {
                    if (item.quantity < stockQty) item.quantity++;
                    else { alert('Số lượng vượt quá tồn kho!'); return; }
                } else if (action === '-') {
                    if (item.quantity > 1) item.quantity--;
                    else cart.splice(cart.indexOf(item),1);
                } else { // Xóa
                    cart.splice(cart.indexOf(item),1);
                }
                updateCartDisplay();
            };
            div.appendChild(btn);
        });

        cartDiv.appendChild(div);
        count += item.quantity;
        total += item.price * item.quantity;
    });

    totalPrice = total;
    document.getElementById('checkout').style.display = 'block';
    document.getElementById('total-price').textContent = total.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: ' + count;
    document.getElementById('cart-quantity').textContent = count;


}

