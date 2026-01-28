/* ================== ACTION BUTTONS ================== */
function renderActions(order) {
    if (order.status === "Chờ xử lý") {
        return `
            <button class="mt-2 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                onclick="cancelOrder(${order.id})">
                Hủy đơn
            </button>`;
    }

    if (order.status === "Chờ thanh toán") {
        return `
            <button class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                onclick="openPaymentModal(${order.id})">
                Thanh toán ngay
            </button>`;
    }

    if (["Đã hủy", "Đã giao hàng"].includes(order.status)) {
        return `
            <a href="reorder.php?payment_id=${order.id}"
               class="inline-block mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Đặt lại đơn hàng
            </a>`;
    }

    return '';
}
