// ================= TRANG CHỦ =================
function renderHome(data) {
    renderBanner(data.banner);
    renderPromotions(data.promotions);
    renderFeaturedProducts(data.featured_products);
}

/* ================= BANNER ================= */
function renderBanner(banner) {
    const bannerBox = document.getElementById('home-banner');
    bannerBox.innerHTML = '';

    if (!banner) return;

    const div = document.createElement('div');
    div.className = 'banner';

    const img = document.createElement('img');
    img.src = banner.image;
    img.alt = banner.title;

    const content = document.createElement('div');
    content.className = 'banner-content';

    const h1 = document.createElement('h1');
    h1.textContent = banner.title;

    const p = document.createElement('p');
    p.textContent = banner.description;

    content.append(h1, p);
    div.append(img, content);
    bannerBox.appendChild(div);
}

/* ================= PROMOTIONS ================= */
function renderPromotions(promotions) {
    const promoBox = document.getElementById('home-promotions');
    const template = document.getElementById('promotion-template');

    promoBox.innerHTML = '';

    if (!promotions || !promotions.length) return;

    promotions.forEach(p => {
        const clone = template.content.cloneNode(true);

        const img = clone.querySelector('.promo-img');
        img.src = p.image;
        img.alt = p.title;

        clone.querySelector('.promo-title').textContent = p.title;
        clone.querySelector('.promo-desc').textContent = p.description;

        const link = clone.querySelector('.cta-btn');
        link.href = p.link || '#';

        promoBox.appendChild(clone);
    });
}

/* ================= FEATURED PRODUCTS ================= */
function renderFeaturedProducts(products) {
    const grid = document.getElementById('home-featured');
    const title = document.getElementById('featured-title');

    grid.innerHTML = '';
    title.style.display = 'none';

    if (!products || !products.length) return;

    title.style.display = 'block';

    products.forEach(p => {
        if (!p.product_code) return;

        const item = document.createElement('div');
        item.className = 'featured-item';

        const img = document.createElement('img');
        img.src = p.image;
        img.alt = p.name;
        img.style.cursor = 'pointer';
        img.onclick = () => goToProduct(p.product_code);

        const name = document.createElement('h4');
        name.textContent = p.name;

        const price = document.createElement('p');
        price.className = 'price';
        price.textContent = Number(p.price).toLocaleString() + ' VNĐ';

        const btn = document.createElement('button');
        btn.className = 'btn-find';
        btn.textContent = 'Khám phá sản phẩm';
        btn.onclick = () => goToProduct(p.product_code);

        item.append(img, name, price, btn);
        grid.appendChild(item);
    });
}
