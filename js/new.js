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

    // Hiển thị trang chủ trước
    showSection('home');

    // Lấy toàn bộ sản phẩm
    fetchJSON('get_products.php').then(data => {
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

    // Lấy dữ liệu trang chủ
    fetchJSON('get_home.php').then(renderHome);

});


// ================= LỌC SẢN PHẨM =================
function applyFilters() {
    const kw = searchInput.value.toLowerCase();
    const cat = categoryFilter.value;
    const pr = priceFilter.value;

    const filtered = allProducts.filter(p => {
        const price = parseFloat(p.price.replace(/\./g, '').replace(',', '.'));
        const matchKeyword = [p.name, p.product_code, p.category].some(x => x.toLowerCase().includes(kw));
        const matchCategory = cat === "all" || p.category.toLowerCase() === cat.toLowerCase();
        let matchPrice = true;

        if (cat !== "all") {
            if (pr === "0-100") matchPrice = price < 100000;
            else if (pr === "100-300") matchPrice = price >= 100000 && price <= 300000;
            else if (pr === "300-500") matchPrice = price >= 300000 && price <= 500000;
            else if (pr === "500-1000") matchPrice = price >= 500000 && price <= 1000000;
            else if (pr === "1000+") matchPrice = price > 1000000;
        }

        return matchKeyword && matchCategory && matchPrice;
    });

    renderProducts(filtered);
}

// ================= EVENT LỌC =================
searchInput.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', () => {
    priceFilter.style.display = categoryFilter.value === "all" ? "none" : "inline-block";
    priceFilter.value = "all";
    applyFilters();
});
priceFilter.addEventListener('change', applyFilters);


