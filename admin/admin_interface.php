<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÍ PHỤ KIỆN ĐIỆN THOẠI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif; 
            background-color: #e9ecef; 
            color: #495057; 
        }

        .admin-wrapper {
            display: flex;
            height: 100vh;
        }

       
        header {
            background-color: #ffffff; 
            color: #343a40; 
            padding: 20px;
            text-align: left; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between; 
            align-items: center; 
        }

        header h1 {
            font-size: 1.75rem;
            margin: 0;
            font-weight: 500;
        }

        nav ul {
            display: flex;
            justify-content: flex-end;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-left: 20px;
        }

        nav ul li a {
            color: #495057; 
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        nav ul li a:hover {
            background-color: #dee2e6; 
        }

   
        .sidebar {
            width: 260px;
            background-color: #343a40;
            color: #fff;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.08);
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 15px 20px;
            text-align: left; 
            border-bottom: 1px solid #495057; 
        }

        .sidebar ul li:last-child {
            border-bottom: none; 
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s ease;
            padding: 8px 0;
        }

        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.05); 
        }

        .sidebar ul li.active a {
            background-color: #007bff; 
        }


        main {
            flex-grow: 1;
            padding: 30px;
            background-color: #e9ecef; 
        }

        main h2 {
            font-size: 2rem;
            margin-bottom: 25px;
            color: #343a40; 
        }

      
        .content-box {
            background-color: #ffffff; 
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .content-box h3 {
            margin-bottom: 20px;
            font-size: 1.75rem;
            color: #343a40; 
        }

     
        button,
        input[type="submit"] {
            background-color: #007bff; 
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        button:hover,
        input[type="submit"]:hover {
            background-color: #0056b3; 
        }

   
        .sidebar ul li a i {
            margin-right: 8px;
        }
    </style>
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
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Quản lý Khách hàng</a></li>
                <li><a href="admin_products.php"><i class="fas fa-boxes"></i> Quản lý sản phẩm</a></li>
                <li><a href="admin_online.php"><i class="fas fa-shopping-cart"></i> Đơn hàng online</a></li>
                <li><a href="admin_contact.php"><i class="fas fa-envelope"></i> Liên hệ</a></li>
                <li><a href="admin_report.php"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
               <li> <a href="admin_chat.php"> <i class="fas fa-comments"></i> Hỗ trợ khách hàng </a></li>

            </ul>
        </aside>
        <main>
            <div class="content-box">
                <h3><i class="fas fa-info-circle"></i> Thông tin</h3>
                <p>Chào mừng đến với trang quản lý phụ kiện điện thoại.</p>
            </div>
        </main>
    </div>
</body>

</html>