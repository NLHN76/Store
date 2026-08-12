<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quản Lý Đơn Hàng</title>

    <link rel="stylesheet" href="css/orders.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>

<body>

    <!-- QUAY LẠI -->
    <a href="../admin_interface.php" class="btn-back">
        <img src="../uploads/exit.jpg" alt="Quay lại">
        <span>Quay lại</span>
    </a>


    <h1>Quản Lý Đơn Hàng</h1>


    <!-- TÌM KIẾM -->
    <form id="searchForm" class="search-form">

        <input
            type="text"
            name="keyword"
            placeholder="Tìm theo Mã đơn"
        >

        <button type="submit">
            Tìm kiếm
        </button>

    </form>


    <!-- BẢNG ĐƠN HÀNG -->
    <table>

        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Ngày đặt</th>
                <th>Tên KH</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th>Mã SP</th>
                <th>Sản phẩm</th>
                <th>Loại SP</th>
                <th>Màu sắc</th>
                <th>Số lượng</th>
                <th>Tổng tiền</th>
                <th>Mã KH</th>
                <th>Trạng thái</th>
                <th>Shipper</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody id="ordersTableBody">

            <tr>
                <td colspan="16" style="text-align:center;">
                    Đang tải...
                </td>
            </tr>

        </tbody>

    </table>


    <!-- TEMPLATE 1 ĐƠN HÀNG -->
    <template id="orderTemplate">

        <tr>

            <td class="order-id"></td>
            <td class="order-date"></td>
            <td class="customer-name"></td>
            <td class="customer-email"></td>
            <td class="customer-phone"></td>
            <td class="customer-address"></td>
            <td class="product-code"></td>
            <td class="product-name"></td>
            <td class="category"></td>
            <td class="color"></td>
            <td class="quantity"></td>
            <td class="total-price"></td>
            <td class="user-code"></td>

            <td>
                <select class="status-select"></select>
            </td>

            <td class="shipper"></td>

            <td class="actions"></td>

        </tr>

    </template>


    <script src="js/orders.js"></script>

</body>
</html>