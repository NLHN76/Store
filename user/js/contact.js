// Liên hệ
document.getElementById('contact-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new URLSearchParams(new FormData(this));
    fetch('user_contact.php', { method:'POST', body: formData })
        .then(res => res.ok ? (alert('Cảm ơn bạn!'), this.reset()) : res.text().then(t => alert('Lỗi: '+t)));
});




