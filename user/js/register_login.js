
// ================= FORM ĐĂNG NHẬP / ĐĂNG KÝ =================
document.querySelector('a[href="#login"]').onclick = () => showSection('login-section');
document.querySelector('a[href="#register"]').onclick = () => showSection('register-section');

document.getElementById('register-form').onsubmit = e => {
    e.preventDefault();
    const name = document.getElementById('register-name').value,
          email = document.getElementById('register-email').value,
          pass = document.getElementById('register-password').value;
    const xhr = new XMLHttpRequest();
    xhr.open("POST","../user/user_register.php",true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.onload = () => xhr.status===200 ? 
        (xhr.responseText.startsWith("Đăng ký thành công") ? (alert(xhr.responseText), showSection('login-section')) : alert(xhr.responseText)) 
        : alert('Lỗi đăng ký!');
    xhr.send(`register-name=${encodeURIComponent(name)}&register-email=${encodeURIComponent(email)}&register-password=${encodeURIComponent(pass)}`);
};

document.getElementById('login-form').onsubmit = e => {
    const name = document.getElementById('login-name').value,
          email = document.getElementById('login-email').value,
          pass = document.getElementById('login-password').value;
    if (!(name && email && pass)) { 
        e.preventDefault(); 
        alert('Vui lòng điền đầy đủ thông tin!'); 
    }
};


