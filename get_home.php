<?php
require_once "db.php";

/* ================== BANNER ================== */
$sqlBanner = "SELECT * FROM home WHERE id = 1";
$resultBanner = $conn->query($sqlBanner);
$banner = $resultBanner ? $resultBanner->fetch_assoc() : null;

if (!empty($banner['image'])) {
    $banner['image'] = 'admin/home/uploads/' . $banner['image'];
}

/* ================== KHUYẾN MÃI ================== */
$sqlPromo = "SELECT * FROM promotions ORDER BY id DESC";
$resultPromo = $conn->query($sqlPromo);

$promotions = [];
if ($resultPromo) {
    while ($row = $resultPromo->fetch_assoc()) {
        if (!empty($row['image'])) {
            $row['image'] = 'admin/home/uploads/' . $row['image'];
        }
        $promotions[] = $row;
    }
}

/* ================== SẢN PHẨM NỔI BẬT ================== */
$sqlFeatured = "
    SELECT 
        p.id,
        p.product_code,  
        p.name,
        p.price,
        p.image
    FROM featured_products f
    JOIN products p ON f.product_id = p.id
    WHERE p.is_active = 1
    ORDER BY f.id DESC
";

$resultFeatured = $conn->query($sqlFeatured);

$featured_products = [];
if ($resultFeatured) {
    while ($row = $resultFeatured->fetch_assoc()) {
        if (!empty($row['image'])) {
            $row['image'] = 'admin/uploads/' . $row['image'];
        }
        $featured_products[] = $row;
    }
}


/* ================== TRẢ JSON ================== */
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'banner' => $banner,
    'promotions' => $promotions,
    'featured_products' => $featured_products
], JSON_UNESCAPED_UNICODE);
