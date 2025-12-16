$(function(){
    let alertAudio = document.getElementById('newOrderSound');
    let alertInterval = null;
    let lastOrderId = parseInt($("#newOrderBanner").data("last-id") || 0);
    let pendingOrders = new Set(); // lưu id các đơn chưa nhận

    // --- Modal shipper ---
    $(".avatar-login").click(() => {
        new bootstrap.Modal(document.getElementById('shipperModal')).show();
    });

    $("#shipperForm").submit(function(e){
        e.preventDefault();
        var fd = new FormData(this);
        $.ajax({
            url: "shipper_dashboard.php",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res=="success"){
                    alert("Cập nhật thành công!");
                    location.reload();
                } else {
                    alert("Lỗi: "+res);
                }
            }
        });
    });

    // --- Nhận đơn ---
    $(document).on('click', '.receive-btn', function(){
        let btn = $(this);
        let id = btn.data("id");
        $.post("shipper_dashboard.php", {action:"receive_order", order_id:id}, d => {
            if(d=="success"){
                alert("Bạn đã nhận đơn!");
                // Xóa đơn khỏi pending và tắt âm thanh ngay lập tức
                pendingOrders.delete(id);
                stopAllAlertAudio(); 
                location.reload();
            } else {
                alert("Đơn đã có shipper khác nhận!");
            }
        });
    });

    // --- Cập nhật trạng thái ---
    $(document).on('change', '.status-select', function(){
        let id = $(this).data("id"),
            s = $(this).val();
        $.post("shipper_dashboard.php", {action:"update_status", order_id:id, new_status:s}, d => {
            alert(d=="success"?"Cập nhật trạng thái thành công!":"Chỉ được chỉnh trạng thái hợp lệ!");
            if(d=="success"){
                if(s != "Đang xử lý") pendingOrders.delete(id); // xóa khỏi pending nếu không còn xử lý
                stopAllAlertAudio();
                location.reload();
            }
        });
    });

    // --- Dừng toàn bộ âm thanh ---
    function stopAllAlertAudio(){
        pendingOrders.clear(); // xóa tất cả đơn pending
        if(alertInterval){
            clearInterval(alertInterval);
            alertInterval = null;
        }
        alertAudio.pause();
        alertAudio.currentTime = 0;
    }

    // --- Load đơn mới ---
    function checkNewOrders(){
        fetch('check_new_orders.php?last_id=' + lastOrderId, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            if(data.success && data.new_orders.length > 0){
                const tbody = $('table tbody');
                data.new_orders.forEach(order => {
                    lastOrderId = Math.max(lastOrderId, parseInt(order.id));

                    const tr = $(`
                        <tr class="status-dangxuly" data-bs-toggle="collapse" data-bs-target="#order${order.id}" style="cursor:pointer;">
                            <td>#${order.id}</td>
                            <td><b>${order.customer_name}</b><br><small>${order.customer_phone}</small></td>
                            <td>${order.product_name}</td>
                            <td>${order.color}</td>
                            <td>${Number(order.total_price).toLocaleString('vi-VN')}₫</td>
                            <td>
                                <select class="form-select form-select-sm status-select" data-id="${order.id}">
                                    <option value="Đang xử lý" ${order.status=="Đang xử lý"?'selected':''}>Đang xử lý</option>
                                    <option value="Đang giao hàng" ${order.status=="Đang giao hàng"?'selected':''}>Đang giao hàng</option>
                                    <option value="Đã giao hàng" ${order.status=="Đã giao hàng"?'selected':''}>Đã giao hàng</option>
                                </select>
                            </td>
                            <td>${order.shipper_id? `<div class="d-flex align-items-center gap-2"><img src="${order.shipper_avatar||'https://via.placeholder.com/30'}" class="avatar-order"> ${order.shipper_name}</div>` : '<span class="text-secondary">Chưa nhận</span>'}</td>
                            <td>${order.status=="Đang xử lý" && !order.shipper_id? `<button class="btn btn-success btn-sm receive-btn" data-id="${order.id}">Nhận đơn</button>` : ''}</td>
                        </tr>
                    `);

                    const trDetail = $(`
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
                    `);

                    tbody.prepend(trDetail);
                    tbody.prepend(tr);

                    // Nếu đơn mới chưa nhận và đang xử lý
                    if(order.status=="Đang xử lý" && !order.shipper_id){
                        pendingOrders.add(order.id);
                    }
                });

                // Banner
                const banner = document.getElementById('newOrderBanner');
                banner.classList.remove('d-none');
                setTimeout(() => banner.classList.add('d-none'), 5000);

                // Nếu còn pendingOrders, bật âm thanh lặp
                if(pendingOrders.size > 0 && !alertInterval){
                    alertInterval = setInterval(() => {
                        alertAudio.play().catch(()=>{});
                    }, 2000);
                }
            }
        })
        .catch(err => console.error(err));
    }

    setInterval(checkNewOrders, 5000);

});
