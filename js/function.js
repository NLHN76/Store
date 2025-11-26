// ================= GIỎ HÀNG TOÀN CỤC =================
let cart = [];
let totalPrice = 0;
let allProducts = [];

const productsContainer = document.getElementById('products-container');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const priceFilter = document.getElementById('priceFilter');
const notification = document.getElementById('notification');

// ================= HÀM TIỆN ÍCH =================
const fetchJSON = async (url) => {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`❌ Lỗi fetch ${url}`);
    return res.json();
};

const formatPrice = (price) => parseFloat(price.replace(/\./g, '').replace(',', '.')).toLocaleString('vi-VN');

// ================= HIỂN THỊ SECTION =================
function showSection(sectionId) {
    document.querySelectorAll('main > section, .container').forEach(s => s.style.display = 'none');
    const section = document.getElementById(sectionId);
    if (section) section.style.display = 'block';
    document.querySelector('footer').style.display = sectionId === 'home' ? 'block' : 'none';

    if (sectionId === 'cart') updateCartDisplay();
}

// ================= LỌC SẢN PHẨM =================
function applyFilters() {
    const keyword = searchInput.value.toLowerCase();
    const category = categoryFilter.value;
    const priceRange = priceFilter.value;

    const filtered = allProducts.filter(p => {
        const price = parseFloat(p.price.replace(/\./g, '').replace(',', '.'));
        const matchKeyword = p.name.toLowerCase().includes(keyword)
            || p.product_code.toLowerCase().includes(keyword)
            || p.category.toLowerCase().includes(keyword);
        const matchCategory = category === "all" || p.category.toLowerCase() === category.toLowerCase();

        let matchPrice = true;
        switch(priceRange) {
            case "0-100": matchPrice = price < 100000; break;
            case "100-300": matchPrice = price >= 100000 && price <= 300000; break;
            case "300-500": matchPrice = price >= 300000 && price <= 500000; break;
            case "500-1000": matchPrice = price >= 500000 && price <= 1000000; break;
            case "1000+": matchPrice = price > 1000000; break;
        }

        return matchKeyword && matchCategory && matchPrice;
    });

    renderProducts(filtered);
}

