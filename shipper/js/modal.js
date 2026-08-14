/* ================= MODAL SHIPPER ================= */

const avatarLogin = document.querySelector(".avatar-login");
const shipperModalElement = document.getElementById("shipperModal");
const shipperModal = new bootstrap.Modal(shipperModalElement);

avatarLogin.addEventListener("click", function () {
    shipperModal.show();
});


const shipperForm = document.getElementById("shipperForm");

shipperForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch("shipper_dashboard.php", {
            method: "POST",
            body: formData
        });

        const res = await response.text();

        alert(
            res === "success"
                ? "Cập nhật thành công!"
                : "Lỗi: " + res
        );

        if (res === "success") {
            location.reload();
        }

    } catch (error) {
        console.error(error);
        alert("Có lỗi xảy ra!");
    }
});