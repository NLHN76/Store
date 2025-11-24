// ================= GIỎ HÀNG TOÀN CỤC =================
let cart = [];
let totalPrice = 0;
let allProducts = [];

const productsContainer = document.getElementById('products-container');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const priceFilter = document.getElementById('priceFilter');
const notification = document.getElementById('notification');

// ================= LOAD SẢN PHẨM TỪ SERVER =================
fetch('get_products.php')
    .then(res => res.json())
    .then(data => {
        allProducts = data;
        renderProducts(allProducts);
    })
    .catch(err => console.error('❌ Lỗi khi tải sản phẩm:', err));

// ================= HIỂN THỊ SECTION =================
function showSection(section) {
    document.querySelectorAll('main > section').forEach(sec => sec.style.display = 'none');
    const activeSection = document.getElementById(section);
    if (activeSection) activeSection.style.display = 'block';
    if (section === 'cart') updateCartDisplay();
}

// ================= LỌC SẢN PHẨM =================
function applyFilters() {
    const keyword = searchInput.value.toLowerCase();
    const category = categoryFilter.value;
    const priceRange = priceFilter.value;

    const filtered = allProducts.filter(p => {
        const price = parseFloat(p.price.replace(/\./g, '').replace(',', '.'));

        const matchKeyword =
            p.name.toLowerCase().includes(keyword) ||
            p.product_code.toLowerCase().includes(keyword) ||
            p.category.toLowerCase().includes(keyword);

        const matchCategory = category === "all" || p.category.toLowerCase() === category.toLowerCase();

        let matchPrice = true;
        if (category !== "all") {
            if (priceRange === "0-100") matchPrice = price < 100000;
            else if (priceRange === "100-300") matchPrice = price >= 100000 && price <= 300000;
            else if (priceRange === "300-500") matchPrice = price >= 300000 && price <= 500000;
            else if (priceRange === "500-1000") matchPrice = price >= 500000 && price <= 1000000;
            else if (priceRange === "1000+") matchPrice = price > 1000000;
        }

        return matchKeyword && matchCategory && matchPrice;
    });

    renderProducts(filtered);
}

// ================= EVENT LỌC =================
searchInput.addEventListener('input', applyFilters);

categoryFilter.addEventListener('change', () => {
    if (categoryFilter.value === "all") {
        priceFilter.style.display = "none";
        priceFilter.value = "all";
    } else {
        priceFilter.style.display = "inline-block";
    }
    applyFilters();
});

priceFilter.addEventListener('change', applyFilters);

