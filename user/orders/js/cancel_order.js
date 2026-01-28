// Hủy đơn
function cancelOrder(orderId) {
    if(!confirm("Bạn có chắc muốn hủy đơn hàng này?")) return;

    fetch('cancel_order.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id: orderId})
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            alert("Đơn hàng đã được hủy!");
            fetchOrders();
        } else {
            alert("Hủy đơn thất bại: " + data.message);
        }
    })
    .catch(err => {console.error(err); alert("Có lỗi xảy ra.");});
}

document.addEventListener('DOMContentLoaded', fetchOrders);

