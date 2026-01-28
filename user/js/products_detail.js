
// ================= RENDER SẢN PHẨM =================
function renderProducts(products) {
    productsContainer.innerHTML = '';

    products.forEach(product => {

        const priceNumber = parseFloat(
            product.price.replace(/\./g, '').replace(',', '.')
        );
        const priceFormatted = priceNumber.toLocaleString('vi-VN');

        // ===== TỒN KHO THEO MÀU =====
        product.stockByColor = product.stockByColor || {};
        const colors = product.color
            ?.split(',')
            .map(c => c.trim())
            .filter(Boolean) || [];

        colors.forEach(c => {
            if (product.stockByColor[c] === undefined) {
                product.stockByColor[c] = 0;
            }
        });

        const colorSelectHTML = colors.length
            ? `
              <div class="color-select-container">
                  <label><strong>Màu sắc:</strong></label>
                  <select class="color-select">
                      ${colors.map(c => `<option value="${c}">${c}</option>`).join('')}
                  </select>
              </div>`
            : ``;

        const productDiv = document.createElement('div');
        productDiv.className = 'product';
        productDiv.dataset.name = product.name;
        productDiv.dataset.price = priceNumber;
        productDiv.dataset.code = product.product_code;

        productDiv.innerHTML = `
            <img src="${product.image}" class="product-image"
                 style="width:150px;height:150px;cursor:pointer;">
            <h3>${product.name}</h3>
            <p><strong>Giá:</strong> ${priceFormatted} VNĐ</p>

            ${colorSelectHTML}

            <p><strong>Kho:</strong> <span class="stock">0</span></p>
            <p class="stock-warning" style="display:none;color:red;">
                ❌ Màu này đã hết hàng!
            </p>

            <button onclick="addToCart(this)">Mua sản phẩm</button>
            <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating || 0}</p>
        `;

        productsContainer.appendChild(productDiv);

        // ===== CLICK ẢNH → CHI TIẾT =====
        productDiv.querySelector('.product-image').onclick = () => {
            window.location.href =
                `products/product_detail.php?code=${product.product_code}`;
        };

        // ===== LOAD TỒN KHO =====
        const select = productDiv.querySelector('.color-select');
        const stockSpan = productDiv.querySelector('.stock');
        const warning = productDiv.querySelector('.stock-warning');
        const addBtn = productDiv.querySelector('button');

        const loadStock = () => {
            if (!select) return;
            const color = select.value;

            fetchJSON(
                `get_inventory.php?product_code=${product.product_code}&color=${encodeURIComponent(color)}`
            ).then(inv => {
                product.stockByColor[color] = inv.quantity;
                stockSpan.textContent = inv.quantity;
                addBtn.disabled = inv.quantity <= 0;
                warning.style.display = inv.quantity <= 0 ? 'block' : 'none';
            });
        };

        if (select) {
            select.addEventListener('change', loadStock);
            loadStock();
        }
    });
}