// ================= RENDER SẢN PHẨM =================
function renderProducts(products) {
    productsContainer.innerHTML = '';

    products.forEach(product => {

        const priceNumber = parseFloat(
            product.price.replace(/\./g, '').replace(',', '.')
        );
        const priceFormatted = priceNumber.toLocaleString('vi-VN');

        // ===== TỒN KHO THEO MÀU =====
        product.stockByColor = product.stockByColor || {};
        const colors = product.color
            ?.split(',')
            .map(c => c.trim())
            .filter(Boolean) || [];

        colors.forEach(c => {
            if (product.stockByColor[c] === undefined) {
                product.stockByColor[c] = 0;
            }
        });

        const colorSelectHTML = colors.length
            ? `
              <div class="color-select-container">
                  <label><strong>Màu sắc:</strong></label>
                  <select class="color-select">
                      ${colors.map(c => `<option value="${c}">${c}</option>`).join('')}
                  </select>
              </div>`
            : `<p><strong>Màu sắc:</strong> Không có</p>`;

        const productDiv = document.createElement('div');
        productDiv.className = 'product';
        productDiv.dataset.name = product.name;
        productDiv.dataset.price = priceNumber;
        productDiv.dataset.code = product.product_code;

        productDiv.innerHTML = `
            <img src="${product.image}" class="product-image"
                 style="width:150px;height:150px;cursor:pointer;">
            <h3>${product.name}</h3>
            <p><strong>Giá:</strong> ${priceFormatted} VNĐ</p>

            ${colorSelectHTML}

            <p><strong>Kho:</strong> <span class="stock">0</span></p>
            <p class="stock-warning" style="display:none;color:red;">
                ❌ Màu này đã hết hàng!
            </p>

            <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
            <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating || 0}</p>
        `;

        productsContainer.appendChild(productDiv);

        // ===== CLICK ẢNH → CHI TIẾT =====
        productDiv.querySelector('.product-image').onclick = () => {
            window.location.href =
                `../products/no_feedback.php?code=${product.product_code}`;
        };

        // ===== LOAD TỒN KHO =====
        const select = productDiv.querySelector('.color-select');
        const stockSpan = productDiv.querySelector('.stock');
        const warning = productDiv.querySelector('.stock-warning');
        const addBtn = productDiv.querySelector('button');

        const loadStock = () => {
            if (!select) return;
            const color = select.value;

            fetchJSON(
                `get_inventory.php?product_code=${product.product_code}&color=${encodeURIComponent(color)}`
            ).then(inv => {
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

// ================= TRANG CHỦ =================
function renderHome(data) {
    const homeSection = document.getElementById('home');
    homeSection.innerHTML = '';

    /* ================= BANNER ================= */
    if (data.banner) {
        const b = document.createElement('div');
        b.className = 'banner';

        b.innerHTML = `
            <img src="${data.banner.image}" alt="${data.banner.title}">
            <div class="banner-content">
                <h1>${data.banner.title}</h1>
                <p>${data.banner.description}</p>
            </div>
        `;

        homeSection.appendChild(b);
    }

    /* ================= KHUYẾN MÃI ================= */
    if (data.promotions && data.promotions.length) {
        const promoSection = document.createElement('div');
        promoSection.className = 'promo-grid';

        data.promotions.forEach(p => {
            const d = document.createElement('div');
            d.className = 'promo-item';

            d.innerHTML = `
                <img src="${p.image}" alt="${p.title}">
                <h3>${p.title}</h3>
                <p>${p.description}</p>
                <a href="${p.link || '#'}" class="cta-btn">Xem chi tiết</a>
            `;

            promoSection.appendChild(d);
        });

        homeSection.appendChild(promoSection);
    }

    /* ================= SẢN PHẨM NỔI BẬT ================= */
    if (data.featured_products && data.featured_products.length) {
        const title = document.createElement('h2');
        title.className = 'section-title';
        title.textContent = 'Sản phẩm nổi bật';
        homeSection.appendChild(title);

        const featuredWrap = document.createElement('div');
        featuredWrap.className = 'featured-grid';

        data.featured_products.forEach(p => {
            // ⚠️ bảo vệ tránh lỗi
            if (!p.product_code) return;

            const item = document.createElement('div');
            item.className = 'featured-item';

          item.innerHTML = `
    <img src="${p.image}" alt="${p.name}" style="cursor:pointer">
    <h4>${p.name}</h4>
    <p class="price">${Number(p.price).toLocaleString()} VNĐ</p>

    <button class="btn-find"
        onclick="goToProduct('${p.product_code}')">
        Khám phá sản phẩm
    </button>
`;
            featuredWrap.appendChild(item);
        });

        homeSection.appendChild(featuredWrap);
    }
}



// Tìm kiếm sản phẩm 
function goToProduct(productCode) {
    // chuyển sang trang sản phẩm
    showSection('products');

    // đợi render xong
    setTimeout(() => {
        // reset filter
        categoryFilter.value = 'all';
        priceFilter.value = 'all';

        // tìm đúng sản phẩm theo mã
        searchInput.value = productCode;

        // lọc lại
        applyFilters();

        // scroll cho UX
        document.getElementById('products')
            .scrollIntoView({ behavior: 'smooth' });

        showNotification('👉 Vui lòng chọn màu sắc để mua sản phẩm');
    }, 150);
}





// ================= FORM ĐĂNG NHẬP / ĐĂNG KÝ =================
document.querySelector('a[href="#login"]').onclick = () => showSection('login-section');
document.querySelector('a[href="#register"]').onclick = () => showSection('register-section');

document.getElementById('register-form').onsubmit = e => {
    e.preventDefault();
    const name = document.getElementById('register-name').value,
          email = document.getElementById('register-email').value,
          pass = document.getElementById('register-password').value;
    const xhr = new XMLHttpRequest();
    xhr.open("POST","user/user_register.php",true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.onload = () => xhr.status===200 ? 
        (xhr.responseText.startsWith("Đăng ký thành công") ? (alert(xhr.responseText), showSection('login-section')) : alert(xhr.responseText)) 
        : alert('Lỗi đăng ký!');
    xhr.send(`register-name=${encodeURIComponent(name)}&register-email=${encodeURIComponent(email)}&register-password=${encodeURIComponent(pass)}`);
};

document.getElementById('login-form').onsubmit = e => {
    const name = document.getElementById('login-name').value,
          email = document.getElementById('login-email').value,
          pass = document.getElementById('login-password').value;
    if (!(name && email && pass)) { 
        e.preventDefault(); 
        alert('Vui lòng điền đầy đủ thông tin!'); 
    }
};


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

