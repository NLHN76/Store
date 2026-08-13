document.querySelector('a[href="#register"]').onclick = () => {
    showSection('register-section');
};

document.getElementById('register-form').onsubmit = e => {
    e.preventDefault();

    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const pass = document.getElementById('register-password').value;

    const xhr = new XMLHttpRequest();

    xhr.open("POST", "../user/register.php", true);

    xhr.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
    );

    xhr.onload = () => {
        if (xhr.status === 200) {

            if (xhr.responseText.startsWith("Đăng ký thành công")) {
                alert(xhr.responseText);
                showSection('login-section');
            } else {
                alert(xhr.responseText);
            }

        } else {
            alert('Lỗi đăng ký!');
        }
    };

    xhr.send(
        `register-name=${encodeURIComponent(name)}
        &register-email=${encodeURIComponent(email)}
        &register-password=${encodeURIComponent(pass)}`
    );
};