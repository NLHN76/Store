document.getElementById("login-form").addEventListener("submit", function (e) {
    e.preventDefault();

    const errorBox = document.getElementById("login-error");
    errorBox.textContent = "";

    fetch("login.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = "user.html";
        } else {
            errorBox.textContent = data.message;
        }
    })
    .catch(() => {
        errorBox.textContent = "Lỗi kết nối server.";
    });
});