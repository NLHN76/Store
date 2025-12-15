
// ================= CHECKOUT =================
document.getElementById("checkout").onclick = () => {
    const isLoggedIn = false; // TODO: thay bằng trạng thái thực tế
    if (!isLoggedIn) showNotification("Bạn cần đăng nhập để tiếp tục mua hàng",3000);
    else alert("Đặt hàng thành công!");
};


//==================Thông báo===================
const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

document.getElementById('zalo-float').addEventListener('click', e => {
    e.preventDefault();
    isLoggedIn ? window.open('https://zalo.me/0587911287', '_blank') : alert('Vui lòng đăng nhập !');
});

document.getElementById('messenger-float').addEventListener('click', e => {
    e.preventDefault();
    isLoggedIn ? window.open('https://www.facebook.com/nam.nguyen.133454?mibextid=ZbWKwL', '_blank') : alert('Vui lòng đăng nhập!');
});

