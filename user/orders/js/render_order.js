/* ================== RENDER ORDERS ================== */
function renderOrders(orders) {
    const orderList = document.getElementById('order-list');
    orderList.innerHTML = '';

    if (!orders || orders.length === 0) {
        orderList.innerHTML = `
            <p class="message message-empty">
                Không có đơn hàng nào
            </p>
        `;
        return;
    }

    [...orders]
        .sort((a, b) => b.id - a.id)
        .forEach(order => {
            orderList.appendChild(createOrderCard(order));
        });
}