// ================= GIỎ HÀNG TOÀN CỤC =================
let cart = [];
let totalPrice = 0;
let allProducts = [];

// ================= THÊM VÀO GIỎ =================
function addToCart(btn) {
    const p = btn.parentElement;

    const name = p.dataset.name;
    const price = parseFloat(p.dataset.price);
    const code = p.dataset.code;
    const color = p.querySelector('.color-select')?.value || null;

    const product = allProducts.find(pr => pr.product_code === code);
    const stockQty = product?.stockByColor[color] || 0;

    if (stockQty <= 0) {
        alert('Sản phẩm này đã hết hàng!');
        return;
    }

    fetch('cart/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_code: code,
            color: color,
            quantity: 1,
            price: price
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.error || 'Không thêm được vào giỏ');
            return;
        }
        showNotification(`Đã thêm "${name}" (${color}) vào giỏ hàng!`);
        loadCart();
        showSection('cart');
    });
}

// ================= LOAD GIỎ HÀNG TỪ DB =================
function loadCart() {
    fetch('cart/get_cart.php')
        .then(res => res.json())
        .then(data => {
            cart = data;
            updateCartDisplay();
        });
}

document.addEventListener('DOMContentLoaded', loadCart);

// ================= HIỂN THỊ THÔNG BÁO =================
function showNotification(text) {
    notification.textContent = text;
    notification.style.display = 'block';
    clearTimeout(notification.timer);
    notification.timer = setTimeout(() => {
        notification.style.display = 'none';
    }, 1500);
}


// ================= HIỂN THỊ GIỎ HÀNG =================
function updateCartDisplay() {
    const cartDiv = document.getElementById('cart-items');
    cartDiv.innerHTML = '';

    let count = 0;
    let total = 0;

    if (cart.length === 0) {
        cartDiv.innerHTML = '<p>Giỏ hàng trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';

        document.getElementById('total-quantity').textContent = 'Tổng số sản phẩm: 0';
        document.getElementById('total-price').textContent = 'Tổng giá trị: 0 VNĐ';
        return;
    }

    cart.forEach(item => {
        const div = document.createElement('div');
        div.classList.add('cart-item');

        div.innerHTML = `
            <img src="../admin/uploads/${item.image}"
         style="width:100px;height:100px;margin-right:10px;">
            <span>
                ${item.name} - <em>${item.color}</em>
                (x${item.quantity}):
                ${(item.price * item.quantity).toLocaleString('vi-VN')} VNĐ
            </span>
        `;

        ['-', '+', 'Xóa'].forEach(action => {
            const btn = document.createElement('button');
            btn.textContent = action;

            btn.onclick = () => {
                const actionType =
                    action === '+' ? 'increase' :
                    action === '-' ? 'decrease' :
                    'remove';

                fetch('cart/update_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: item.product_id,
                        color: item.color,
                        action: actionType
                    })
                })
                .then(res => res.json())
                .then(() => loadCart());
            };

            div.appendChild(btn);
        });

        cartDiv.appendChild(div);

        count += item.quantity;
        total += item.price * item.quantity;
    });


    document.getElementById('checkout').style.display = 'block';
    document.getElementById('total-quantity').textContent =
        'Tổng số sản phẩm: ' + count;
    document.getElementById('total-price').textContent =
        'Tổng giá trị: ' + total.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('cart-quantity').textContent = count;
}



