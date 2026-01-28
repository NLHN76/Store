// Hiển thị và đóng  modal thanh toán
function openPaymentModal(orderId) {
    const order = allOrders.find(o => o.id == orderId);
    if (!order) return;

    document.getElementById('modal-content').innerHTML = `
        Cảm ơn bạn đã đặt hàng! Vui lòng kiểm tra email xác nhận.<br>
        Khi thanh toán bằng chuyển khoản, ghi rõ
        <strong>Mã Khách Hàng (${order.user_code})</strong>
        trong nội dung chuyển khoản.
    `;

    const modal = document.getElementById('payment-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePaymentModal() {
    const modal = document.getElementById('payment-modal');
    modal.classList.add('hidden');
}