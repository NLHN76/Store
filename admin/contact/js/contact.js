document.addEventListener('DOMContentLoaded', function () {

    const contactList = document.getElementById('contactList');
    const searchForm = document.getElementById('searchForm');

    function getContacts(search = '') {

        fetch('functions/get_contacts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'search_query=' + encodeURIComponent(search)
        })
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

                    if (data === 'ok') {
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

                    if (data === 'ok') {
                        this.classList.remove('new-contact');
                        this.classList.add('old-contact');
                    }

                });

            });

        });
    }


    searchForm.addEventListener('submit', function (e) {

        e.preventDefault();

        const search = document.getElementById('searchInput').value.trim();

        getContacts(search);
    });


    getContacts();

});