document.querySelector('a[href="#register"]').onclick = () => {
    showSection('register-section');
};

document.getElementById('register-form').onsubmit = e => {
    e.preventDefault();

    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const pass = document.getElementById('register-password').value;

    fetch("register.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body:
            `register-name=${encodeURIComponent(name)}` +
            `&register-email=${encodeURIComponent(email)}` +
            `&register-password=${encodeURIComponent(pass)}`
    })
    .then(response => response.text())

    .then(data => {

        if (data.startsWith("Đăng ký thành công")) {
            alert(data);
            showSection('login-section');
        } else {
            alert(data);
        }

    })

    .catch(error => {
        console.error(error);
        alert("Lỗi đăng ký!");
    });
};