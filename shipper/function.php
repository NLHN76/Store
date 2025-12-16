<?php

if(!isset($_SESSION['shipper_id'])){
    header("Location: shipper_login.php"); exit;
}

$shipper_id = $_SESSION['shipper_id'];
$shipper_name = $_SESSION['shipper_name'];


// --- Lấy thông tin shipper ---
$shipper = $conn->query("SELECT * FROM shipper WHERE id=$shipper_id")->fetch_assoc();
$avatar_login = $shipper['avatar'] ?? 'https://via.placeholder.com/40';

// --- Xử lý AJAX ---
if(isset($_POST['action'])){
    $action = $_POST['action'];

    // Nhận đơn
    if($action=="receive_order"){
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare("UPDATE payment SET shipper_id=?, receive_date=NOW(), status='Đang giao hàng' WHERE id=? AND shipper_id IS NULL AND status='Đang xử lý'");
        $stmt->bind_param("ii",$shipper_id,$order_id);
        $stmt->execute();
        echo $stmt->affected_rows>0?"success":"fail"; exit;
    }

    // Cập nhật trạng thái
    if($action=="update_status"){
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['new_status'];
        $check = $conn->query("SELECT status, shipper_id FROM payment WHERE id=$order_id")->fetch_assoc();
        if($check && $check['shipper_id']==$shipper_id){
            $valid_transitions = [
                'Đang xử lý'=>['Đang xử lý','Đang giao hàng','Đã giao hàng'],
                'Đang giao hàng'=>['Đang giao hàng','Đã giao hàng']
            ];
            if(in_array($new_status,$valid_transitions[$check['status']] ?? [])){
                $stmt = $conn->prepare("UPDATE payment SET status=? WHERE id=?");
                $stmt->bind_param("si",$new_status,$order_id);
                $stmt->execute(); echo "success"; exit;
            }
        }
        echo "fail"; exit;
    }

    // Cập nhật thông tin shipper
    if($action=="update_shipper_info"){
        $id = intval($_POST['shipper_id']);
        $fields = ['name','email','phone','dob','cmt']; $types='sssss'; $params=[];
        foreach($fields as $f) $params[] = $_POST[$f] ?? '';

        if(!empty($_POST['password'])){ $fields[]='password'; $types.='s'; $params[]=password_hash($_POST['password'],PASSWORD_DEFAULT); }
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error']==0){
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if(in_array($ext,['jpg','jpeg','png','gif'])){
                $avatar_path = "uploads/shipper_".$id.".".$ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'],$avatar_path);
                $fields[]='avatar'; $types.='s'; $params[]=$avatar_path;
            }
        }
        $fields_str = implode(', ',array_map(fn($f)=>"$f=?", $fields));
        $stmt = $conn->prepare("UPDATE shipper SET $fields_str WHERE id=?");
        $types.='i'; $params[]=$id;
        $stmt->bind_param($types,...$params);
        echo $stmt->execute()?"success":$conn->error; exit;
    }
}


// --- Lấy danh sách đơn ---
$orders = $conn->query("
    SELECT p.*, s.name AS shipper_name, s.avatar AS shipper_avatar
    FROM payment p
    LEFT JOIN shipper s ON p.shipper_id = s.id
    ORDER BY 
        (p.status = 'Đang xử lý' AND p.shipper_id IS NULL) DESC,
        p.order_date DESC
");

?>