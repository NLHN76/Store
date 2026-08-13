function loadMessages(user_id) {

    fetch(
        `function/fetch_message.php?action=fetch&user_id=${user_id}`
    )
        .then(res => res.text())
        .then(html => {

            const box =
                document.getElementById("chat-messages");

            box.innerHTML = html;

            box.scrollTop = box.scrollHeight;
        });
}


function sendMessage() {

    const input =
        document.getElementById("admin-input");

    const message =
        input.value.trim();

    if (!message || !selectedUserId) {
        return;
    }

    const btn =
        document.getElementById("send-admin");

    btn.innerHTML = "...";
    btn.disabled = true;

    fetch("function/send_message.php?action=send", {
        method: "POST",

        headers: {
            "Content-Type":
                "application/x-www-form-urlencoded"
        },

        body:
            `user_id=${selectedUserId}&message=${encodeURIComponent(message)}`
    })

        .then(res => res.text())

        .then(txt => {

            if (txt === "OK") {

                input.value = "";

                loadMessages(selectedUserId);
            }
        })

        .finally(() => {

            btn.innerHTML = "➤";

            btn.disabled = false;

            input.focus();
        });
}