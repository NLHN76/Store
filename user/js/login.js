document.querySelector('a[href="#login"]').onclick = () => showSection('login-section');

document.getElementById('login-form').onsubmit = e => {
    const email = document.getElementById('login-email').value,
          pass = document.getElementById('login-password').value;

    if (!(email && pass)) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin!');
    }
};