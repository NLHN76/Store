let allOrders = []; // Lưu tất cả đơn hàng

// Map trạng thái → icon + màu
const statusMap = {
    "Chờ xử lý":        {icon: "🕒", class: "text-gray-500 font-semibold"},
    "Chờ thanh toán":   {icon: "🕓", class: "text-orange-500 font-semibold"},
    "Đã thanh toán":    {icon: "✔️", class: "text-green-600 font-semibold"},
    "Đang xử lý":       {icon: "⚠️", class: "text-yellow-500 font-semibold"},
    "Đang giao hàng":   {icon: "🚚", class: "text-blue-500 font-semibold"},
    "Đã giao hàng":     {icon: "✅", class: "text-green-700 font-semibold"},
    "Đã hủy":           {icon: "❌", class: "text-red-500 font-semibold"}
};

// Fetch danh sách đơn hàng
async function fetchOrders() {
    try {
        const response = await fetch('order.php');
        const data = await response.json();
        if(data.status === 'error'){
            document.getElementById('order-list').innerHTML = `<p class="col-span-full text-red-500 text-center">${data.message}</p>`;
            return;
        }
        allOrders = data;
        renderStatusFilters();
        renderOrders(allOrders);
    } catch(error) {
        console.error(error);
        document.getElementById('order-list').innerHTML = `<p class="col-span-full text-red-500 text-center">Không thể tải thông tin đơn hàng.</p>`;
    }
}

// Tạo nút lọc trạng thái
function renderStatusFilters() {
    const filterContainer = document.getElementById('status-filters');
    filterContainer.innerHTML = '';

    const statuses = Object.keys(statusMap);
    statuses.unshift('Tất cả');

    statuses.forEach(status => {
        const btn = document.createElement('button');
        btn.textContent = status;
        btn.className = 'px-4 py-2 rounded-md border hover:bg-gray-200 transition-all';
        btn.addEventListener('click', () => {
            // Xóa active của tất cả nút
            filterContainer.querySelectorAll('button').forEach(b => b.classList.remove('border-b-2','border-blue-500','font-bold'));
            // Thêm active cho nút được click
            btn.classList.add('border-b-2','border-blue-500','font-bold');

            if(status === 'Tất cả') renderOrders(allOrders);
            else renderOrders(allOrders.filter(order => order.status === status));
        });
        filterContainer.appendChild(btn);
    });

    // Mặc định chọn Tất cả
    filterContainer.querySelector('button').classList.add('border-b-2','border-blue-500','font-bold');
}

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