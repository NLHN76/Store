document.querySelector('a[href="#login"]').onclick = () => showSection('login-section');

document.getElementById('login-form').onsubmit = e => {
    const name = document.getElementById('login-name').value,
          email = document.getElementById('login-email').value,
          pass = document.getElementById('login-password').value;
    if (!(name && email && pass)) { 
        e.preventDefault(); 
        alert('Vui lòng điền đầy đủ thông tin!'); 
    }
};