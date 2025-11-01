 // Biến toàn cục để lưu giỏ hàng
let cart = [];
let totalPrice = 0;

// Hàm hiển thị phần tương ứng
function showSection(section) {
    const sections = document.querySelectorAll('main > section');
    sections.forEach(sec => {
        sec.style.display = 'none'; // Ẩn tất cả các phần
    });

    const activeSection = document.getElementById(section);
    if (activeSection) {
        activeSection.style.display = 'block'; // Hiển thị phần đã chọn
    }

    if (section === 'cart') {
        updateCartDisplay(); // Cập nhật giỏ hàng khi vào trang giỏ hàng
    }
}

// Hàm thêm sản phẩm vào giỏ hàng
function addToCart(button) {
    const product = button.parentElement;
    const productName = product.getAttribute('data-name');
    const price = parseFloat(product.getAttribute('data-price'));
    const image = product.querySelector('img').getAttribute('src');

    const existingProduct = cart.find(item => item.name === productName);
    if (existingProduct) {
        existingProduct.quantity++;
    } else {
        cart.push({ name: productName, price, quantity: 1, image });
    }

    totalPrice += price;

    // Lưu lại vào localStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    // Thông báo
    const notification = document.getElementById('notification');
    notification.textContent = `Sản phẩm "${productName}" đã được thêm vào giỏ hàng!`;
    notification.style.display = 'block';
    setTimeout(() => {
        notification.style.display = 'none';
    }, 1000);

    // ✅ Cập nhật giỏ hàng và hiển thị section cart
    updateCartDisplay();
    showSection('cart'); // <-- Thêm dòng này để hiển thị giỏ hàng ngay
}

// Hiển thị giỏ hàng
function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    cartItemsDiv.innerHTML = '';
    let itemCount = 0;
    totalPrice = 0;

    cart = JSON.parse(localStorage.getItem('cart')) || [];

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p>Giỏ hàng của bạn trống.</p>';
        document.getElementById('checkout').style.display = 'none';
        document.getElementById('cart-quantity').textContent = '0';
    } else {
        cart.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('cart-item');
            itemDiv.innerHTML = `
                <img src="${item.image}" alt="${item.name}" style="width:100px; height: 100px; margin-right: 10px;">
                <span>${item.name} (x${item.quantity}): ${item.price * item.quantity} VNĐ</span>
            `;

            // Nút tăng
            const increaseBtn = document.createElement('button');
            increaseBtn.textContent = '+';
            increaseBtn.onclick = function () {
                item.quantity++;
                saveAndUpdate();
            };

            // Nút giảm
            const decreaseBtn = document.createElement('button');
            decreaseBtn.textContent = '-';
            decreaseBtn.onclick = function () {
                if (item.quantity > 1) {
                    item.quantity--;
                } else {
                    cart = cart.filter(cartItem => cartItem.name !== item.name);
                }
                saveAndUpdate();
            };

            // Nút xoá
            const deleteBtn = document.createElement('button');
            deleteBtn.textContent = 'Xóa';
            deleteBtn.onclick = function () {
                cart = cart.filter(cartItem => cartItem.name !== item.name);
                saveAndUpdate();
            };

            itemDiv.appendChild(decreaseBtn);
            itemDiv.appendChild(increaseBtn);
            itemDiv.appendChild(deleteBtn);
            cartItemsDiv.appendChild(itemDiv);

            itemCount += item.quantity;
            totalPrice += item.price * item.quantity;
        });

        document.getElementById('checkout').style.display = 'block';
    }

    document.getElementById('total-price').textContent = totalPrice + ' VNĐ';
    document.getElementById('total-quantity').textContent = 'Tổng số sản phẩm: ' + itemCount;
    document.getElementById('cart-quantity').textContent = itemCount;

    // Cập nhật localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Lưu và cập nhật lại hiển thị
function saveAndUpdate() {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

                      

// Lấy dữ liệu sản phẩm từ admin 
document.addEventListener("DOMContentLoaded", function() {
    fetch('get_products.php')
    .then(response => response.json())
    .then(data => {
        const productsContainer = document.getElementById('products');
        productsContainer.innerHTML = ''; // Xóa nội dung hiện tại

        data.forEach(product => {
            const productDiv = document.createElement('div');
            productDiv.classList.add('product');
            productDiv.setAttribute('data-name', product.name);
            productDiv.setAttribute('data-price', product.price);
            productDiv.setAttribute('data-code', product.product_code); 
            productDiv.setAttribute('data-category', product.category); 

            productDiv.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <h3>${product.name}</h3>
                <p><strong>Mã sản phẩm:</strong> ${product.product_code}</p> 
                <p><strong>Loại sản phẩm:</strong> ${product.category}</p> 
                <p><strong>Giá:</strong> ${product.price} VNĐ</p>
                <button onclick="addToCart(this)">Thêm vào giỏ hàng</button>
                      <a href="no_feedback.php?code=${product.product_code}">
                    <button>Xem chi tiết</button>
                </a>
                <p><strong>Đánh giá:</strong> ⭐ ${product.avg_rating} / 5 (${product.total_reviews} lượt đánh giá)</p>

            `;

            productsContainer.appendChild(productDiv);
        });
    })
    .catch(error => console.error('Lỗi:', error));
});




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
    
    








// Hàm tìm kiếm sản phẩm khi form được submit
function searchProduct(event) {
    event.preventDefault(); // Ngừng việc gửi form theo cách truyền thống

    var query = document.getElementById("search-query").value;
    
    // Kiểm tra nếu từ khóa tìm kiếm không trống
    if (query.trim() === "") {
        document.getElementById("search-results").innerHTML = "Vui lòng nhập từ khóa tìm kiếm.";
        return;
    }

    // Sử dụng fetch API để gửi yêu cầu AJAX
    fetch("search.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "search_query=" + encodeURIComponent(query) // Gửi từ khóa tìm kiếm
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById("search-results").innerHTML = data; // Hiển thị kết quả trả về
    })
    .catch(error => {
        document.getElementById("search-results").innerHTML = "Có lỗi xảy ra, vui lòng thử lại.";
        console.error("Error:", error);
    });
}







// Lấy dữ liệu trang chủ từ admin 
fetch('get_home.php')
.then(response => response.json()) // Chuyển đổi phản hồi sang JSON
.then(data => {
const homeSection = document.getElementById('home'); // Chọn phần tử section với id là "home"

// Duyệt qua từng phần tử trong mảng dữ liệu và hiển thị
data.forEach(item => {
    // Tạo phần tử div cho từng mục khuyến mãi
    const promoDiv = document.createElement('div');
    promoDiv.classList.add('promo-item'); 

    // Tạo phần tử cho tiêu đề
    const title = document.createElement('h3');
    title.textContent = item.title;

    // Tạo phần tử cho mô tả
    const description = document.createElement('p');
    description.textContent = item.description;

    // Tạo phần tử cho hình ảnh
    const image = document.createElement('img');
    image.src = item.image;
    image.alt = item.title;

    // Thêm tất cả các phần tử vào promoDiv
    promoDiv.appendChild(title);
    promoDiv.appendChild(image);
    promoDiv.appendChild(description);

    // Thêm promoDiv vào section "home"
    homeSection.appendChild(promoDiv);
});
})
.catch(error => console.error('Error:', error)); // Bắt lỗi nếu có








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






