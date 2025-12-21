async function checkUnpaidOrdersForMenu() {
    try {
        const res = await fetch('orders/order.php'); 
        const data = await res.json();

        if (!Array.isArray(data)) return;

        const hasUnpaid = data.some(order => order.status === "Chờ thanh toán");

        const alertIcon = document.getElementById('order-alert');
        if (!alertIcon) return;

        if (hasUnpaid) {
            alertIcon.classList.remove('hidden');
        } else {
            alertIcon.classList.add('hidden');
        }
    } catch (err) {
        console.error("Lỗi kiểm tra đơn chưa thanh toán", err);
    }
}

document.addEventListener('DOMContentLoaded', checkUnpaidOrdersForMenu);
