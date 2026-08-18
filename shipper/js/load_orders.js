document.addEventListener("DOMContentLoaded", function () {

    function checkNewOrders() {
        fetch("functions/check_new_orders.php?last_id=" + ShipperState.lastOrderId, {
            credentials: "same-origin"
        })
        .then(res => res.json())
        .then(data => {

            if (!data.success || data.new_orders.length === 0) return;

            const tbody = document.querySelector("table tbody");
            tbody.innerHTML = "";

            data.new_orders.forEach(function (order) {

                ShipperState.lastOrderId = Math.max(
                    ShipperState.lastOrderId,
                    Number(order.id)
                );

                const row = document.createElement("tr");

                row.className =
                    "order-row status-" +
                    order.status.replace(/\s/g, "").toLowerCase();

                row.innerHTML = `
                    <td>#${order.id}</td>

                    <td>
                        <b>${order.customer_name}</b>
                    </td>

                    <td>
                        <select
                            class="form-select form-select-sm status-select"
                            data-id="${order.id}"
                        >
                            <option value="Đang xử lý"
                                ${order.status === "Đang xử lý" ? "selected" : ""}>
                                Đang xử lý
                            </option>

                            <option value="Đang giao hàng"
                                ${order.status === "Đang giao hàng" ? "selected" : ""}>
                                Đang giao hàng
                            </option>

                            <option value="Đã giao hàng"
                                ${order.status === "Đã giao hàng" ? "selected" : ""}>
                                Đã giao hàng
                            </option>
                        </select>
                    </td>

                    <td>
                        ${
                            order.status === "Đang xử lý"
                            ? `<button
                                    class="btn btn-success btn-sm receive-btn"
                                    data-id="${order.id}"
                               >
                                    Nhận đơn
                               </button>`
                            : ""
                        }
                    </td>
                `;

                tbody.prepend(row);

                if (order.status === "Đang xử lý") {
                    ShipperState.pendingOrders.add(order.id);
                }
            });

            const banner = document.getElementById("newOrderBanner");

            if (banner) {
                banner.classList.remove("d-none");

                setTimeout(function () {
                    banner.classList.add("d-none");
                }, 5000);
            }

            if (ShipperState.pendingOrders.size > 0) {
                startAlert();
            }
        })
        .catch(function (error) {
            console.error("Lỗi kiểm tra đơn hàng:", error);
        });
    }

    checkNewOrders();
    setInterval(checkNewOrders, 5000);
});