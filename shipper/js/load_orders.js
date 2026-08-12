//kiểm tra đơn hàng mới và load đơn hàng 

$(function () {

    function checkNewOrders() {

        fetch("functions/check_new_orders.php?last_id=" + ShipperState.lastOrderId, {
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {

            if (!data.success || data.new_orders.length === 0) return;

            var tbody = $('table tbody');
            tbody.empty();

            data.new_orders.forEach(function (order) {

                ShipperState.lastOrderId = Math.max(
                    ShipperState.lastOrderId,
                    Number(order.id)
                );

                var row = `
                <tr class="order-row status-${order.status.replace(/\s/g,'').toLowerCase()}"
                    data-bs-toggle="collapse"
                    data-bs-target="#order${order.id}"
                    style="cursor:pointer">
                    <td>#${order.id}</td>
                    <td>
                        <b>${order.customer_name}</b><br>
                        <small>${order.customer_phone}</small>
                    </td>
                    <td>${order.product_name}</td>
                    <td>${order.color}</td>
                    <td>${Number(order.total_price).toLocaleString('vi-VN')}₫</td>
                    <td>
                        <select class="form-select form-select-sm status-select" data-id="${order.id}">
                            <option value="Đang xử lý" ${order.status === "Đang xử lý" ? "selected" : ""}>Đang xử lý</option>
                            <option value="Đang giao hàng" ${order.status === "Đang giao hàng" ? "selected" : ""}>Đang giao hàng</option>
                            <option value="Đã giao hàng" ${order.status === "Đã giao hàng" ? "selected" : ""}>Đã giao hàng</option>
                        </select>
                    </td>
                    <td>
                        ${
                            order.shipper_id
                            ? `<div class="d-flex align-items-center gap-2">
                                <img src="${order.shipper_avatar || 'https://via.placeholder.com/30'}" class="avatar-order">
                                ${order.shipper_name}
                              </div>`
                            : `<span class="text-secondary">Chưa nhận</span>`
                        }
                    </td>
                    <td>
                        ${
                            order.status === "Đang xử lý" && !order.shipper_id
                            ? `<button class="btn btn-success btn-sm receive-btn" data-id="${order.id}">Nhận đơn</button>`
                            : ""
                        }
                    </td>
                </tr>
                `;

                var detail = `
                <tr class="collapse-row">
                    <td colspan="8" class="p-0">
                        <div id="order${order.id}" class="collapse p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><b>📞 Điện thoại:</b> ${order.customer_phone}</p>
                                    <p><b>🏠 Địa chỉ:</b> ${order.customer_address}</p>
                                    <p><b>📅 Ngày đặt:</b> ${order.order_date}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><b>📦 Sản phẩm:</b> ${order.product_name}</p>
                                    <p><b>🔢 Số lượng:</b> ${order.product_quantity}</p>
                                    <p><b>🎨 Màu sắc:</b> ${order.color}</p>
                                    <p><b>💰 Tổng tiền:</b> ${Number(order.total_price).toLocaleString('vi-VN')}₫</p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                `;

                tbody.prepend(detail).prepend(row);

                if (order.status === "Đang xử lý" && !order.shipper_id) {
                    ShipperState.pendingOrders.add(order.id);
                }
            });

            $("#newOrderBanner").removeClass('d-none')
                .delay(5000)
                .queue(function () {
                    $(this).addClass('d-none').dequeue();
                });

            if (ShipperState.pendingOrders.size > 0) {
                startAlert();
            }
        })
        .catch(console.error);
    }


    checkNewOrders();
    setInterval(checkNewOrders, 5000);

});
