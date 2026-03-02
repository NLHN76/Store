/* ================== HELPER ================== */
function splitField(value) {
    if (!value) return [];
    return value.split(',').map(v => v.trim());
}

function createOrderCard(order) {
    const template = document.getElementById('order-card-template');
    const card = template.content.cloneNode(true);

    const status = statusMap[order.status] || {
        icon: "❓",
        class: "status-unknown"
    };

    const price = Number(order.total_price).toLocaleString('vi-VN');

    /* ===== ORDER INFO ===== */
    card.querySelector('.order-id').textContent = `Đơn Hàng: ${order.id}`;
    card.querySelector('.product-price').textContent = price;
    card.querySelector('.order-date').textContent = order.order_date;

    const statusEl = card.querySelector('.order-status');
    statusEl.className = `order-status ${status.class}`;
    statusEl.textContent = `${status.icon} ${order.status}`;

    /* ===== SPLIT PRODUCTS ===== */
    const names  = splitField(order.product_name);
    const colors = splitField(order.color);
    const images = splitField(order.image);

    let totalQuantity = 0;

    if (String(order.product_quantity).includes(',')) {
        splitField(order.product_quantity).forEach(q => {
            totalQuantity += Number(q) || 0;
        });
    } else {
        totalQuantity = Number(order.product_quantity) || 0;
    }

    card.querySelector('.total-quantity').textContent = totalQuantity;

    /* ===== RENDER PRODUCTS ===== */
    const productBox = card.querySelector('.order-products');
    productBox.innerHTML = '';

    names.forEach((name, index) => {
        const productEl = document.createElement('div');
        productEl.className = 'order-product-item';

        const imgSrc = images[index]
            ? `../../admin/uploads/${images[index]}`
            : '';

        productEl.innerHTML = `
            <img src="${imgSrc}" class="order-image" alt="${name}">
            <div class="order-product-info">
                <p><strong>Tên:</strong> ${name}</p>
                <p><strong>Màu:</strong> ${colors[index] || 'Không có màu'}</p>
            </div>
        `;

        productBox.appendChild(productEl);
    });

    /* ===== ACTIONS ===== */
    card.querySelector('.order-actions').innerHTML = renderActions(order);

    return card;
}