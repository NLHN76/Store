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
    } else {
        box.style.display = "block";
        badge.style.display = "none";

        // Đánh dấu đã đọc tất cả
        const adminCount = document.querySelectorAll(
            '#messenger-messages .admin-message'
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

    fetch("../user/chat_handler.php", {
        method: "POST",
        body: new URLSearchParams({ action: "send", message: msg })
    }).then(() => {
        input.value = "";
    });
}

// Fetch tin nhắn
function fetchMessages() {
    fetch("../user/chat_handler.php?action=fetch")
        .then(res => res.text())
        .then(html => {
            const chatBox = document.getElementById("messenger-messages");
            chatBox.innerHTML = html;

            const adminCount = chatBox.querySelectorAll('.admin-message').length;
            const badge = document.getElementById("messenger-badge");
            const box = document.getElementById("messenger-box");

            // Nếu có tin admin mới & chat đang đóng
            if (adminCount > lastSeenAdminCount && box.style.display !== "block") {
                badge.style.display = "inline-block";
                badge.textContent = "1";
            }

            scrollChatToBottom();
        });
}

// Gán sự kiện
document.getElementById("messenger-float").onclick = toggleChatBox;
document.getElementById("close-messenger").onclick = () => {
    document.getElementById("messenger-box").style.display = "none";
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
