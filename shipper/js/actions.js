document.addEventListener("click", function (e) {
    const receiveBtn = e.target.closest(".receive-btn");

    if (!receiveBtn) return;

    e.stopPropagation();

    const id = receiveBtn.dataset.id;

    fetch("shipper_dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "receive_order",
            order_id: id
        })
    })
    .then(response => response.text())
    .then(res => {
        if (res === "success") {
            ShipperState.pendingOrders.delete(id);
            stopAlert();
            location.reload();
        } else {
            alert("Đơn đã có shipper khác nhận!");
            location.reload();
        }
    });
});


document.addEventListener("change", function (e) {
    const statusSelect = e.target.closest(".status-select");

    if (!statusSelect) return;

    e.stopPropagation();

    const id = statusSelect.dataset.id;
    const status = statusSelect.value;

    fetch("shipper_dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "update_status",
            order_id: id,
            new_status: status
        })
    })
    .then(response => response.text())
    .then(res => {
        if (res === "success") {
            ShipperState.pendingOrders.delete(id);
            stopAlert();
            location.reload();
        } else {
            alert("Trạng thái không hợp lệ!");
        }
    });
});