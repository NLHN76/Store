document.getElementById('contact-form')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('user_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (res.ok) {
            alert('Cảm ơn bạn!');
            this.reset();
        } else {
            return res.text().then(t => alert('Lỗi: ' + t));
        }
    });
});
