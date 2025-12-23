// Khi click vào liên hệ mới, đánh dấu đã xem
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tr.new-contact').forEach(function(row) {
        row.addEventListener('click', function(e) {
            // tránh click vào nút xóa cũng trigger
            if (e.target.closest('button')) return;

            const contactId = this.dataset.id;

            fetch('mark_seen.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + contactId
            }).then(response => response.text())
              .then(data => {
                  if (data === 'ok') {
                      this.classList.remove('new-contact');
                      this.classList.add('old-contact');
                  }
              });
        });
    });
});