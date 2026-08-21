const colors = {
    "Chờ xử lý": "#f8f9fa",
    "Chờ thanh toán": "#fff3cd",
    "Đã thanh toán": "#d1ecf1",
    "Đang xử lý": "#fff3cd",
    "Đang giao hàng": "#cce5ff",
    "Đã giao hàng": "#d6d8d9",
    "Đã hủy": "#f8d7da"
};

const statuses = Object.keys(colors);


// XÓA
function deleteOrder(id) {

    if (!confirm(`Xóa đơn #${id}?`)) return;

    fetch("functions/update.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            delete_id: id
        })
    })
    .then(res => res.text())
    .then(data => {

        if (data.trim() === "success") {
            loadOrders();
        } else {
            alert("Lỗi: " + data);
        }

    });
}


// CẬP NHẬT
function updateStatus(id, select) {

    fetch("functions/update.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "update_status",
            order_id: id,
            new_status: select.value
        })
    })
    .then(res => res.text())
    .then(data => {

        if (data.trim() === "success") {
            loadOrders();
        } else {
            alert("Lỗi: " + data);
        }

    });
}


// LOAD
function loadOrders() {

    const keyword =
        document.querySelector("input[name=keyword]").value;

    fetch(
        "functions/get_data.php?" +
        new URLSearchParams({ keyword })
    )
    .then(res => res.json())
    .then(orders => renderOrders(orders));
}


// HIỂN THỊ
function renderOrders(orders) {

    const tbody =
        document.querySelector("#ordersTableBody");

    tbody.innerHTML = "";

    const template =
        document.getElementById("orderTemplate");

    if (!orders.length) {

        tbody.innerHTML = `
            <tr>
                <td colspan="16" style="text-align:center">
                    Không có đơn hàng
                </td>
            </tr>
        `;

        return;
    }

    orders.forEach(order => {

        const row =
            template.content.cloneNode(true);

        const tr =
            row.querySelector("tr");

        tr.style.backgroundColor =
            colors[order.status] || "#fff";


        // DỮ LIỆU
        tr.querySelector(".order-id").textContent =
            order.order_code;

        tr.querySelector(".order-date").textContent =
            new Date(order.order_date)
                .toLocaleString("vi-VN");

        tr.querySelector(".customer-name").textContent =
            order.customer_name;

        tr.querySelector(".customer-email").textContent =
            order.customer_email;

        tr.querySelector(".customer-phone").textContent =
            order.customer_phone;

        tr.querySelector(".customer-address").textContent =
            order.customer_address;

        tr.querySelector(".product-code").textContent =
            order.product_code;

        tr.querySelector(".product-name").textContent =
            order.product_name;

        tr.querySelector(".category").textContent =
            order.category;

        tr.querySelector(".color").textContent =
            order.color || "-";

        tr.querySelector(".quantity").textContent =
            order.product_quantity;

        tr.querySelector(".total-price").textContent =
            Number(order.total_price)
                .toLocaleString("vi-VN");

        tr.querySelector(".user-code").textContent =
            order.user_code || "-";


        // TRẠNG THÁI
        const select =
            tr.querySelector(".status-select");

        statuses.forEach(status => {

            const option =
                new Option(status, status);

            option.selected =
                status === order.status;

            select.appendChild(option);

        });

        select.addEventListener("change", () => {
            updateStatus(order.id, select);
        });


        // SHIPPER
        tr.querySelector(".shipper").innerHTML =
            order.shipper_id
                ? `<strong>${order.shipper_name}</strong><br>
                   Email: ${order.shipper_email}<br>
                   SĐT: ${order.shipper_phone}`
                : "Chưa nhận";


        // CHỨC NĂNG
        tr.querySelector(".actions").innerHTML = `
            <button class="delete-btn">
                Xóa
            </button>

            <form
                method="POST"
                action="functions/invoice.php"
                style="display:inline"
            >
                <input
                    type="hidden"
                    name="export_html_id"
                    value="${order.id}"
                >

                <button>
                    Xuất hóa đơn
                </button>
            </form>
        `;


        // NÚT XÓA
        tr.querySelector(".delete-btn")
            .addEventListener("click", () => {
                deleteOrder(order.id);
            });


        tbody.appendChild(row);
    });
}


// TÌM KIẾM
document.querySelector("#searchForm").addEventListener("submit", function(e) {

    e.preventDefault();

    loadOrders();

});


document.addEventListener("DOMContentLoaded", function() {

    loadOrders();

    setInterval(loadOrders, 5000);

});