// ================= RENDER SẢN PHẨM =================
function renderProducts(data) {
    productsContainer.innerHTML = '';

    data.forEach(product => {
        const productDiv = document.createElement('div');
        productDiv.classList.add('product');

        productDiv.setAttribute('data-name', product.name);
        productDiv.setAttribute('data-price', parseFloat(product.price.replace(/\./g, '').replace(',', '.')));
        productDiv.setAttribute('data-code', product.product_code);

        const colors = product.color ? product.color.split(',').map(c => c.trim()).filter(c => c !== '') : [];

        let inventoryHTML = `
            <p><strong>Kho:</strong> <span class="stock">0</span></p>
            <p><strong>Đã bán:</strong> <span class="sold">0</span></p>
            <p class="text-danger stock-warning" style="display:none;">❌ Màu này đã hết hàng!</p>
        `;

        const colorSelectHTML = colors.length > 0
    ? `<div class="color-select-container">
           <label><strong>Màu sắc:</strong></label>
           <select class="color-select">
               ${colors.map(c => `<option value="${c}">${c}</option>`).join('')}
           </select>
       </div>
       ${inventoryHTML}`
    : `<div class="color-select-container">
           <p><strong>Màu sắc:</strong> Không có tùy chọn</p>
       </div>
       ${inventoryHTML}`;


        const priceNumber = parseFloat(product.price.replace(/\./g, '').replace(',', '.'));
        const priceFormatted = priceNumber.toLocaleString('vi-VN');

        productDiv.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image" style="width:150px; height:150px; cursor:pointer;">
            <h3>${product.name}</h3>
            <p><strong>Mã sản phẩm:</strong> ${product.product_code}</p>
            <p><strong>Giá:</strong> ${priceFormatted} VNĐ</p>
            ${colorSelectHTML}
            <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
            <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating} </p>
        `;

        productsContainer.appendChild(productDiv);

        // Click vào ảnh → chi tiết sản phẩm
        productDiv.querySelector('.product-image').addEventListener('click', () => {
            window.location.href = `product_detail.php?code=${product.product_code}`;
        });

        // Load tồn kho theo màu
        const select = productDiv.querySelector('.color-select');
        const stockSpan = productDiv.querySelector('.stock');
        const soldSpan = productDiv.querySelector('.sold');
        const warning = productDiv.querySelector('.stock-warning');
        const addBtn = productDiv.querySelector('button');

        if (select) {
            const loadStock = () => {
                fetch(`get_inventory.php?product_code=${product.product_code}&color=${encodeURIComponent(select.value)}`)
                    .then(res => res.json())
                    .then(inv => {
                        stockSpan.textContent = inv.quantity;
                        soldSpan.textContent = inv.sold;
                        if (inv.quantity <= 0) {
                            addBtn.disabled = true;
                            warning.style.display = 'block';
                        } else {
                            addBtn.disabled = false;
                            warning.style.display = 'none';
                        }
                    });
            };
            select.addEventListener('change', loadStock);
            loadStock();
        }
    });
}

// ================= THÊM VÀO GIỎ HÀNG =================
function addToCart(button) {
    const product = button.parentElement;
    const productName = product.getAttribute('data-name');
    const price = parseFloat(product.getAttribute('data-price'));
    const image = product.querySelector('img').src;
    const colorSelect = product.querySelector('.color-select');
    const color = colorSelect ? colorSelect.value : 'Không có màu';
    const stockQty = parseInt(product.querySelector('.stock').textContent);

    const existing = cart.find(item => item.name === productName && item.color === color);

    if (existing && existing.quantity >= stockQty) {
        alert('Số lượng vượt quá tồn kho!');
        return;
    }

    if (existing) existing.quantity++;
    else {
        if (stockQty <= 0) { alert('Sản phẩm này đã hết hàng!'); return; }
        cart.push({ name: productName, color, price, quantity: 1, image });
    }

    updateCartDisplay();

    notification.textContent = `Đã thêm "${productName}" (${color}) vào giỏ hàng!`;
    notification.style.display = 'block';
    setTimeout(() => notification.style.display = 'none', 1000);

    showSection('cart');
}

// ================= HIỂN THỊ GIỎ HÀNG =================
function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    cartItemsDiv.innerHTML = '';
    let itemCount = 0;
    totalPrice = 0;

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p>Giỏ hàng trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';
        document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: 0';
        document.getElementById('total-price').textContent = '0 VNĐ';
        return;
    }

    cart.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.classList.add('cart-item');

        const colorText = item.color ? ` - <em>${item.color}</em>` : '';

        itemDiv.innerHTML = `
            <img src="${item.image}" style="width:100px; height:100px; margin-right:10px;">
            <span>${item.name}${colorText} (x${item.quantity}): ${(item.price * item.quantity).toLocaleString('vi-VN')} VNĐ</span>
        `;

        const increaseBtn = document.createElement('button');
        increaseBtn.textContent = '+';
        increaseBtn.onclick = () => { item.quantity++; updateCartDisplay(); };

        const decreaseBtn = document.createElement('button');
        decreaseBtn.textContent = '-';
        decreaseBtn.onclick = () => {
            if (item.quantity > 1) item.quantity--;
            else cart = cart.filter(c => !(c.name === item.name && c.color === item.color));
            updateCartDisplay();
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Xóa';
        deleteBtn.onclick = () => { cart = cart.filter(c => !(c.name === item.name && c.color === item.color)); updateCartDisplay(); };

        itemDiv.appendChild(decreaseBtn);
        itemDiv.appendChild(increaseBtn);
        itemDiv.appendChild(deleteBtn);
        cartItemsDiv.appendChild(itemDiv);

        itemCount += item.quantity;
        totalPrice += item.price * item.quantity;
    });

    document.getElementById('checkout').style.display = 'block';
    document.getElementById('total-price').textContent = totalPrice.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('total-quantity').textContent = 'Tổng sản phẩm: ' + itemCount;
    document.getElementById('cart-quantity').textContent = itemCount;
}




// Hàm thanh toán đặt hàng online                                         
function checkout() {
    // Gửi giỏ hàng đến server để lưu vào session
    fetch('pay/save_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(cart)
    }).then(response => {
        if (response.ok) {
            // Sau khi lưu thành công, chuyển hướng đến trang thanh toán
            window.location.href = 'pay/user_pay.php';
        } else {
            alert('Có lỗi xảy ra khi lưu giỏ hàng.');
        }
    }).catch(error => {
        console.error('Lỗi:', error);
    });
}






// Hàm sự kiện khi gửi biểu mẫu liên hệ
document.getElementById('contact-form').addEventListener('submit', function(event) {
event.preventDefault(); // Ngăn không cho gửi biểu mẫu

// Gửi dữ liệu qua AJAX 
const xhr = new XMLHttpRequest();
xhr.open("POST", "user_contact.php", true);
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

// Lấy dữ liệu từ biểu mẫu
const name = document.getElementById('name').value;
const email = document.getElementById('email').value;
const phone = document.getElementById('phone').value;
const message = document.getElementById('message').value;

// Gửi dữ liệu
xhr.send(`name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&message=${encodeURIComponent(message)}`);

// Xử lý phản hồi từ máy chủ
xhr.onload = function() {
if (xhr.status === 200) {
    alert('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.');
    document.getElementById('contact-form').reset(); // Đặt lại biểu mẫu
} else {
    alert('Có lỗi xảy ra khi gửi liên hệ: ' + xhr.responseText);
}
};
});








//Lấy thông tin trang chủ 
fetch('get_home.php')
  .then(response => response.json())
  .then(data => {
    const homeSection = document.getElementById('home');
    homeSection.innerHTML = ''; // Xóa nội dung cũ

    // --- Banner ---
    if (data.banner) {
      const bannerDiv = document.createElement('div');
      bannerDiv.classList.add('banner');

      const bannerImg = document.createElement('img');
      bannerImg.src = data.banner.image;
      bannerImg.alt = data.banner.title;

      const bannerTitle = document.createElement('h1');
      bannerTitle.textContent = data.banner.title;

      const bannerDesc = document.createElement('p');
      bannerDesc.textContent = data.banner.description;

      bannerDiv.appendChild(bannerImg);
      bannerDiv.appendChild(bannerTitle);
      bannerDiv.appendChild(bannerDesc);
      homeSection.appendChild(bannerDiv);
    }

    // --- Khuyến mãi ---
    if (data.promotions && data.promotions.length > 0) {
      const promoSection = document.createElement('div');
      promoSection.classList.add('promo-grid');

      data.promotions.forEach(item => {
        const promoDiv = document.createElement('div');
        promoDiv.classList.add('promo-item');

        const title = document.createElement('h3');
        title.textContent = item.title;

        const description = document.createElement('p');
        description.textContent = item.description;

        const image = document.createElement('img');
        image.src = item.image;
        image.alt = item.title;

        const ctaBtn = document.createElement('a');
        ctaBtn.href = item.link || "#";
        ctaBtn.textContent = "Xem chi tiết";
        ctaBtn.classList.add('cta-btn');

        promoDiv.appendChild(image);
        promoDiv.appendChild(title);
        promoDiv.appendChild(description);
        promoDiv.appendChild(ctaBtn);

        promoSection.appendChild(promoDiv);
      });

      homeSection.appendChild(promoSection);
    }
  })
  .catch(error => console.error('Error:', error));







// Hàm đăng xuất
function logout() {
    cart = [];
    totalPrice = 0;
    updateCartDisplay();
    alert('Bạn đã đăng xuất thành công!');
    window.location.href = 'user.html';
}







function showSection(sectionId) {
    // Ẩn tất cả các phần
    const sections = document.querySelectorAll('main > section, .container');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Hiển thị phần được chọn
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }

    // Hiển thị footer chỉ nếu đang ở trang chủ
    const footer = document.querySelector('footer');
    if (sectionId === 'home') {
        footer.style.display = 'block'; // Hiện footer
    } else {
        footer.style.display = 'none'; // Ẩn footer
    }
}

// Mặc định hiển thị trang chủ và footer
document.addEventListener("DOMContentLoaded", function() {
    showSection('home'); // Hiện trang chủ khi tải trang
});





     // Hàm để lấy thông tin người dùng từ tệp PHP
     async function fetchUserData() {
        try {
            const response = await fetch('auto/auto.php');
            if (!response.ok) {
                throw new Error('Mất kết nối tới máy chủ');
            }
            const user = await response.json();
            document.getElementById('name').value = user.name || '';
            document.getElementById('email').value = user.email || '';
        } catch (error) {
            console.error(error);
        }
    }
    // Gọi hàm để lấy dữ liệu khi trang được tải
    document.addEventListener('DOMContentLoaded', fetchUserData);










