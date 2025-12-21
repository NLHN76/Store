let selectedUserId = 0;

// Load danh sách user
function loadUsers() {
    fetch('get_users.php?action=users')
    .then(res => res.json())
    .then(users => {
        const container = document.getElementById('users');
        container.innerHTML = '';
        users.forEach(user => {
            const div = document.createElement('div');
            div.className = 'user-item';
            div.dataset.userid = user.user_id;

            // Nếu là user đang chọn → highlight
            if(user.user_id == selectedUserId){
                div.classList.add('active');
            }

            div.innerHTML = `
                <span>${user.user_name}</span>
                <span class="badge-new badge bg-warning text-dark">Mới</span>
            `;

            div.onclick = () => selectUser(user.user_id, user.user_name, div);
            container.appendChild(div);
        });
        checkNewMessages(); // kiểm tra tin nhắn mới
    });
}

// Chọn user để chat
function selectUser(user_id, user_name, div) {
    selectedUserId = user_id;
    document.getElementById('chat-header').innerText = user_name;
    loadMessages(user_id);

  
    div.classList.remove('new-message');
    div.querySelector('.badge-new').style.display = 'none';

    // Highlight user hiện tại
    document.querySelectorAll('.user-item').forEach(d => d.classList.remove('active'));
    div.classList.add('active');
}

// Load tin nhắn của user
function loadMessages(user_id) {
    fetch(`fetch_message.php?action=fetch&user_id=${user_id}`)
    .then(res => res.text())
    .then(html => {
        const messages = document.getElementById('chat-messages');
        messages.innerHTML = html;
        messages.scrollTop = messages.scrollHeight;
    });
}

// Gửi tin nhắn
document.getElementById('send-admin').addEventListener('click', () => {
    const input = document.getElementById('admin-input');
    const message = input.value.trim();
    if(!message || !selectedUserId) return;

    fetch('send_message.php?action=send', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `user_id=${selectedUserId}&message=${encodeURIComponent(message)}`
    }).then(res => res.text())
      .then(txt => {
          if(txt === 'OK') {
              input.value = '';
              loadMessages(selectedUserId);
          }
      });
});

// Kiểm tra tin nhắn mới
function checkNewMessages() {
    document.querySelectorAll('.user-item').forEach(div => {
        const user_id = div.dataset.userid;
        fetch(`check_new.php?action=check_new&user_id=${user_id}`)
        .then(res => res.json())
        .then(data => {
            const badge = div.querySelector('.badge-new');
            if(data.new) {
                div.classList.add('new-message');
                badge.style.display = 'inline-block';
            } else {
                div.classList.remove('new-message');
                badge.style.display = 'none';
            }
        });
    });
}

// Tải user lần đầu
loadUsers();

// Kiểm tra tin nhắn mới mỗi 5 giây
setInterval(checkNewMessages, 5000);
