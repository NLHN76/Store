document.querySelector('a[href="#login"]').onclick = () => { 
    showSection('login-section'); 
}; 
 
document.getElementById("login-form").addEventListener("submit", function(e) { 
 
    e.preventDefault(); 
 
    const email = document.getElementById("login-email").value.trim(); 
    const pass = document.getElementById("login-password").value; 
 
    const errorBox = document.getElementById("login-error"); 
 
    errorBox.textContent = ""; 
 
    if (!email || !pass) { 
        errorBox.textContent = "Vui lòng điền đầy đủ thông tin!"; 
        return; 
    } 
 
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