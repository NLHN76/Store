function loadUsers() {
    fetch("function/get_users.php?action=users")
        .then(res => res.json())
        .then(users => {

            const container = document.getElementById("users");
            container.innerHTML = "";

            users.forEach(user => {

                const div = document.createElement("div");

                div.className = "user-item";
                div.dataset.userid = user.user_id;

                if (user.user_id == selectedUserId) {
                    div.classList.add("active");
                }

                const firstLetter =
                    user.user_name.charAt(0).toUpperCase();

                div.innerHTML = `
                    <div class="avatar">${firstLetter}</div>

                    <div class="user-info">
                        <b>${user.user_name}</b>
                        <small>Khách hàng</small>
                    </div>

                    <span class="badge-new badge bg-warning text-dark">
                        Mới
                    </span>
                `;

                div.onclick = () =>
                    selectUser(
                        user.user_id,
                        user.user_name,
                        div
                    );

                container.appendChild(div);
            });

            checkNewMessages();
        })
        .catch(err => console.log(err));
}


function selectUser(user_id, user_name, div) {

    selectedUserId = user_id;
    currentUserName = user_name;

    const letter =
        user_name.charAt(0).toUpperCase();

    document.getElementById("chat-header").innerHTML = `
        <div class="avatar">${letter}</div>
        <div>
            <b>${user_name}</b>
        </div>
    `;

    document.querySelector(".customer-avatar").innerText = letter;

    document.getElementById("info-name").innerText = user_name;

    loadMessages(user_id);

    div.classList.remove("new-message");

    const badge = div.querySelector(".badge-new");

    if (badge) {
        badge.style.display = "none";
    }

    document
        .querySelectorAll(".user-item")
        .forEach(item => {
            item.classList.remove("active");
        });

    div.classList.add("active");
}


function checkNewMessages() {

    document
        .querySelectorAll(".user-item")
        .forEach(div => {

            const user_id = div.dataset.userid;

            fetch(
                `function/check_new.php?action=check_new&user_id=${user_id}`
            )
                .then(res => res.json())
                .then(data => {

                    const badge =
                        div.querySelector(".badge-new");

                    if (data.new) {

                        div.classList.add("new-message");

                        if (badge) {
                            badge.style.display = "inline-block";
                        }

                    } else {

                        div.classList.remove("new-message");

                        if (badge) {
                            badge.style.display = "none";
                        }
                    }
                });
        });
}


function searchUser() {

    const keyword =
        this.value.toLowerCase();

    document
        .querySelectorAll(".user-item")
        .forEach(item => {

            item.style.display =
                item.innerText
                    .toLowerCase()
                    .includes(keyword)
                    ? "flex"
                    : "none";
        });
}