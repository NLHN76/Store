function logout() {
    fetch('logout.php')
        .then(res => res.json())
        .then(() => {
            alert("Đăng xuất thành công!");
            window.location.href = 'user.html';
        });
}
