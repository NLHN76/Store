// Lấy số tin admin đã đọc từ localStorage
let lastSeenAdminCount = parseInt(
    localStorage.getItem("lastSeenAdminCount") || "0"
);

// Toggle chat
function toggleChatBox() {
    const box = document.getElementById("messenger-box");
    const badge = document.getElementById("messenger-badge");

    if (box.style.display === "block") {
        box.style.display = "none";
        document.body.classList.remove("chat-open"); // HIỆN icon messenger
    } else {
        box.style.display = "block";
        badge.style.display = "none";
        document.body.classList.add("chat-open"); // ẨN icon messenger

        // Đánh dấu đã đọc tất cả tin admin
        const adminCount = document.querySelectorAll(
            "#messenger-messages .admin-message"
        ).length;

        lastSeenAdminCount = adminCount;
        localStorage.setItem("lastSeenAdminCount", adminCount);

        scrollChatToBottom();
    }
}

// Scroll cuối
function scrollChatToBottom() {
    const chatBox = document.getElementById("messenger-messages");
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Gửi tin nhắn
function sendMessage() {
    const input = document.getElementById("messenger-input");
    const msg = input.value.trim();
    if (!msg) return;

    fetch("chat/send_message.php", {
        method: "POST",
        body: new URLSearchParams({ action: "send", message: msg })
    }).then(() => {
        input.value = "";
        fetchMessages();
    });
}

// Fetch tin nhắn
function fetchMessages() {
    fetch("chat/fetch_message.php?action=fetch")
        .then(res => res.text())
        .then(html => {
            const chatBox = document.getElementById("messenger-messages");
            chatBox.innerHTML = html;

            const adminMessages = chatBox.querySelectorAll(".admin-message").length;
            const badge = document.getElementById("messenger-badge");
            const box = document.getElementById("messenger-box");

            const newAdminMessages = adminMessages - lastSeenAdminCount;

            if (newAdminMessages > 0 && box.style.display !== "block") {
                badge.style.display = "inline-block";
                badge.textContent = newAdminMessages;
            } else {
                badge.style.display = "none";
            }

            scrollChatToBottom();
        });
}

// ===== Gán sự kiện =====
document.getElementById("messenger-float").onclick = toggleChatBox;

document.getElementById("close-messenger").onclick = () => {
    document.getElementById("messenger-box").style.display = "none";
    document.body.classList.remove("chat-open"); // HIỆN icon messenger
};

document.getElementById("send-messenger").onclick = sendMessage;

document.getElementById("messenger-input").addEventListener("keypress", e => {
    if (e.key === "Enter") {
        e.preventDefault();
        sendMessage();
    }
});

// Fetch mỗi 2s
setInterval(fetchMessages, 2000);

// Load lần đầu
fetchMessages();
