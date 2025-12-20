
//==================Thông báo===================
function addToCart(btn) {
    alert("⚠️ Bạn cần đăng nhập để thực hiện");
}

const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

document.getElementById('zalo-float').addEventListener('click', e => {
    e.preventDefault();
    isLoggedIn ? window.open('https://zalo.me/0587911287', '_blank') : alert('Vui lòng đăng nhập !');
});

document.getElementById('messenger-float').addEventListener('click', e => {
    e.preventDefault();
    isLoggedIn ? window.open('https://www.facebook.com/nam.nguyen.133454?mibextid=ZbWKwL', '_blank') : alert('Vui lòng đăng nhập!');
});

