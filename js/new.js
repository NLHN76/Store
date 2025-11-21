// Biến toàn cục (chỉ tồn tại trong phiên hiện tại)
let cart = [];
let totalPrice = 0;

// DOM
const productsContainer = document.getElementById('products-container');
const searchInput = document.getElementById('searchInput');
const notification = document.getElementById('notification');

// Load sản phẩm từ server
let allProducts = [];
fetch('get_products.php')
    .then(res => res.json())
    .then(data => {
        allProducts = data;
        renderProducts(allProducts);
    })
    .catch(err => console.error('❌ Lỗi khi tải sản phẩm:', err));

// Hiển thị section
function showSection(section) {
    const sections = document.querySelectorAll('main > section');
    sections.forEach(sec => sec.style.display = 'none');

    const activeSection = document.getElementById(section);
    if (activeSection) activeSection.style.display = 'block';

    if (section === 'cart') updateCartDisplay();
}

// Render sản phẩm
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
            ? `<label><strong>Chọn màu:</strong></label>
               <select class="color-select" style="margin:4px 0; padding:4px; border-radius:6px;">
                   ${colors.map(c => `<option value="${c}">${c}</option>`).join('')}
               </select>
               ${inventoryHTML}`
            : `<p><strong>Màu sắc:</strong> Không có tùy chọn</p>${inventoryHTML}`;

        const priceNumber = parseFloat(product.price.replace(/\./g, '').replace(',', '.'));
        const priceFormatted = priceNumber.toLocaleString('vi-VN');

        // ======================
        //  ẢNH BẤM ĐỂ XEM CHI TIẾT
        // ======================
        productDiv.innerHTML = `
            <img src="${product.image}" 
                 alt="${product.name}" 
                 class="product-image"
                 style="width:150px; height:150px; cursor:pointer;">

            <h3>${product.name}</h3>
            <p><strong>Mã sản phẩm:</strong> ${product.product_code}</p>
            <p><strong>Loại sản phẩm:</strong> ${product.category}</p>
            <p><strong>Giá:</strong> ${priceFormatted} VNĐ</p>
            ${colorSelectHTML}
            <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
            <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating} / 5 (${product.total_reviews} lượt đánh giá)</p>
        `;

        productsContainer.appendChild(productDiv);

        // Sự kiện click vào ảnh
        const img = productDiv.querySelector('.product-image');
        img.addEventListener('click', () => {
            window.location.href = `no_feedback.php?code=${product.product_code}`;
        });

        // Tồn kho theo màu
        const select = productDiv.querySelector('.color-select');
        const stockSpan = productDiv.querySelector('.stock');
        const soldSpan = productDiv.querySelector('.sold');
        const addBtn = productDiv.querySelector('button');
        const warning = productDiv.querySelector('.stock-warning');

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

// Tìm kiếm sản phẩm
searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase();
    const filtered = allProducts.filter(p =>
        p.name.toLowerCase().includes(keyword) ||
        p.product_code.toLowerCase().includes(keyword) ||
        p.category.toLowerCase().includes(keyword)
    );
    renderProducts(filtered);
});

// Thêm sản phẩm vào giỏ hàng
function addToCart(button) {
    const product = button.parentElement;
    const productName = product.getAttribute('data-name');
    const price = parseFloat(product.getAttribute('data-price'));
    const image = product.querySelector('img') ? product.querySelector('img').src : '';
    const colorSelect = product.querySelector('.color-select');
    const color = colorSelect ? colorSelect.value : 'Không có màu';
    const stockSpan = product.querySelector('.stock');
    const stockQty = parseInt(stockSpan ? stockSpan.textContent : '0');

    const existing = cart.find(item => item.name === productName && item.color === color);
    if (existing && existing.quantity >= stockQty) {
        alert('Sản phẩm đã đạt số lượng tối đa trong kho!');
        return;
    }

    if (existing) {
        existing.quantity++;
    } else {
        if (stockQty <= 0) {
            alert('Sản phẩm này đã hết hàng!');
            return;
        }
        cart.push({ name: productName, color, price, quantity: 1, image });
    }

    updateCartDisplay();

    notification.textContent = `Đã thêm "${productName}" (${color}) vào giỏ hàng!`;
    notification.style.display = 'block';
    setTimeout(() => notification.style.display = 'none', 1000);

    showSection('cart');
}

