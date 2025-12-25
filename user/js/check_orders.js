async function checkUnpaidOrdersForMenu() {
    try {
        const res = await fetch('orders/order.php'); 
        const data = await res.json();

        if (!Array.isArray(data)) return;

        const hasUnpaid = data.some(order => order.status === "Chờ thanh toán");

        // ❗ ở mục Đơn Hàng
        const orderAlert = document.getElementById('order-alert');

        // ❗ ở chữ Tài Khoản
        const accountAlert = document.getElementById('account-order-alert');

        if (hasUnpaid) {
            orderAlert?.classList.remove('hidden');
            accountAlert?.classList.remove('hidden');
        } else {
            orderAlert?.classList.add('hidden');
            accountAlert?.classList.add('hidden');
        }

    } catch (err) {
        console.error("Lỗi kiểm tra đơn chưa thanh toán", err);
    }
}

document.addEventListener('DOMContentLoaded', checkUnpaidOrdersForMenu);
