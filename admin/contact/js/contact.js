document.addEventListener('DOMContentLoaded', function () {

    const contactList = document.getElementById('contactList');

    function getContacts() {
        fetch('functions/get_contacts.php')
            .then(response => response.text())
            .then(data => {
                contactList.innerHTML = data;
                addEvents();
            });
    }

    function addEvents() {

        document.querySelectorAll('.delete-contact').forEach(function (button) {

            button.addEventListener('click', function () {

                if (!confirm('Bạn có chắc chắn muốn xóa không?')) {
                    return;
                }

                const id = this.dataset.id;

                fetch('functions/delete_contacts.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(id)
                })
                .then(response => response.text())
                .then(data => {

                    if (data.trim() === 'ok') {
                        this.closest('tr').remove();
                    }

                });

            });

        });

        document.querySelectorAll('tr.new-contact').forEach(function (row) {

            row.addEventListener('click', function (e) {

                if (e.target.closest('.delete-contact')) {
                    return;
                }

                const id = this.dataset.id;

                fetch('functions/mark_seen.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(id)
                })
                .then(response => response.text())
                .then(data => {

                    if (data.trim() === 'ok') {
                        this.classList.remove('new-contact');
                        this.classList.add('old-contact');
                    }

                });

            });

        });
    }

    getContacts();

});