    /* ================= MODAL SHIPPER ================= */
    $(".avatar-login").click(() => {
        new bootstrap.Modal('#shipperModal').show();
    });

    $("#shipperForm").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "shipper_dashboard.php",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            processData: false,
            success: res => {
                alert(res === "success" ? "Cập nhật thành công!" : "Lỗi: " + res);
                if (res === "success") location.reload();
            }
        });
    });
