<?php

require_once "db.php";

$search = trim($_POST['search_query'] ?? '');

if ($search !== '') {

    $like = "%$search%";

    if (is_numeric($search)) {

        $stmt = $conn->prepare("
            SELECT *
            FROM contact
            WHERE id = ?
               OR phone LIKE ?
            ORDER BY id DESC
        ");

        $stmt->bind_param("is", $search, $like);

    } else {

        $stmt = $conn->prepare("
            SELECT *
            FROM contact
            WHERE name LIKE ?
               OR email LIKE ?
            ORDER BY id DESC
        ");

        $stmt->bind_param("ss", $like, $like);
    }

    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT *
        FROM contact
        ORDER BY id DESC
    ");
}

while ($row = $result->fetch_assoc()) {

    $class = $row['is_new'] == 1
        ? 'new-contact'
        : 'old-contact';

    echo "
    <tr class='$class' data-id='{$row['id']}'>
        <td>{$row['id']}</td>
        <td>" . htmlspecialchars($row['name']) . "</td>
        <td>" . htmlspecialchars($row['email']) . "</td>
        <td>" . htmlspecialchars($row['phone']) . "</td>
        <td>" . htmlspecialchars($row['message']) . "</td>
        <td>" . htmlspecialchars($row['created_at']) . "</td>
        <td>
            <button
                type='button'
                class='delete-button delete-contact'
                data-id='{$row['id']}'>
                <i class='fas fa-trash-alt'></i>
                Xóa
            </button>
        </td>
    </tr>";
}

?>