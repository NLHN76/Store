
// Render danh sách đơn hàng
function renderOrders(orders) {
    const orderList = document.getElementById('order-list');
    orderList.innerHTML = '';

    if (orders.length === 0) {
        orderList.innerHTML = `<p class="col-span-full text-center text-gray-500">Không có đơn hàng nào</p>`;
        return;
    }

    orders.forEach(order => {
        const formattedPrice = parseFloat(order.total_price).toLocaleString('de-DE');
        const colors = order.color || 'Không có màu';
        const status = statusMap[order.status] || { icon: "❓", class: "text-gray-500" };

        // ⭐ XỬ LÝ IMAGE
        let imagesHtml = '';
        if (order.image) {
            const images = order.image.split(', ');
            images.forEach(img => {
                imagesHtml += `
                    <img 
                        src="../../admin/uploads/${img}" 
                        alt="Ảnh sản phẩm"
                        class="order-image"
                    >
                `;
            });
        }

        const orderCard = document.createElement('div');
        orderCard.className = 'bg-white shadow-md rounded-lg p-6 flex flex-col';

        orderCard.innerHTML = `
            <h3 class="text-xl font-bold mb-2">Mã Đơn Hàng: ${order.id}</h3>

            ${imagesHtml ? `<div class="order-images">${imagesHtml}</div>` : ''}

            <div class="flex-1">
                <p><strong>Số Điện Thoại:</strong> ${order.customer_phone}</p>
                <p><strong>Địa Chỉ:</strong> ${order.customer_address}</p>
                <p><strong>Sản Phẩm:</strong> ${order.product_name}</p>
                <p><strong>Loại Sản Phẩm:</strong> ${order.category}</p>
                <p><strong>Màu Sắc:</strong> ${colors}</p>
                <p><strong>Số Lượng:</strong> ${order.product_quantity}</p>
                <p><strong>Tổng Tiền:</strong> ${formattedPrice} VNĐ</p>
                <p><strong>Ngày Thanh Toán:</strong> ${order.order_date}</p>

                <p>
                    <strong>Trạng Thái:</strong>
                    <span class="${status.class}">
                        ${status.icon} ${order.status}
                    </span>
                </p>

                ${
                    order.status === "Chờ xử lý"
                    ? `<button class="mt-2 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                        onclick="cancelOrder(${order.id})">Hủy đơn</button>`
                    : ''
                }

${
    order.status === "Chờ thanh toán"
    ? `
        <button
            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            onclick="openPaymentModal(${order.id})">
            Thanh toán ngay
        </button>
    `
    : ''
}


            </div>
        `;

        orderList.appendChild(orderCard);
    });
}


