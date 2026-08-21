function checkout() {
    fetch('pay/save_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(cart)
    })
    .then(res => {
        if (res.ok) {
            window.location.href = 'pay/user_pay.php';
        } else {
            return res.text().then(t => alert(t));
        }
    })
    .catch(err => {
        console.error(err);
    });
}
