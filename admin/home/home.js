
const modal = document.getElementById("promoFormModal");
const btn = document.getElementById("togglePromoForm");
const span = document.getElementsByClassName("close")[0];

btn.onclick = () => modal.style.display = "block";
span.onclick = () => modal.style.display = "none";
window.onclick = e => { if (e.target === modal) modal.style.display = "none"; }



// Lưu vị trí cuộn trước khi submit form
document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", () => {
        sessionStorage.setItem("scrollPos", window.scrollY);
    });
});

// Khôi phục vị trí cuộn sau khi load lại trang
window.addEventListener("load", () => {
    const scrollPos = sessionStorage.getItem("scrollPos");
    if (scrollPos !== null) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem("scrollPos");
    }
});




if (sessionStorage.getItem("scrollToFeatured")) {
    document.getElementById("featured-section")
        ?.scrollIntoView({ behavior: "smooth" });
    sessionStorage.removeItem("scrollToFeatured");
}

// Khi submit form thêm nổi bật
document.querySelectorAll("form").forEach(f => {
    if (f.querySelector("[name='featured_action']")) {
        f.addEventListener("submit", () => {
            sessionStorage.setItem("scrollToFeatured", "1");
        });
    }
});
