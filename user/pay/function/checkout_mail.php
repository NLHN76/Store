<?php
use PHPMailer\PHPMailer\PHPMailer;

function sendConfirmationEmail($name, $email, $totalPrice, $items, $address, $user_code) {
    require '../password/vendor/autoload.php';

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'Namdo2003hp@gmail.com';
    $mail->Password = 'pdkd ujkn piok qrnu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('Namdo2003hp@gmail.com', 'Mobile Gear');
    $mail->addAddress($email, $name);
    $mail->isHTML(true);
    $mail->Subject = 'Xác nhận đơn hàng - Mobile Gear';
    $mail->Body = buildMailBody($name, $totalPrice, $items, $address, $user_code);

    $mail->send();
}

function buildMailBody($name, $total, $items, $address, $user_code) {
    $html = "<h2>Xác nhận đơn hàng</h2>";
    $html .= "<p>Khách hàng: <b>$name</b> | Mã KH: $user_code</p>";
    $html .= "<p>Địa chỉ: $address</p>";
    $html .= "<ul>";

    foreach ($items as $i) {
        $html .= "<li>{$i['name']} ({$i['color']}) x{$i['quantity']}</li>";
    }

    $html .= "</ul>";
    $html .= "<h3>Tổng tiền: " . number_format($total, 0, ',', '.') . " VNĐ</h3>";
    return $html;
}
?>