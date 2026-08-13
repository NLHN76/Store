function showCustomerInfo() {

    if (!selectedUserId) {
        return;
    }

    fetch(
        `function/users_info.php?user_id=${selectedUserId}`
    )

        .then(res => res.json())

        .then(data => {

            document
                .getElementById("modal-email")
                .innerText =
                data.email ?? "Chưa cập nhật";

            document
                .getElementById("modal-phone")
                .innerText =
                data.phone ?? "Chưa cập nhật";

            const modal =
                new bootstrap.Modal(
                    document.getElementById("customerModal")
                );

            modal.show();
        });
}