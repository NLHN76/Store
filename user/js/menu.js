document.addEventListener('DOMContentLoaded', () => {
    fetch('check_login.php')
        .then(res => res.json())
        .then(data => {
            const loginMenu = document.getElementById('login-menu');
            const accountMenu = document.getElementById('account-menu');
            const accountName = document.getElementById('account-name');

            if (data.logged_in) {
                loginMenu?.classList.add('hidden');
                accountMenu?.classList.remove('hidden');

                if (accountName && data.user_name) {
                    accountName.textContent = data.user_name;
                }
            } else {
                loginMenu?.classList.remove('hidden');
                accountMenu?.classList.add('hidden');
            }
        });
});
