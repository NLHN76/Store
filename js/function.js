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
                <img src="${item.image}" alt="${item.name}" style="width: 100px; height: 100px; margin-right: 10px;">
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
                      <a href="product_detail.php?code=${product.product_code}">
                    <button>Xem chi tiết</button>
                </a>
            `;

            productsContainer.appendChild(productDiv);
        });
    })
    .catch(error => console.error('Lỗi:', error));
});






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



    
// Đặt hàng thông tin đơn hàng
    async function fetchOrders() {
        try {
            const response = await fetch('order.php'); // Gọi API lấy đơn hàng
            const data = await response.json();
    
            // Kiểm tra nếu có lỗi từ API
            if (data.status === 'error') {
                document.getElementById('order-list').innerHTML = `<p>${data.message}</p>`;
                return;
            }
    
            let ordersHtml = '';
            // Duyệt qua tất cả các đơn hàng và hiển thị
            data.forEach(order => {
                const formattedPrice = parseInt(order.total_price, 10).toLocaleString('de-DE'); // Định dạng không có phần thập phân
              
                ordersHtml += `
                    <div class="order-item">
                        <p><strong>Mã Đơn Hàng:</strong> ${order.id}</p>
                        <p><strong>Mã Khách Hàng :</strong> ${order.user_code}</p>
                        <p><strong>Sản Phẩm:</strong> ${order.product_name}</p> 
                        <p><strong>Loại Sản Phẩm:</strong> ${order.category}</p> 
                        <p><strong>Số Lượng:</strong> ${order.product_quantity}</p> 
                        <p><strong>Tổng Tiền:</strong> ${formattedPrice} VNĐ</p> 
                        <p><strong>Ngày Thanh Toán:</strong> ${order.order_date}</p>
                        <p><strong>Trạng Thái:</strong> ${order.status}</p> 
                    </div>
                    <hr>
                `;
            });
    
            // Đưa kết quả vào phần hiển thị đơn hàng
            document.getElementById('order-list').innerHTML = ordersHtml;
        } catch (error) {
            console.error('Lỗi khi tải đơn hàng:', error);
            document.getElementById('order-list').innerHTML = '<p>Không thể tải thông tin đơn hàng. Vui lòng thử lại sau.</p>';
        }
    }
    
    // Gọi hàm khi trang được tải
    document.addEventListener('DOMContentLoaded', fetchOrders);
    