// Hiển thị giỏ hàng
function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    cartItemsDiv.innerHTML = '';
    let itemCount = 0;
    totalPrice = 0;

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p>Giỏ hàng của bạn trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';
        document.getElementById('total-quantity').textContent = 'Tổng số sản phẩm: 0';
        document.getElementById('total-price').textContent = '0 VNĐ';
        return;
    }

    cart.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.classList.add('cart-item');

        const colorText = item.color ? ` - <em>${item.color}</em>` : '';
        itemDiv.innerHTML = `
            <img src="${item.image}" alt="${item.name}" style="width:100px; height:100px; margin-right:10px;">
            <span>${item.name}${colorText} (x${item.quantity}): ${(item.price * item.quantity).toLocaleString('vi-VN')} VNĐ</span>
        `;

        const increaseBtn = document.createElement('button');
        increaseBtn.textContent = '+';
        increaseBtn.onclick = () => { item.quantity++; updateCartDisplay(); };

        const decreaseBtn = document.createElement('button');
        decreaseBtn.textContent = '-';
        decreaseBtn.onclick = () => {
            if (item.quantity > 1) item.quantity--;
            else cart = cart.filter(cartItem => !(cartItem.name === item.name && cartItem.color === item.color));
            updateCartDisplay();
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Xóa';
        deleteBtn.onclick = () => {
            cart = cart.filter(cartItem => !(cartItem.name === item.name && cartItem.color === item.color));
            updateCartDisplay();
        };

        itemDiv.appendChild(decreaseBtn);
        itemDiv.appendChild(increaseBtn);
        itemDiv.appendChild(deleteBtn);
        cartItemsDiv.appendChild(itemDiv);

        itemCount += item.quantity;
        totalPrice += item.price * item.quantity;
    });

    document.getElementById('checkout').style.display = 'block';
    document.getElementById('total-price').textContent = totalPrice.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('total-quantity').textContent = 'Tổng số sản phẩm: ' + itemCount;
    document.getElementById('cart-quantity').textContent = itemCount;
}





//Cảnh báo cần đăng nhập để mua hàng 
document.addEventListener("DOMContentLoaded", function () {
    const checkoutButton = document.getElementById("checkout");

    checkoutButton.addEventListener("click", function () {
        // Kiểm tra trạng thái đăng nhập
        const isLoggedIn = false; // Thay đổi giá trị này dựa trên trạng thái đăng nhập thực tế

        if (!isLoggedIn) {
            showNotification("Bạn cần đăng nhập để tiếp tục mua hàng");
        } else {
            // Xử lý logic đặt hàng nếu người dùng đã đăng nhập
            alert("Đặt hàng thành công!");
        }
    });

    function showNotification(message) {
        const notification = document.getElementById("notification");
        notification.textContent = message;
        notification.style.display = "block";

        // Ẩn thông báo sau 3 giây
        setTimeout(() => {
            notification.style.display = "none";
        }, 3000);
    }
});





// Gọi hàm này khi nhấn vào Đăng Nhập
document.querySelector('a[href="#login"]').addEventListener('click', function() {
    showSection('login-section'); // Hiện phần đăng nhập
});

// Gọi hàm này khi nhấn vào Đăng Ký
document.querySelector('a[href="#register"]').addEventListener('click', function() {
    showSection('register-section'); // Hiện phần đăng ký
});



// Xử lý sự kiện khi nhấn nút Đăng Ký
document.getElementById('register-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Ngăn không cho gửi biểu mẫu

    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "user_register.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = xhr.responseText.trim();
            
            if (response.startsWith("Đăng ký thành công")) {
                alert(response); // Hiện cả thông báo thành công + mã khách hàng
                showSection('login-section'); // Sau đó chuyển sang giao diện Đăng nhập
            } else {
                alert(response); // Nếu có lỗi, vẫn thông báo lỗi
            }
        } else {
            alert('Có lỗi xảy ra khi đăng ký. Vui lòng thử lại sau.');
        }
    };

    const params = `register-name=${encodeURIComponent(name)}&register-email=${encodeURIComponent(email)}&register-password=${encodeURIComponent(password)}`;
    xhr.send(params);
});




// Xử lý sự kiện khi nhấn nút Đăng Nhập
document.getElementById('login-form').addEventListener('submit', function(event) {
    // Lấy thông tin đăng nhập từ biểu mẫu
    const name = document.getElementById('login-name').value;
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    // Kiểm tra thông tin đăng nhập
    if (name && email && password) {
        // Thực hiện logic đăng nhập 
    } else {
        event.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin!');
    }
});






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
    
    

// Lấy thông tin trang chủ  
fetch('get_home.php')
  .then(response => response.json())
  .then(data => {
    const homeSection = document.getElementById('home');
    homeSection.innerHTML = ''; 

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




function togglePassword(inputId) {
    const passwordField = document.getElementById(inputId);
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
}






