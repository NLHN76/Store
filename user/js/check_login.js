document.addEventListener('DOMContentLoaded', () => {
    fetch('check_login.php')
        .then(res => res.json())
        .then(data => {
            const loginMenu = document.getElementById('login-menu');
            const accountMenu = document.getElementById('account-menu');

            if (data.logged_in) {
                loginMenu?.classList.add('hidden');
                accountMenu?.classList.remove('hidden');

                // (tuỳ chọn) hiện tên user
                const accountBtn = document.getElementById('account-btn');
                if (accountBtn && data.user_name) {
                    accountBtn.innerHTML = `<i class="fas fa-user"></i> ${data.user_name}`;
                }
            } else {
                loginMenu?.classList.remove('hidden');
                accountMenu?.classList.add('hidden');
            }
        });
});
