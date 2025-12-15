// ================= TRANG CHỦ =================
function renderHome(data) {
    const homeSection = document.getElementById('home');
    homeSection.innerHTML = '';

    /* ================= BANNER ================= */
    if (data.banner) {
        const b = document.createElement('div');
        b.className = 'banner';

        b.innerHTML = `
            <img src="${data.banner.image}" alt="${data.banner.title}">
            <div class="banner-content">
                <h1>${data.banner.title}</h1>
                <p>${data.banner.description}</p>
            </div>
        `;

        homeSection.appendChild(b);
    }

    /* ================= KHUYẾN MÃI ================= */
    if (data.promotions && data.promotions.length) {
        const promoSection = document.createElement('div');
        promoSection.className = 'promo-grid';

        data.promotions.forEach(p => {
            const d = document.createElement('div');
            d.className = 'promo-item';

            d.innerHTML = `
                <img src="${p.image}" alt="${p.title}">
                <h3>${p.title}</h3>
                <p>${p.description}</p>
                <a href="${p.link || '#'}" class="cta-btn">Xem chi tiết</a>
            `;

            promoSection.appendChild(d);
        });

        homeSection.appendChild(promoSection);
    }

    /* ================= SẢN PHẨM NỔI BẬT ================= */
    if (data.featured_products && data.featured_products.length) {
        const title = document.createElement('h2');
        title.className = 'section-title';
        title.textContent = 'Sản phẩm nổi bật';
        homeSection.appendChild(title);

        const featuredWrap = document.createElement('div');
        featuredWrap.className = 'featured-grid';

        data.featured_products.forEach(p => {
            // ⚠️ bảo vệ tránh lỗi
            if (!p.product_code) return;

            const item = document.createElement('div');
            item.className = 'featured-item';

          item.innerHTML = `
    <img src="${p.image}" alt="${p.name}" style="cursor:pointer">
    <h4>${p.name}</h4>
    <p class="price">${Number(p.price).toLocaleString()} VNĐ</p>

    <button class="btn-find"
        onclick="goToProduct('${p.product_code}')">
        Khám phá sản phẩm
    </button>
`;
            featuredWrap.appendChild(item);
        });

        homeSection.appendChild(featuredWrap);
    }
}