// ================= RENDER SẢN PHẨM =================
function renderProducts(data) {
    productsContainer.innerHTML = '';

    data.forEach(product => {
        const div = document.createElement('div');
        div.classList.add('product');
        div.dataset.name = product.name;
        div.dataset.price = parseFloat(product.price.replace(/\./g, '').replace(',', '.'));
        div.dataset.code = product.product_code;

        // Lưu tồn kho riêng theo màu
        product.stockByColor = product.color?.split(',').reduce((acc, c) => { 
            acc[c.trim()] = 0; 
            return acc; 
        }, {}) || {};

        const colors = Object.keys(product.stockByColor);
        const inventoryHTML = `
            <p><strong>Kho:</strong> <span class="stock">0</span></p>
            <p class="sold" style="display:none;"><strong>Đã bán:</strong> <span>0</span></p>
            <p class="text-danger stock-warning" style="display:none;">❌ Màu này đã hết hàng!</p>
        `;
        const colorSelectHTML = colors.length
            ? `<div class="color-select-container">
                   <label><strong>Màu sắc:</strong></label>
                   <select class="color-select">${colors.map(c => `<option value="${c}">${c}</option>`).join('')}</select>
               </div>${inventoryHTML}`
            : `<div class="color-select-container"><p><strong>Màu sắc:</strong> Không có tùy chọn</p></div>${inventoryHTML}`;

        div.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image" style="width:150px; height:150px; cursor:pointer;">
            <h3>${product.name}</h3>
            <p class="product-code" style="display:none;"><strong>Mã sản phẩm:</strong> ${product.product_code}</p>
            <p><strong>Giá:</strong> ${formatPrice(product.price)} VNĐ</p>
            ${colorSelectHTML}
            <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
            <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating} </p>
        `;
        productsContainer.appendChild(div);

        // Chi tiết sản phẩm
        div.querySelector('.product-image').addEventListener('click', () => {
            window.location.href = `product_detail.php?code=${product.product_code}`;
        });

        // Load tồn kho từ server
        const select = div.querySelector('.color-select');
        const stockSpan = div.querySelector('.stock');
        const addBtn = div.querySelector('button');
        const warning = div.querySelector('.stock-warning');

        const loadStock = () => {
            if (!select) return;
            const color = select.value;
            fetch(`get_inventory.php?product_code=${product.product_code}&color=${encodeURIComponent(color)}`)
                .then(res => res.json())
                .then(inv => {
                    product.stockByColor[color] = inv.quantity;
                    stockSpan.textContent = inv.quantity;
                    addBtn.disabled = inv.quantity <= 0;
                    warning.style.display = inv.quantity <= 0 ? 'block' : 'none';
                });
        };

        if (select) {
            select.addEventListener('change', loadStock);
            loadStock();
        }
    });
}

// ================= GIỎ HÀNG =================
function addToCart(btn) {
    const p = btn.parentElement;
    const name = p.dataset.name;
    const price = parseFloat(p.dataset.price);
    const code = p.dataset.code;
    const image = p.querySelector('img').src;
    const color = p.querySelector('.color-select')?.value || 'Không có màu';

    const product = allProducts.find(p => p.product_code === code);
    const stockQty = product?.stockByColor[color] || 0;

    const existing = cart.find(i => i.name === name && i.color === color);
    if (existing && existing.quantity >= stockQty) { 
        alert('Số lượng vượt quá tồn kho!'); 
        return; 
    }
    if (existing) existing.quantity++; 
    else if (stockQty > 0) cart.push({ name, color, price, quantity:1, image });
    else { 
        alert('Sản phẩm này đã hết hàng!'); 
        return; 
    }

    showNotification(`Đã thêm "${name}" (${color}) vào giỏ hàng!`);
    updateCartDisplay();
    showSection('cart');
}

function showNotification(text) {
    notification.textContent = text;
    notification.style.display = 'block';
    clearTimeout(notification.timer);
    notification.timer = setTimeout(() => notification.style.display = 'none', 1500);
}

function updateCartDisplay() {
    const cartDiv = document.getElementById('cart-items');
    cartDiv.innerHTML = '';
    let count = 0, total = 0;

    if (cart.length === 0) {
        cartDiv.innerHTML = '<p>Giỏ hàng trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';
        document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: 0';
        document.getElementById('total-price').textContent = '0 VNĐ';
        return;
    }

    cart.forEach(item => {
        const div = document.createElement('div');
        div.classList.add('cart-item');

        div.innerHTML = `<img src="${item.image}" style="width:100px; height:100px; margin-right:10px;">
                         <span>${item.name} - <em>${item.color}</em> (x${item.quantity}): ${(item.price*item.quantity).toLocaleString('vi-VN')} VNĐ</span>`;
        
        ['-', '+', 'Xóa'].forEach(action => {
            const btn = document.createElement('button');
            btn.textContent = action;

            btn.onclick = () => {
                const product = allProducts.find(p => p.name === item.name);
                const stockQty = product?.stockByColor[item.color] ?? Infinity;

                if (action === '+') {
                    if (item.quantity < stockQty) item.quantity++;
                    else { alert('Số lượng vượt quá tồn kho!'); return; }
                } else if (action === '-') {
                    if (item.quantity > 1) item.quantity--;
                    else cart.splice(cart.indexOf(item),1);
                } else { // Xóa
                    cart.splice(cart.indexOf(item),1);
                }
                updateCartDisplay();
            };
            div.appendChild(btn);
        });

        cartDiv.appendChild(div);
        count += item.quantity;
        total += item.price * item.quantity;
    });

    totalPrice = total;
    document.getElementById('checkout').style.display = 'block';
    document.getElementById('total-price').textContent = total.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: ' + count;
    document.getElementById('cart-quantity').textContent = count;
}



// Thanh toán 
async function checkout() {
    try {
        const res = await fetch('pay/save_cart.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            credentials: 'same-origin', // ← gửi cookie PHP session
            body: JSON.stringify(cart)
        });
        if (res.ok) window.location.href = 'pay/user_pay.php';
        else res.text().then(t => alert(t)); // hiện thông báo lỗi nếu cần
    } catch(err) { console.error(err); }
}


// Liên hệ
document.getElementById('contact-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new URLSearchParams(new FormData(this));
    fetch('user_contact.php', { method:'POST', body: formData })
        .then(res => res.ok ? (alert('Cảm ơn bạn!'), this.reset()) : res.text().then(t => alert('Lỗi: '+t)));
});

// Trang chủ
async function loadHome() {
    try {
        const data = await fetchJSON('get_home.php');
        const home = document.getElementById('home');
        home.innerHTML = '';
        if (data.banner) {
            const b = document.createElement('div'); b.classList.add('banner');
            b.innerHTML = `<img src="${data.banner.image}" alt="${data.banner.title}">
                           <h1>${data.banner.title}</h1>
                           <p>${data.banner.description}</p>`;
            home.appendChild(b);
        }
        if (data.promotions?.length) {
            const promo = document.createElement('div'); promo.classList.add('promo-grid');
            data.promotions.forEach(item => {
                const d = document.createElement('div'); d.classList.add('promo-item');
                d.innerHTML = `<img src="${item.image}" alt="${item.title}">
                               <h3>${item.title}</h3>
                               <p>${item.description}</p>
                               <a href="${item.link||'#'}" class="cta-btn">Xem chi tiết</a>`;
                promo.appendChild(d);
            });
            home.appendChild(promo);
        }
    } catch(err){ console.error(err); }
}

// Lấy sản phẩm
document.addEventListener('DOMContentLoaded', () => {
    showSection('home');
    fetchJSON('get_products.php').then(data => { allProducts=data; renderProducts(allProducts); });
    loadHome();
    fetchJSON('auto/auto.php').then(user=>{
        document.getElementById('name').value = user.name||'';
        document.getElementById('email').value = user.email||'';
    }).catch(console.error);
});

// Đăng xuất
function logout() { cart=[]; totalPrice=0; updateCartDisplay(); alert('Đăng xuất thành công'); window.location.href='user.html'; }

searchInput.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', () => {
    priceFilter.style.display = categoryFilter.value==='all'?'none':'inline-block';
    priceFilter.value='all';
    applyFilters();
});
priceFilter.addEventListener('change', applyFilters);


