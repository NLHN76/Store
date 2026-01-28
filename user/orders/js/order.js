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

