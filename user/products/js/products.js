function addToCartDetail(btn) {
    const productCode = btn.dataset.productCode;
    const price = btn.dataset.price;

    const colorSelect = document.getElementById('productColor');
    const color = colorSelect ? colorSelect.value : null;

    fetch('../cart/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_code: productCode,
            color: color,
            quantity: 1,
            price: price
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = "../user_login.html#cart";
        } else {
            alert(data.error || '❌ Thêm thất bại');
        }
    })
    .catch(() => alert('❌ Lỗi kết nối'));
}
