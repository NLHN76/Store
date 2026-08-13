
let selectedCategory = "all";


// ================= DOM ELEMENTS =================
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const priceFilter = document.getElementById('priceFilter');

// ================= LỌC SẢN PHẨM =================
function applyFilters() {

    const keyword = searchInput ? searchInput.value.trim().toLowerCase() : "";
    const brand = brandFilter ? brandFilter.value : "all";
    const price = priceFilter ? priceFilter.value : "all";

    const filtered = allProducts.filter(product => {

        // ---------- Tìm kiếm ----------
        const matchKeyword =
            keyword === "" ||
            product.name.toLowerCase().includes(keyword) ||
            product.product_code.toLowerCase().includes(keyword);

        // ---------- Danh mục ----------
        const matchCategory =
            selectedCategory === "all" ||
            product.category.trim().toLowerCase() ===
            selectedCategory.trim().toLowerCase();

        // ---------- Thương hiệu ----------
        const matchBrand =
            brand === "all" ||
            (product.brand &&
             product.brand.trim().toLowerCase() === brand.toLowerCase());

        // ---------- Giá ----------
        const productPrice = Number(product.price.toString().replace(/\./g, ""));

        let matchPrice = true;

        switch (price) {
            case "0-100":
                matchPrice = productPrice < 100000;
                break;

            case "100-300":
                matchPrice = productPrice >= 100000 && productPrice <= 300000;
                break;

            case "300-500":
                matchPrice = productPrice >= 300000 && productPrice <= 500000;
                break;

            case "500-1000":
                matchPrice = productPrice >= 500000 && productPrice <= 1000000;
                break;

            case "1000+":
                matchPrice = productPrice > 1000000;
                break;
        }

        return (
            matchKeyword &&
            matchCategory &&
            matchBrand &&
            matchPrice
        );

    });

    renderProducts(filtered);
}

// ================= CLICK DANH MỤC =================
function initCategoryFilter() {

    const items = document.querySelectorAll(".category-item");

  items.forEach(item => {

    item.addEventListener("click", function () {

        // Xóa active ở các danh mục khác
        items.forEach(i => i.classList.remove("active"));

        // Active danh mục đang chọn
        this.classList.add("active");

        // Lấy danh mục được chọn
        selectedCategory = this.dataset.category;

        // Hiện thanh bộ lọc
        const filterBar = document.getElementById("productFilter");

        if (filterBar) {
            filterBar.style.display = "flex";
        }

        // Cập nhật danh sách thương hiệu theo danh mục
        populateBrandFilter();

        // Reset thương hiệu về "Tất cả"
        if (brandFilter) {
            brandFilter.value = "all";
        }

        // Áp dụng lại toàn bộ bộ lọc
        applyFilters();

    });

});

}


//-------------------------LỌC THƯƠNG HIỆU------------------------------
function populateBrandFilter() {

    const brandFilter = document.getElementById("brandFilter");

    if (!brandFilter) return;

    brandFilter.innerHTML =
        '<option value="all">Tất cả thương hiệu</option>';

    const categoryProducts = selectedCategory === "all"
        ? allProducts
        : allProducts.filter(product =>
            product.category?.trim().toLowerCase() ===
            selectedCategory.trim().toLowerCase()
        );

    const brands = [...new Set(
        categoryProducts
            .map(product => product.brand?.trim())
            .filter(Boolean)
    )];

    brands.sort((a, b) => a.localeCompare(b, "vi"));

    brands.forEach(brand => {

        const option = document.createElement("option");

        option.value = brand;
        option.textContent = brand;

        brandFilter.appendChild(option);

    });
}

// ================= EVENT =================
if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
}

if (brandFilter) {
    brandFilter.addEventListener("change", applyFilters);
}

if (priceFilter) {
    priceFilter.addEventListener("change", applyFilters);
}




// ================= TÌM KIẾM TỪ TRANG CHỦ =================
function goToProduct(productCode) {

    showSection("products");

    setTimeout(() => {

        selectedCategory = "all";

        document.querySelectorAll(".category-item")
            .forEach(i => i.classList.remove("active"));

        if (searchInput) {
            searchInput.value = productCode;
        }

        if (brandFilter) brandFilter.value = "all";
        if (priceFilter) priceFilter.value = "all";

        applyFilters();

        document
            .getElementById("products")
            .scrollIntoView({ behavior: "smooth" });

        showNotification("👉 Vui lòng chọn màu sắc để mua sản phẩm");

    }, 150);
}



