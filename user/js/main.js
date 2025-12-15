// ================= GIỎ HÀNG TOÀN CỤC =================
let cart = [];
let totalPrice = 0;
let allProducts = [];

// ================= DOM ELEMENTS =================
const productsContainer = document.getElementById('products-container');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const priceFilter = document.getElementById('priceFilter');
const notification = document.getElementById('notification');

// ================= HÀM TIỆN ÍCH =================
const fetchJSON = url => fetch(url).then(res => res.json()).catch(err => console.error('❌ Lỗi:', err));

const formatPrice = price => parseFloat(price.replace(/\./g, '').replace(',', '.')).toLocaleString('vi-VN');

function showNotification(msg, duration = 1000) {
    notification.textContent = msg;
    notification.style.display = 'block';
    setTimeout(() => notification.style.display = 'none', duration);
}

function togglePassword(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}

// ================= HIỂN THỊ SECTION =================
function showSection(sectionId) {
    document.querySelectorAll('main > section, .container')
        .forEach(s => s.style.display = 'none');

    const sec = document.getElementById(sectionId);
    if (sec) sec.style.display = 'block';

    document.querySelector('footer').style.display =
        sectionId === 'home' ? 'block' : 'none';
}


// ================= FETCH DỮ LIỆU =================
document.addEventListener('DOMContentLoaded', () => {

    // 1️⃣ Hiển thị trang chủ
    showSection('home');

    // 2️⃣ Lấy sản phẩm
    fetchJSON('../user/get_products.php').then(data => {
        allProducts = data;

        // Khởi tạo tồn kho theo màu
        allProducts.forEach(p => {
            p.stock = {};
            const colors = p.color
                ?.split(',')
                .map(c => c.trim())
                .filter(Boolean) || [];

            colors.forEach(c => p.stock[c] = 0);
        });

        renderProducts(allProducts);
    });

    // 3️⃣ Lấy dữ liệu trang chủ
    fetchJSON('../user/get_home.php').then(renderHome);

    // 4️⃣ AUTO: tự điền tên + email nếu đã đăng nhập
    fetchJSON('auto/auto.php')
        .then(user => {
            if (!user) return;

            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');

            if (nameInput && user.name) nameInput.value = user.name;
            if (emailInput && user.email) emailInput.value = user.email;
        })
        .catch(err => console.warn('Auto user error:', err));

});

