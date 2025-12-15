
// Thanh toán 
async function checkout() {
    try {
        const res = await fetch('../pay/save_cart.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            credentials: 'same-origin', // ← gửi cookie PHP session
            body: JSON.stringify(cart)
        });
        if (res.ok) window.location.href = '../pay/user_pay.php';
        else res.text().then(t => alert(t)); // hiện thông báo lỗi nếu cần
    } catch(err) { console.error(err); }
}
