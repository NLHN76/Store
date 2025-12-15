function checkNotifications(){
    fetch("notification/check_notifications.php")
        .then(res => res.json())
        .then(data => {
            toggle("alert-orders", data.orders);
            toggle("alert-contact", data.contact);
            toggle("alert-chat", data.chat);
        })
        .catch(err => console.error(err));
}

function toggle(id, count){
    const el = document.getElementById(id);
    if(!el) return;
    el.style.display = count > 0 ? "inline-block" : "none";
}

// chạy ngay khi load
document.addEventListener("DOMContentLoaded", () => {
    checkNotifications();
    setInterval(checkNotifications, 5000);
});
