// ================= LỌC SẢN PHẨM =================
function applyFilters() {
    const kw = searchInput.value.toLowerCase();
    const cat = categoryFilter.value;
    const pr = priceFilter.value;

    const filtered = allProducts.filter(p => {
        const price = parseFloat(p.price.replace(/\./g, '').replace(',', '.'));
        const matchKeyword = [p.name, p.product_code, p.category].some(x => x.toLowerCase().includes(kw));
        const matchCategory = cat === "all" || p.category.toLowerCase() === cat.toLowerCase();
        let matchPrice = true;

        if (cat !== "all") {
            if (pr === "0-100") matchPrice = price < 100000;
            else if (pr === "100-300") matchPrice = price >= 100000 && price <= 300000;
            else if (pr === "300-500") matchPrice = price >= 300000 && price <= 500000;
            else if (pr === "500-1000") matchPrice = price >= 500000 && price <= 1000000;
            else if (pr === "1000+") matchPrice = price > 1000000;
        }

        return matchKeyword && matchCategory && matchPrice;
    });

    renderProducts(filtered);
}

// ================= EVENT LỌC =================
searchInput.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', () => {
    priceFilter.style.display = categoryFilter.value === "all" ? "none" : "inline-block";
    priceFilter.value = "all";
    applyFilters();
});
priceFilter.addEventListener('change', applyFilters);



// Tìm kiếm sản phẩm 
function goToProduct(productCode) {
    // chuyển sang trang sản phẩm
    showSection('products');

    // đợi render xong
    setTimeout(() => {
        // reset filter
        categoryFilter.value = 'all';
        priceFilter.value = 'all';

        // tìm đúng sản phẩm theo mã
        searchInput.value = productCode;

        // lọc lại
        applyFilters();

        // scroll cho UX
        document.getElementById('products')
            .scrollIntoView({ behavior: 'smooth' });

        showNotification('👉 Vui lòng chọn màu sắc để mua sản phẩm');
    }, 150);
}






