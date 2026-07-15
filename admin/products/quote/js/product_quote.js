 const form = document.getElementById('quote-form');
    const productsInput = document.getElementById('products-input');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const qtyInputs = document.querySelectorAll('.product-qty');

    function updateSelectedProducts() {
        let products = [];
        checkboxes.forEach((chk, i) => {
            if (chk.checked) {
                const qty = parseInt(qtyInputs[i].value) || 1;
                products.push({
                    name: chk.dataset.name,
                    price: parseFloat(chk.dataset.price),
                    quantity: qty
                });
            }
        });
        productsInput.value = JSON.stringify(products);
    }

    checkboxes.forEach((chk, i) => {
        chk.addEventListener('change', () => {
            qtyInputs[i].disabled = !chk.checked;
            if (!chk.checked) qtyInputs[i].value = 1;
            updateSelectedProducts();
        });
    });

    qtyInputs.forEach((qty, i) => {
        qty.addEventListener('input', updateSelectedProducts);
    });

    // Cập nhật ban đầu
    updateSelectedProducts();


    form.addEventListener('submit', function (e) {
    updateSelectedProducts();

    const products = JSON.parse(productsInput.value || "[]");

    if (products.length === 0) {
        e.preventDefault();
        alert("Vui lòng chọn ít nhất 1 sản phẩm để xuất báo giá");
    }
});
