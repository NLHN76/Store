let selectedUserId=0;
let currentUserName="";

function loadUsers(){
fetch('function/get_users.php?action=users')
.then(res=>res.json())
.then(users=>{
    const container=document.getElementById('users');
    container.innerHTML="";

    users.forEach(user=>{
        const div=document.createElement("div");
        div.className="user-item";
        div.dataset.userid=user.user_id;

        if(user.user_id==selectedUserId) div.classList.add("active");

        let firstLetter=user.user_name.charAt(0).toUpperCase();

        div.innerHTML=`
        <div class="avatar">${firstLetter}</div>
        <div class="user-info">
            <b>${user.user_name}</b>
            <small>Khách hàng</small>
        </div>
        <span class="badge-new badge bg-warning text-dark">Mới</span>`;

        div.onclick=()=>selectUser(user.user_id,user.user_name,div);

        container.appendChild(div);
    });

    checkNewMessages();
})
.catch(err=>console.log(err));
}



function selectUser(user_id,user_name,div){

selectedUserId=user_id;
currentUserName=user_name;

let letter=user_name.charAt(0).toUpperCase();

document.getElementById("chat-header").innerHTML=`
<div class="avatar">${letter}</div>
<div><b>${user_name}</b></div>`;


document.querySelector(".customer-avatar").innerText=letter;
document.getElementById("info-name").innerText=user_name;

loadMessages(user_id);

div.classList.remove("new-message");

let badge=div.querySelector(".badge-new");
if(badge) badge.style.display="none";

document.querySelectorAll(".user-item")
.forEach(item=>item.classList.remove("active"));

div.classList.add("active");

}



function loadMessages(user_id){
fetch(`function/fetch_message.php?action=fetch&user_id=${user_id}`)
.then(res=>res.text())
.then(html=>{
    const box=document.getElementById("chat-messages");
    box.innerHTML=html;
    box.scrollTop=box.scrollHeight;
});
}



function sendMessage(){

const input=document.getElementById("admin-input");
const message=input.value.trim();

if(!message || !selectedUserId) return;

const btn=document.getElementById("send-admin");

btn.innerHTML="...";
btn.disabled=true;


fetch('function/send_message.php?action=send',{
method:"POST",
headers:{
'Content-Type':'application/x-www-form-urlencoded'
},
body:`user_id=${selectedUserId}&message=${encodeURIComponent(message)}`
})

.then(res=>res.text())

.then(txt=>{
if(txt==="OK"){
    input.value="";
    loadMessages(selectedUserId);
}
})

.finally(()=>{
btn.innerHTML="➤";
btn.disabled=false;
input.focus();
});

}



document.getElementById("send-admin")
.addEventListener("click",sendMessage);



document.getElementById("admin-input")
.addEventListener("keydown",e=>{
if(e.key==="Enter"&&!e.shiftKey){
    e.preventDefault();
    sendMessage();
}
});



function checkNewMessages(){

document.querySelectorAll(".user-item")
.forEach(div=>{

const user_id=div.dataset.userid;

fetch(`function/check_new.php?action=check_new&user_id=${user_id}`)
.then(res=>res.json())
.then(data=>{

const badge=div.querySelector(".badge-new");

if(data.new){

    div.classList.add("new-message");

    if(badge) badge.style.display="inline-block";

}else{

    div.classList.remove("new-message");

    if(badge) badge.style.display="none";

}

});

});

}



document.getElementById("search-user")
.addEventListener("keyup",function(){

let keyword=this.value.toLowerCase();

document.querySelectorAll(".user-item")
.forEach(item=>{

item.style.display=
item.innerText.toLowerCase().includes(keyword)
?"flex"
:"none";

});

});



loadUsers();
setInterval(checkNewMessages,5000);


// CLICK AVATAR XEM THÔNG TIN

document
.getElementById("customer-avatar")
.addEventListener("click",()=>{


    if(!selectedUserId)
        return;


    fetch(
    `function/users_info.php?user_id=${selectedUserId}`
    )

    .then(res=>res.json())

    .then(data=>{


        document
        .getElementById("modal-email")
        .innerText=data.email ?? "Chưa cập nhật";


        document
        .getElementById("modal-phone")
        .innerText=data.phone ?? "Chưa cập nhật";



        let modal=new bootstrap.Modal(
            document.getElementById("customerModal")
        );


        modal.show();


    });


});