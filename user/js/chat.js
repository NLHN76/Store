
// Chat
function toggleChatBox() {
  const box = document.getElementById("messenger-box");
  box.style.display = (box.style.display==="block")?"none":"block";
}
document.getElementById("close-messenger").onclick = () => document.getElementById("messenger-box").style.display="none";
const sendMessage = () => {
  const msg = document.getElementById("messenger-input").value.trim();
  if(!msg) return;
  const data = new URLSearchParams({action:"send", message:msg});
  fetch("../user/chat_handler.php",{method:"POST", body:data})
    .then(res=>res.text()).then(res=>{
      if(res==="OK"){
        const chatBox=document.getElementById("messenger-messages");
        const newMsg=document.createElement("div");
        newMsg.className="user-message";
        newMsg.innerHTML=`<strong>Bạn:</strong> ${msg}`;
        chatBox.appendChild(newMsg);
        chatBox.scrollTop=chatBox.scrollHeight;
        document.getElementById("messenger-input").value="";
      } else alert(res);
    });
};
document.getElementById("send-messenger").onclick = sendMessage;
document.getElementById("messenger-input").addEventListener("keypress", e=>{ if(e.key==="Enter"){e.preventDefault(); sendMessage();} });
setInterval(()=>fetch("../user/chat_handler.php?action=fetch").then(res=>res.text()).then(html=>{ const chatBox=document.getElementById("messenger-messages"); chatBox.innerHTML=html; chatBox.scrollTop=chatBox.scrollHeight; }),2000);