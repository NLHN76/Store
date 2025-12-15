
// Đăng xuất
function logout() { cart=[]; totalPrice=0; updateCartDisplay(); alert('Đăng xuất thành công'); window.location.href='user.html'; }

searchInput.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', () => {
    priceFilter.style.display = categoryFilter.value==='all'?'none':'inline-block';
    priceFilter.value='all';
    applyFilters();
});
priceFilter.addEventListener('change', applyFilters);
