const productsContainer = document.getElementById('productsContainer');
const template = document.getElementById('product-template');

function renderProducts(products) {
    productsContainer.innerHTML = '';

    products.forEach(product => {
        const clone = template.content.cloneNode(true);
        const productDiv = clone.querySelector('.product');

        // ===== XỬ LÝ GIÁ =====
        const priceNumber = parseFloat(
            product.price.replace(/\./g, '').replace(',', '.')
        );
        const priceFormatted = priceNumber.toLocaleString('vi-VN');

        // ===== DATASET =====
        productDiv.dataset.name = product.name;
        productDiv.dataset.price = priceNumber;
        productDiv.dataset.code = product.product_code;

        // ===== GÁN NỘI DUNG =====
        clone.querySelector('.product-image').src = product.image;
        clone.querySelector('.product-name').textContent = product.name;
        clone.querySelector('.product-price').textContent = priceFormatted;
        clone.querySelector('.avg-rating').textContent = product.avg_rating || 0;

        // ===== CLICK ẢNH → CHI TIẾT =====
        clone.querySelector('.product-image').onclick = () => {
            window.location.href =
                `products/product_detail.php?code=${product.product_code}`;
        };

        // ===== MÀU SẮC =====
        const colors = product.color
            ?.split(',')
            .map(c => c.trim())
            .filter(Boolean) || [];

        const colorBox = clone.querySelector('.color-select-container');
        const select = clone.querySelector('.color-select');
        const stockSpan = clone.querySelector('.stock');
        const warning = clone.querySelector('.stock-warning');
        const addBtn = clone.querySelector('.add-cart-btn');

        product.stockByColor = product.stockByColor || {};

        if (colors.length) {
            colorBox.style.display = 'block';
            select.innerHTML = colors
                .map(c => `<option value="${c}">${c}</option>`)
                .join('');

            const loadStock = () => {
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

            select.addEventListener('change', loadStock);
            loadStock();
        }

        // ===== THÊM GIỎ =====
        addBtn.onclick = () => addToCart(addBtn);

        productsContainer.appendChild(clone);
    });
}
