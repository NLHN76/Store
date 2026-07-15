// actions.js

$(document).on('click', '.receive-btn', function (e) {
    e.stopPropagation();

    var id = $(this).data("id");

    $.post("shipper_dashboard.php", {
        action: "receive_order",
        order_id: id
    }, function (res) {
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


$(document).on('change', '.status-select', function (e) {
    e.stopPropagation();

    var id = $(this).data("id");
    var status = $(this).val();

    $.post("shipper_dashboard.php", {
        action: "update_status",
        order_id: id,
        new_status: status
    }, function (res) {
        if (res === "success") {
            ShipperState.pendingOrders.delete(id);
            stopAlert();
            location.reload();
        } else {
            alert("Trạng thái không hợp lệ!");
        }
    });
});
