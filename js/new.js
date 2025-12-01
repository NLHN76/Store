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
    document.querySelectorAll('main > section, .container').forEach(s => s.style.display = 'none');
    const sec = document.getElementById(sectionId);
    if (sec) sec.style.display = 'block';
    document.querySelector('footer').style.display = sectionId === 'home' ? 'block' : 'none';
}
document.addEventListener("DOMContentLoaded", () => showSection('home'));

// ================= FETCH DỮ LIỆU =================
fetchJSON('get_products.php').then(data => { 
    allProducts = data; 

    // Khởi tạo tồn kho cho từng màu
    allProducts.forEach(p => {
        p.stock = {};
        const colors = p.color?.split(',').map(c => c.trim()).filter(Boolean) || [];
        colors.forEach(c => p.stock[c] = 0); // sẽ fetch tồn kho thực sau
    });

    renderProducts(allProducts); 
});
fetchJSON('get_home.php').then(renderHome);

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
        const priceNumber = parseFloat(product.price.replace(/\./g, '').replace(',', '.'));
        const priceFormatted = priceNumber.toLocaleString('vi-VN');

        const colors = product.color?.split(',').map(c => c.trim()).filter(Boolean) || [];
        const colorSelectHTML = colors.length
            ? `<div class="color-select-container">
                   <label><strong>Màu sắc:</strong></label>
                   <select class="color-select">
                       ${colors.map(c => `<option value="${c}">${c}</option>`).join('')}
                   </select>
               </div>`
            : `<div class="color-select-container">
                   <p><strong>Màu sắc:</strong> Không có tùy chọn</p>
               </div>`;

        const inventoryHTML = `
            <p><strong>Kho:</strong> <span class="stock">0</span></p>
            <p class="text-danger stock-warning" style="display:none;">❌ Màu này đã hết hàng!</p>
        `;

        const productDiv = document.createElement('div');
        productDiv.className = 'product';
        productDiv.dataset.name = product.name;
        productDiv.dataset.price = priceNumber;
        productDiv.dataset.code = product.product_code;

        productDiv.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image" style="width:150px;height:150px;cursor:pointer;">
            <h3>${product.name}</h3>
            <p><strong>Giá:</strong> ${priceFormatted} VNĐ</p>
            ${colorSelectHTML}
            ${inventoryHTML}
            <button>Thêm vào giỏ hàng</button>
            <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating}</p>
        `;

        productsContainer.appendChild(productDiv);

        // Click ảnh → mở chi tiết
        productDiv.querySelector('.product-image').onclick =
            () => window.location.href = `no_feedback.php?code=${product.product_code}`;

        // Load tồn kho theo màu
        const select = productDiv.querySelector('.color-select');
        const stockSpan = productDiv.querySelector('.stock');
        const warning = productDiv.querySelector('.stock-warning');
        const addBtn = productDiv.querySelector('button');

        const loadStock = () => {
            if (!select) return;
            const color = select.value;
            fetchJSON(`get_inventory.php?product_code=${product.product_code}&color=${encodeURIComponent(color)}`)
                .then(inv => {
                    product.stock[color] = inv.quantity;
                    stockSpan.textContent = inv.quantity;
                    addBtn.disabled = inv.quantity <= 0;
                    warning.style.display = inv.quantity <= 0 ? 'block' : 'none';
                });
        };

        if (select) {
            select.addEventListener('change', loadStock);
            loadStock();
        }

        addBtn.onclick = () => addToCart(productDiv);
    });
}

// ================= GIỎ HÀNG =================
function addToCart(productDiv) {
    const productName = productDiv.dataset.name;
    const colorSelect = productDiv.querySelector('.color-select');
    const color = colorSelect?.value || 'Không có màu';
    const product = allProducts.find(p => p.name === productName);
    if (!product) return alert("Sản phẩm không tồn tại!");

    const stockQty = product.stock[color] || 0;
    const existing = cart.find(i => i.name === productName && i.color === color);

    if (existing) {
        if (existing.quantity >= stockQty) return alert(`Số lượng vượt quá tồn kho!`);
        existing.quantity++;
    } else {
        if (stockQty <= 0) return alert('Sản phẩm này đã hết hàng!');
        cart.push({
            name: productName,
            color,
            price: parseFloat(product.price.replace(/\./g,'')),
            quantity: 1,
            image: productDiv.querySelector('img').src
        });
    }

    updateCartDisplay();
    showNotification(`Đã thêm "${productName}" (${color}) vào giỏ hàng!`);
    showSection('cart');
}

function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    cartItemsDiv.innerHTML = '';
    let itemCount = 0;
    totalPrice = 0;

    if (!cart.length) {
        cartItemsDiv.innerHTML = '<p>Giỏ hàng trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';
        document.getElementById('total-quantity').textContent = '0';
        document.getElementById('total-price').textContent = '0 VNĐ';
        return;
    }

    cart.forEach(item => {
        const product = allProducts.find(p => p.name === item.name);
        const stockQty = product?.stock[item.color] || 0;

        const div = document.createElement('div');
        div.className = 'cart-item';

        div.innerHTML = `
            <img src="${item.image}" style="width:100px;height:100px;margin-right:10px;">
            <span>${item.name} - <em>${item.color}</em> (x${item.quantity}): ${(item.price * item.quantity).toLocaleString('vi-VN')} VNĐ</span>
        `;

        // Nút -
        const btnMinus = document.createElement('button');
        btnMinus.textContent = '-';
        btnMinus.onclick = () => {
            if (item.quantity > 1) item.quantity--;
            else cart = cart.filter(c => !(c.name === item.name && c.color === item.color));
            updateCartDisplay();
        };
        div.appendChild(btnMinus);

        // Nút +
        const btnPlus = document.createElement('button');
        btnPlus.textContent = '+';
        btnPlus.onclick = () => {
            if (item.quantity >= stockQty) return alert(`Số lượng vượt quá tồn kho!`);
            item.quantity++;
            updateCartDisplay();
        };
        div.appendChild(btnPlus);

        // Nút Xóa
        const btnDelete = document.createElement('button');
        btnDelete.textContent = 'Xóa';
        btnDelete.onclick = () => {
            cart = cart.filter(c => !(c.name === item.name && c.color === item.color));
            updateCartDisplay();
        };
        div.appendChild(btnDelete);

        cartItemsDiv.appendChild(div);

        itemCount += item.quantity;
        totalPrice += item.price * item.quantity;
    });

    document.getElementById('checkout').style.display = 'block';
    document.getElementById('total-price').textContent = totalPrice.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('cart-quantity').textContent = itemCount;
    document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: ' + itemCount;
}


// ================= TRANG CHỦ =================
function renderHome(data) {
    const homeSection = document.getElementById('home');
    homeSection.innerHTML = '';

    // Banner
    if (data.banner) {
        const b = document.createElement('div'); b.className = 'banner';
        b.innerHTML = `<img src="${data.banner.image}" alt="${data.banner.title}">
                       <h1>${data.banner.title}</h1>
                       <p>${data.banner.description}</p>`;
        homeSection.appendChild(b);
    }

    // Promotions
    if (data.promotions?.length) {
        const promoSection = document.createElement('div'); promoSection.className = 'promo-grid';
        data.promotions.forEach(p => {
            const d = document.createElement('div'); d.className = 'promo-item';
            d.innerHTML = `<img src="${p.image}" alt="${p.title}">
                           <h3>${p.title}</h3>
                           <p>${p.description}</p>
                           <a href="${p.link||'#'}" class="cta-btn">Xem chi tiết</a>`;
            promoSection.appendChild(d);
        });
        homeSection.appendChild(promoSection);
    }
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
    xhr.open("POST","user_register.php",true);
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
