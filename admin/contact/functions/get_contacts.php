<?php

require_once "db.php";

$result = $conn->query("
    SELECT *
    FROM contact
    ORDER BY id DESC
");

while ($row = $result->fetch_assoc()) {

    $class = $row['is_new'] == 1
        ? 'new-contact'
        : 'old-contact';

    echo "
    <tr class='$class' data-id='{$row['id']}'>
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