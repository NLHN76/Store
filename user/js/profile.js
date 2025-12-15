// Lấy các element
const profileBtn = document.getElementById("profile-btn");
const profileSection = document.getElementById("profile-section");
const profileForm = document.getElementById("profile-form");

// Hàm mở modal
function openProfile(){
    profileSection.style.display = "block";
}

// Hàm đóng modal
function closeProfile(){
    profileSection.style.display = "none";
}

// Khi bấm nút "Thông tin cá nhân"
profileBtn.addEventListener("click", function(e){
    e.preventDefault();
    openProfile();

    // Lấy dữ liệu từ server
    fetch("../auto/get_profile.php")
    .then(res => res.json())
    .then(data => {
        if(data.error){
            alert(data.error);
            closeProfile();
        } else {
            // Điền dữ liệu vào form
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

// Xử lý submit form (chỉ cập nhật phone và address)
profileForm.addEventListener("submit", function(e){
    e.preventDefault();

    const formData = new FormData();
    formData.append("phone", profileForm.phone.value);
    formData.append("address", profileForm.address.value);

    fetch("../auto/update_profile.php", {
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



