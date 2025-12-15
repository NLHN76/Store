let selectedUserId = null;

function loadUsers(){
    fetch('?action=users').then(res=>res.json()).then(users=>{
        const usersDiv = document.getElementById('users');
        usersDiv.innerHTML='';
        users.forEach(u=>{
            const div = document.createElement('div');
            div.className='user-item';
            div.innerHTML = `<strong>${u.user_name}</strong>`;
            div.onclick = ()=>{
                selectedUserId = u.user_id;
                document.getElementById('chat-header').textContent = u.user_name;
                document.querySelectorAll('.user-item').forEach(d=>d.classList.remove('active'));
                div.classList.add('active');
                loadMessages();
            };
            usersDiv.appendChild(div);
        });
    });
}

function loadMessages(){
    if(!selectedUserId) return;
    fetch('?action=fetch&user_id='+selectedUserId).then(res=>res.text()).then(html=>{
        const chatBox = document.getElementById('chat-messages');
        chatBox.innerHTML = html;
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

function sendMessage(){
    const msg = document.getElementById('admin-input').value.trim();
    if(!msg || !selectedUserId) return;
    const data = new URLSearchParams();
    data.append('message', msg);
    data.append('user_id', selectedUserId);
    fetch('?action=send',{ method:'POST', body:data }).then(res=>res.text()).then(res=>{
        if(res==='OK'){ document.getElementById('admin-input').value=''; loadMessages(); }
        else alert(res);
    });
}

document.getElementById('send-admin').addEventListener('click', sendMessage);
document.getElementById('admin-input').addEventListener('keypress', e=>{ if(e.key==='Enter'){ e.preventDefault(); sendMessage(); } });

setInterval(loadUsers,5000);
setInterval(loadMessages,2000);
loadUsers();