function logout() {
    fetch('logout.php')
        .then(res => res.json())
        .then(() => {
            alert("Đăng xuất thành công!");

            cart = [];
            totalPrice = 0;
            updateCartDisplay();

            window.location.href = 'user.html';
        });
}
