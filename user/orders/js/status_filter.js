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
