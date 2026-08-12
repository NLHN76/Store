<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Chat Support</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/chat.css">
</head>

<body>

<div class="chat-wrapper">

    <div class="top-header">
        <div class="header-title">
            <div class="header-icon">💬</div>
            <div>
                <h4>Trung tâm hỗ trợ khách hàng</h4>
                <span>Quản lý hội thoại trực tuyến</span>
            </div>
        </div>

        <a href="../admin_interface.php" class="btn-back">← Trang chính</a>
    </div>


    <div class="chat-container">

        <div class="user-list">
            <div class="user-title">
                <h5>Khách hàng</h5>
                <div class="search-box">
                    🔍 <input type="text" id="search-user" placeholder="Tìm khách hàng...">
                </div>
            </div>

            <div id="users"></div>
        </div>


        <div class="chat-box">

            <div class="chat-header" id="chat-header">
                <div class="empty-chat">Chọn khách hàng để bắt đầu hỗ trợ</div>
            </div>

            <div class="chat-messages" id="chat-messages"></div>

            <div class="chat-input">
                <input type="text" id="admin-input" class="form-control" placeholder="Nhập nội dung hỗ trợ...">
                <button id="send-admin" class="send-btn">➤</button>
            </div>

        </div>


        <div class="customer-info">

            <h5>Nhấn để xem thêm</h5>

            <div class="customer-avatar" id="customer-avatar"> ?</div>

            <div class="info-item">
                <span>Tên khách hàng</span>
                <b id="info-name">Chưa chọn</b>
            </div>


        </div>

    </div>

</div>

<!-- CUSTOMER MODAL -->
<div class="modal fade" id="customerModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Thông tin liên hệ
                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>


            <div class="modal-body">

                <p>
                    <b>Email:</b>
                    <span id="modal-email">---</span>
                </p>

                <p>
                    <b>Số điện thoại:</b>
                    <span id="modal-phone">---</span>
                </p>

            </div>

        </div>

    </div>

</div>

<script src="js/chat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>