<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ SẢN PHẨM</title>
    <link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/button.css">
<link rel="stylesheet" href="css/search.css">
<link rel="stylesheet" href="css/product.css">
<link rel="stylesheet" href="css/form.css">
<link rel="stylesheet" href="css/modal.css">
</head>
<body>
<div class="container">
    <a href="../admin_interface.php" class="back-button" title="Quay lại trang quản trị">
        <img src="../uploads/exit.jpg" alt="Quay lại"> 
    </a>
    <h2>QUẢN LÝ SẢN PHẨM</h2>

  
    <?php if (!empty($status_message)): ?>
        <div id="status-message" class="status-message <?= $status_type ?>">
            <?= htmlspecialchars($status_message) ?>
        </div>
    <?php endif; ?>

  
    <?php include "product_panel.php"; ?>


    <?php include "product_list.php"; ?>
</div>

<script src="js/products.js"></script>
</body>
</html>
