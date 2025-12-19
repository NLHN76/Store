
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÍ PHỤ KIỆN ĐIỆN THOẠI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/interface.css">
  
</head>
<body>
    <div class="admin-wrapper">
        <header>
            <h1><i class="fas fa-tachometer-alt"></i> ADMIN</h1>
            <nav>
                <ul>
                    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </nav>
        </header>

        <aside class="sidebar">
            <ul>
                <li class="active"><a href="home/admin_home.php"><i class="fas fa-home"></i> Quản lý Trang chủ</a></li>
                <li><a href="user_management/admin_users.php"><i class="fas fa-users"></i> Quản lý Khách hàng</a></li>
                <li><a href="products/admin_products.php"><i class="fas fa-boxes"></i> Quản lý sản phẩm</a></li>
              <li>
  <a href="orders/admin_orders.php">
    <i class="fas fa-shopping-cart"></i>
    Đơn hàng online
    <span id="alert-orders" class="new-alert" style="display:none">!</span>
  </a>
</li>

<li>
  <a href="contact/admin_contact.php">
    <i class="fas fa-envelope"></i>
    Liên hệ
    <span id="alert-contact" class="new-alert" style="display:none">!</span>
  </a>
</li>

<li>
  <a href="chat/admin_chat.php">
    <i class="fas fa-comments"></i>
    Hỗ trợ khách hàng
    <span id="alert-chat" class="new-alert" style="display:none">!</span>
  </a>
</li>

 
                <li><a href="report/admin_report.php"><i class="fas fa-chart-bar"></i> Thống kê</a></li>

       <li>
        <a href="inventory/admin_inventory.php">
            <i class="fas fa-warehouse"></i>
            Quản lý Kho
            <span id="alert-inventory" class="new-alert" style="display:none">!</span>
        </a>
    </li>
            </ul>
        </aside>

        <main>
            <div class="content-box">
                <h3><i class="fas fa-info-circle"></i> Thông tin</h3>
                <p>Chào mừng đến với trang quản lý phụ kiện điện thoại.</p>
            </div>
        </main>
    </div>


<script src="js/admin_interface.js"></script>
</body>


</html>



