function checkUnpaidOrdersForMenu() {
    fetch('orders/order.php')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;

            const hasUnpaid = data.some(
                order => order.status === "Chờ thanh toán"
            );

            const orderAlert = document.getElementById('order-alert');
            const accountAlert = document.getElementById('account-order-alert');

            if (hasUnpaid) {
                orderAlert?.classList.remove('hidden');
                accountAlert?.classList.remove('hidden');
            } else {
                orderAlert?.classList.add('hidden');
                accountAlert?.classList.add('hidden');
            }
        })
        .catch(err => {
            console.error("Lỗi kiểm tra đơn chưa thanh toán", err);
        });
}

document.addEventListener('DOMContentLoaded', checkUnpaidOrdersForMenu);