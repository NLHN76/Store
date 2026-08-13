let selectedUserId = 0;
let currentUserName = "";

document.addEventListener("DOMContentLoaded", () => {
    loadUsers();

    setInterval(checkNewMessages, 5000);

    document
        .getElementById("send-admin")
        .addEventListener("click", sendMessage);

    document
        .getElementById("admin-input")
        .addEventListener("keydown", e => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

    document
        .getElementById("search-user")
        .addEventListener("keyup", searchUser);

    document
        .getElementById("customer-avatar")
        .addEventListener("click", showCustomerInfo);
});