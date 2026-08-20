
const profileBtn = document.getElementById("profile-btn");
const profileSection = document.getElementById("profile-section");
const profileForm = document.getElementById("profile-form");


function openProfile(){
    profileSection.style.display = "block";
}


function closeProfile(){
    profileSection.style.display = "none";
}


profileBtn.addEventListener("click", function(e){
    e.preventDefault();
    openProfile();


    fetch("auto/get_profile.php")
    .then(res => res.json())
    .then(data => {
        if(data.error){
            alert(data.error);
            closeProfile();
        } else {
            profileForm.name.value = data.name || '';
            profileForm.email.value = data.email || '';
            profileForm.phone.value = data.phone || '';
            profileForm.address.value = data.address || '';
        }
    })
    .catch(err => {
        console.error(err);
        alert("Lỗi khi tải thông tin");
        closeProfile();
    });
});



profileForm.addEventListener("submit", function(e){
    e.preventDefault();

    const formData = new FormData();
    formData.append("phone", profileForm.phone.value);
    formData.append("address", profileForm.address.value);

    fetch("auto/update_profile.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if(data.success){
            closeProfile();
        }
    })
    .catch(err => {
        console.error(err);
        alert("Cập nhật thất bại");
    });
});



