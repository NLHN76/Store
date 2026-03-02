// Tạo nút lọc trạng thái
function renderStatusFilters() {
    const filterContainer = document.getElementById('status-filters');
    filterContainer.innerHTML = '';

    const statuses = Object.keys(statusMap);
    statuses.unshift('Tất cả');

    statuses.forEach(status => {
        const btn = document.createElement('button');
        btn.textContent = status;

        // class CSS thuần
        btn.className = 'status-filter-btn';

        btn.addEventListener('click', () => {

            // Xóa active của tất cả nút
            filterContainer
                .querySelectorAll('.status-filter-btn')
                .forEach(b => b.classList.remove('active'));

            // Thêm active cho nút được click
            btn.classList.add('active');

            if (status === 'Tất cả') {
                renderOrders(allOrders);
            } else {
                renderOrders(allOrders.filter(order => order.status === status));
            }
        });

        filterContainer.appendChild(btn);
    });

    // Mặc định chọn "Tất cả"
    const firstBtn = filterContainer.querySelector('.status-filter-btn');
    if (firstBtn) firstBtn.classList.add('active');
}