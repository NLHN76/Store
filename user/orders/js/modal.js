function openPaymentModal(orderId) {
    const order = allOrders.find(o => o.id == orderId);
    if (!order) return;

    const bankId = "MB";
    const accountNo = "031203001868";
    const accountName = "NGUYEN LE HOAI NAM";

    const qrUrl =
        `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png` +
        `?amount=${Number(order.total_price)}` +
        `&addInfo=${encodeURIComponent(order.order_code)}` +
        `&accountName=${encodeURIComponent(accountName)}`;

    document.getElementById('modal-content-text').innerHTML = `
        <p><strong>Mã đơn hàng:</strong> ${order.order_code}</p>
        <p><strong>Mã khách hàng:</strong> ${order.user_code}</p>
        <p><strong>Số tiền:</strong> ${Number(order.total_price).toLocaleString('vi-VN')} VNĐ</p>

        <img
            src="${qrUrl}"
            alt="QR thanh toán"
            style="width:250px; max-width:100%; display:block; margin:15px auto;"
        >

        <p>
            Quét mã QR bằng ứng dụng ngân hàng để thanh toán.
        </p>

    `;

    const modal = document.getElementById('payment-modal');

    modal.classList.remove('hidden');
    modal.classList.add('modal-show');
}

function closePaymentModal() {
    const modal = document.getElementById('payment-modal');

    modal.classList.add('hidden');
    modal.classList.remove('modal-show');
}