/* ================== ACTION BUTTONS ================== */
function renderActions(order) {

    if (order.status === "Chờ xử lý") {
        return `
            <button class="order-btn order-btn-cancel"
                onclick="cancelOrder(${order.id})">
                Hủy đơn
            </button>
        `;
    }

    if (order.status === "Chờ thanh toán") {
        return `
            <button class="order-btn order-btn-pay"
                onclick="openPaymentModal(${order.id})">
                Thanh toán ngay
            </button>
        `;
    }

    if (["Đã hủy", "Đã giao hàng"].includes(order.status)) {
        return `
            <a href="reorder.php?payment_id=${order.id}"
               class="order-btn order-btn-reorder">
                Đặt lại đơn hàng
            </a>
        `;
    }

    return '';
}