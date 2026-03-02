let allOrders = []; // Lưu tất cả đơn hàng

// Map trạng thái → icon + class CSS thuần
const statusMap = {
    "Chờ xử lý":        { icon: "🕒", class: "status-pending" },
    "Chờ thanh toán":   { icon: "🕓", class: "status-wait-payment" },
    "Đã thanh toán":    { icon: "✔️", class: "status-paid" },
    "Đang xử lý":       { icon: "⚠️", class: "status-processing" },
    "Đang giao hàng":   { icon: "🚚", class: "status-shipping" },
    "Đã giao hàng":     { icon: "✅", class: "status-delivered" },
    "Đã hủy":           { icon: "❌", class: "status-cancelled" }
};

// Fetch danh sách đơn hàng
async function fetchOrders() {
    const orderList = document.getElementById('order-list');

    try {
        const response = await fetch('order.php');
        const data = await response.json();

        if (data.status === 'error') {
            orderList.innerHTML = `
                <p class="message message-error">
                    ${data.message}
                </p>
            `;
            return;
        }

        allOrders = data;
        renderStatusFilters();
        renderOrders(allOrders);

    } catch (error) {
        console.error(error);
        orderList.innerHTML = `
            <p class="message message-error">
                Không thể tải thông tin đơn hàng.
            </p>
        `;
    }
}