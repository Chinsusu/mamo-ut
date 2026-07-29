(function (window, document) {
  'use strict';

  const D = window.OUtData;
  const root = document.body?.dataset.root || '';
  const page = document.body?.dataset.page || '';

  function asset(path) {
    if (!path) return '';
    if (/^(https?:|data:|\/)/.test(path)) return path;
    return `${root}${path}`;
  }

  function icon(name, cls = '') {
    return `<svg class="icon ${cls}" aria-hidden="true"><use href="${root}assets/icons/icons.svg#${name}"></use></svg>`;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function brandMarkup() {
    return `
      <a class="brand" href="${root}index.html" aria-label="O Út đặc sản Huế - Trang chủ">
        <img class="brand__mark" src="${root}assets/images/logo-mark.png" alt="" width="48" height="48">
        <span class="brand__text">
          <span class="brand__name">O Út</span>
          <span class="brand__tagline">đặc sản Huế</span>
        </span>
      </a>`;
  }

  function navLink(href, label, key) {
    return `<li><a class="nav-link ${page === key ? 'is-active' : ''}" href="${root}${href}">${label}</a></li>`;
  }

  function headerMarkup() {
    const settings = D.getSettings();
    return `
      <a class="skip-link" href="#main-content">Bỏ qua điều hướng</a>
      <div class="utility-bar">
        <div class="container utility-bar__inner">
          <ul class="utility-list" aria-label="Cam kết dịch vụ">
            <li>${icon('shield','icon--sm')} Cam kết chất lượng</li>
            <li>${icon('leaf','icon--sm')} Nguyên liệu rõ ràng</li>
            <li>${icon('truck','icon--sm')} Giao hàng toàn quốc</li>
          </ul>
          <ul class="utility-list">
            <li>${icon('phone','icon--sm')} Hotline: ${escapeHtml(settings.hotline)}</li>
            <li><a href="${root}tra-cuu-don.html">Theo dõi đơn hàng</a></li>
          </ul>
        </div>
      </div>
      <header class="site-header" role="banner">
        <div class="container header-main">
          ${brandMarkup()}
          <form class="search-bar" data-search-form action="${root}san-pham.html" role="search">
            <label class="sr-only" for="site-search">Tìm sản phẩm</label>
            <input id="site-search" name="q" type="search" autocomplete="off" placeholder="Tìm tôm chua, mắm ruốc..." aria-controls="search-suggestions" aria-expanded="false">
            <button type="submit" aria-label="Tìm kiếm">${icon('search')}</button>
            <div id="search-suggestions" class="search-suggestions" hidden></div>
          </form>
          <div class="header-actions">
            <a class="header-action" href="${root}tra-cuu-don.html">${icon('receipt')}<span>Tra cứu đơn</span></a>
            <a class="header-action header-action--cart" href="${root}gio-hang.html">${icon('cart')}<span>Giỏ hàng</span><b class="badge-count" data-cart-badge>0</b></a>
          </div>
        </div>
        <nav class="main-nav" aria-label="Điều hướng chính">
          <ul class="container nav-list">
            ${navLink('index.html','Trang chủ','home')}
            ${navLink('san-pham.html','Sản phẩm','products')}
            ${navLink('ve-o-ut.html','Về O Út','about')}
            ${navLink('blog.html','Cẩm nang & công thức','blog')}
            ${navLink('chinh-sach.html','Chính sách','policy')}
            ${navLink('lien-he.html','Liên hệ','contact')}
          </ul>
        </nav>
        <div class="mobile-header">
          <button class="icon-btn" type="button" data-mobile-menu-button aria-label="Mở menu" aria-expanded="false">${icon('menu')}</button>
          ${brandMarkup()}
          <a class="icon-btn" href="${root}gio-hang.html" aria-label="Giỏ hàng">${icon('cart')}<b class="badge-count" data-cart-badge>0</b></a>
        </div>
        <div class="mobile-menu" data-mobile-menu aria-hidden="true">
          <form class="search-bar" data-search-form action="${root}san-pham.html" role="search">
            <label class="sr-only" for="mobile-search">Tìm sản phẩm</label>
            <input id="mobile-search" name="q" type="search" placeholder="Tìm đặc sản Huế...">
            <button type="submit" aria-label="Tìm kiếm">${icon('search')}</button>
          </form>
          <nav class="mobile-menu__links" aria-label="Menu mobile">
            <a href="${root}index.html">Trang chủ ${icon('chevron-right')}</a>
            <a href="${root}san-pham.html">Sản phẩm ${icon('chevron-right')}</a>
            <a href="${root}ve-o-ut.html">Về O Út ${icon('chevron-right')}</a>
            <a href="${root}blog.html">Cẩm nang & công thức ${icon('chevron-right')}</a>
            <a href="${root}chinh-sach.html">Chính sách ${icon('chevron-right')}</a>
            <a href="${root}lien-he.html">Liên hệ ${icon('chevron-right')}</a>
          </nav>
        </div>
      </header>
      <nav class="bottom-nav" aria-label="Điều hướng nhanh">
        <a class="${page === 'home' ? 'is-active' : ''}" href="${root}index.html">${icon('home')}<span>Trang chủ</span></a>
        <a class="${page === 'products' || page === 'product' ? 'is-active' : ''}" href="${root}san-pham.html">${icon('grid')}<span>Sản phẩm</span></a>
        <a class="${page === 'cart' || page === 'checkout' ? 'is-active' : ''}" href="${root}gio-hang.html">${icon('cart')}<span>Giỏ hàng</span><b class="badge-count" data-cart-badge>0</b></a>
        <a class="${page === 'tracking' ? 'is-active' : ''}" href="${root}tra-cuu-don.html">${icon('receipt')}<span>Tra cứu</span></a>
      </nav>`;
  }

  function footerMarkup() {
    const settings = D.getSettings();
    return `
      <footer class="site-footer">
        <div class="container footer-main">
          <div class="footer-brand">
            ${brandMarkup()}
            <p>Hương vị Huế trong từng món ngon nhà làm. O Út ưu tiên thông tin rõ ràng, trải nghiệm dễ mua và chăm sóc tận tình.</p>
            <div class="social-list" aria-label="Mạng xã hội">
              <a class="social-link" href="#" aria-label="Facebook">f</a>
              <a class="social-link" href="#" aria-label="Instagram">ig</a>
              <a class="social-link" href="#" aria-label="Zalo">Za</a>
              <a class="social-link" href="#" aria-label="TikTok">tt</a>
            </div>
          </div>
          <div>
            <h2 class="footer-title">Về O Út</h2>
            <div class="footer-links">
              <a href="${root}ve-o-ut.html">Câu chuyện thương hiệu</a>
              <a href="${root}san-pham.html">Sản phẩm nhà làm</a>
              <a href="${root}blog.html">Cẩm nang món Huế</a>
              <a href="${root}lien-he.html">Liên hệ</a>
            </div>
          </div>
          <div>
            <h2 class="footer-title">Hỗ trợ khách hàng</h2>
            <div class="footer-links">
              <a href="${root}chinh-sach.html#shipping">Hướng dẫn mua hàng</a>
              <a href="${root}chinh-sach.html#shipping">Chính sách giao hàng</a>
              <a href="${root}chinh-sach.html#return">Chính sách đổi trả</a>
              <a href="${root}chinh-sach.html#storage">Hướng dẫn bảo quản</a>
            </div>
          </div>
          <div>
            <h2 class="footer-title">Thông tin liên hệ</h2>
            <div class="footer-links">
              <span>${icon('phone','icon--sm')} ${escapeHtml(settings.hotline)}</span>
              <span>${icon('mail','icon--sm')} ${escapeHtml(settings.email)}</span>
              <span>${icon('pin','icon--sm')} ${escapeHtml(settings.address)}</span>
            </div>
          </div>
          <div>
            <h2 class="footer-title">Nhận tin & ưu đãi</h2>
            <p class="small muted">Nhận công thức món Huế và thông tin mẻ mới. Không gửi tin dày.</p>
            <form class="newsletter" data-newsletter-form>
              <label class="sr-only" for="newsletter-email">Email</label>
              <input id="newsletter-email" type="email" required placeholder="Email của bạn...">
              <button class="btn btn--primary btn--sm" type="submit" aria-label="Đăng ký nhận tin">${icon('arrow-right')}</button>
            </form>
          </div>
        </div>
        <div class="container footer-bottom">
          <span>© 2026 O Út đặc sản Huế. Giao diện prototype FE v1.0.</span>
          <div class="footer-seals">
            <span class="footer-seal">${icon('shield','icon--sm')} Thanh toán an toàn</span>
            <span class="footer-seal">${icon('package','icon--sm')} Đóng gói cẩn thận</span>
            <span class="footer-seal">${icon('truck','icon--sm')} Giao hàng toàn quốc</span>
          </div>
        </div>
      </footer>`;
  }

  function renderHeaderFooter() {
    const header = document.querySelector('[data-site-header]');
    const footer = document.querySelector('[data-site-footer]');
    if (header) header.innerHTML = headerMarkup();
    if (footer) footer.innerHTML = footerMarkup();
  }

  function productCard(product, options = {}) {
    const variant = product.variants[0];
    const favorites = D.getFavorites();
    const isFavorite = favorites.includes(product.id);
    const out = product.status !== 'active' || !product.variants.some(v => v.stock > 0);
    const link = `${root}chi-tiet-san-pham.html?slug=${encodeURIComponent(product.slug)}`;
    return `
      <article class="product-card" data-product-card data-product-id="${product.id}" data-category="${escapeHtml(product.category)}">
        <div class="product-card__media">
          <a href="${link}" aria-label="Xem ${escapeHtml(product.name)}">
            <img src="${asset(product.image)}" alt="${escapeHtml(product.name)}" width="600" height="600" loading="${options.eager ? 'eager' : 'lazy'}">
          </a>
          ${product.badge ? `<span class="product-card__badge">${escapeHtml(product.badge)}</span>` : ''}
          <button class="product-card__wish ${isFavorite ? 'is-active' : ''}" type="button" data-favorite="${product.id}" aria-label="${isFavorite ? 'Bỏ khỏi' : 'Thêm vào'} yêu thích">${icon('heart')}</button>
        </div>
        <div class="product-card__body">
          <h3 class="product-card__name"><a href="${link}">${escapeHtml(product.name)}</a></h3>
          <div class="product-card__variant">Giá từ ${escapeHtml(variant.label)}</div>
          <div class="rating"><span class="rating-stars" aria-label="${product.rating} trên 5 sao">★★★★★</span><span>${product.rating} (${product.reviews})</span></div>
          <div class="product-card__footer">
            <div>
              <div class="product-card__price">${D.currency(D.productMinPrice(product))}</div>
              <span class="stock-label ${out ? 'stock-label--out' : ''}">${out ? 'Tạm hết hàng' : 'Còn hàng'}</span>
            </div>
            <button class="product-card__quick" type="button" data-add-quick="${product.id}" data-variant="${variant.id}" ${out ? 'disabled' : ''} aria-label="Thêm nhanh ${escapeHtml(product.name)} vào giỏ">${icon(out ? 'x' : 'plus')}</button>
          </div>
        </div>
      </article>`;
  }

  function categoryChip(category, label, image, active = false) {
    return `<button class="category-chip ${active ? 'is-active' : ''}" type="button" data-category-filter="${category}"><img src="${root}assets/images/${image}" alt="" width="46" height="46"><strong>${label}</strong></button>`;
  }

  function categoryStrip(active = 'all') {
    return `
      <div class="category-strip" aria-label="Danh mục sản phẩm">
        ${categoryChip('tom-chua','Tôm chua','tom-chua-thumb.jpg',active === 'tom-chua')}
        ${categoryChip('tep-chua','Tép chua','tep-chua-thumb.jpg',active === 'tep-chua')}
        ${categoryChip('cha-ca-xay','Chả cá xay','cha-ca-xay-thumb.jpg',active === 'cha-ca-xay')}
        ${categoryChip('mam-ro','Mắm rò','mam-ro-thumb.jpg',active === 'mam-ro')}
        ${categoryChip('mam-ruot','Mắm ruốt','mam-ruot-thumb.jpg',active === 'mam-ruot')}
        <button class="category-chip category-chip--all ${active === 'all' ? 'is-active' : ''}" type="button" data-category-filter="all">${icon('grid','icon--lg')}<strong>Xem tất cả</strong></button>
      </div>`;
  }

  function trustStrip() {
    const items = [
      ['leaf','Nguyên liệu tươi ngon','Chọn lọc kỹ lưỡng theo từng mẻ'],
      ['shield','Thông tin rõ ràng','Thành phần và bảo quản minh bạch'],
      ['package','Đóng gói cẩn thận','Hạn chế va đập và ám mùi'],
      ['truck','Giao hàng toàn quốc','Theo dõi và hỗ trợ khi cần'],
    ];
    return `<div class="trust-strip">${items.map(([ic,title,desc]) => `<div class="trust-item"><span class="trust-item__icon">${icon(ic)}</span><span><strong>${title}</strong><span>${desc}</span></span></div>`).join('')}</div>`;
  }

  function updateCartBadges() {
    const count = D.cartDetails().count;
    document.querySelectorAll('[data-cart-badge]').forEach(el => {
      el.textContent = String(count);
      el.hidden = count < 1;
    });
  }

  function toast(message, options = {}) {
    let region = document.querySelector('.toast-region');
    if (!region) {
      region = document.createElement('div');
      region.className = 'toast-region';
      region.setAttribute('aria-live', 'polite');
      document.body.appendChild(region);
    }
    const item = document.createElement('div');
    item.className = `toast ${options.type === 'error' ? 'toast--error' : ''}`;
    item.innerHTML = `<span class="toast__icon">${icon(options.type === 'error' ? 'warning' : 'check')}</span><span><strong>${escapeHtml(options.title || (options.type === 'error' ? 'Chưa thể thực hiện' : 'Đã cập nhật'))}</strong><span>${escapeHtml(message)}</span></span><button type="button" aria-label="Đóng">${icon('x','icon--sm')}</button>`;
    const close = () => item.remove();
    item.querySelector('button').addEventListener('click', close);
    region.appendChild(item);
    window.setTimeout(close, options.duration || 4500);
  }

  function bindHeader() {
    const menuButton = document.querySelector('[data-mobile-menu-button]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (menuButton && menu) {
      menuButton.addEventListener('click', () => {
        const open = !menu.classList.contains('is-open');
        menu.classList.toggle('is-open', open);
        menuButton.setAttribute('aria-expanded', String(open));
        menu.setAttribute('aria-hidden', String(!open));
        document.body.classList.toggle('menu-open', open);
      });
    }

    document.querySelectorAll('[data-search-form]').forEach(form => {
      const input = form.querySelector('input[type="search"]');
      const suggestions = form.querySelector('.search-suggestions');
      if (input && suggestions) {
        input.addEventListener('input', () => {
          const q = input.value.trim().toLowerCase();
          if (q.length < 2) {
            suggestions.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            return;
          }
          const matches = D.getProducts().filter(p => `${p.name} ${p.short}`.toLowerCase().includes(q)).slice(0, 4);
          if (!matches.length) {
            suggestions.innerHTML = `<div class="search-suggestion"><span class="muted small">Không thấy sản phẩm phù hợp. Bấm Enter để xem kết quả.</span></div>`;
          } else {
            suggestions.innerHTML = matches.map(p => `<a class="search-suggestion" href="${root}chi-tiet-san-pham.html?slug=${encodeURIComponent(p.slug)}"><img src="${asset(p.image)}" alt=""><span><strong>${escapeHtml(p.name)}</strong><span class="small muted">${D.currency(D.productMinPrice(p))}</span></span></a>`).join('');
          }
          suggestions.hidden = false;
          input.setAttribute('aria-expanded', 'true');
        });
        input.addEventListener('blur', () => window.setTimeout(() => { suggestions.hidden = true; input.setAttribute('aria-expanded', 'false'); }, 160));
      }
    });

    document.querySelectorAll('[data-newsletter-form]').forEach(form => {
      form.addEventListener('submit', event => {
        event.preventDefault();
        toast('O Út đã ghi nhận email. Đây là hành vi demo frontend.');
        form.reset();
      });
    });
  }

  function bindGlobalProductActions(scope = document) {
    scope.querySelectorAll('[data-add-quick]').forEach(button => {
      button.addEventListener('click', () => {
        const result = D.addToCart(Number(button.dataset.addQuick), button.dataset.variant, 1);
        if (result.ok) toast('Sản phẩm đã được thêm vào giỏ hàng.', { title: 'Đã thêm vào giỏ' });
        else toast('Sản phẩm hiện tạm hết hàng.', { type: 'error' });
      });
    });
    scope.querySelectorAll('[data-favorite]').forEach(button => {
      button.addEventListener('click', () => {
        const favorites = D.toggleFavorite(Number(button.dataset.favorite));
        const active = favorites.includes(Number(button.dataset.favorite));
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-label', `${active ? 'Bỏ khỏi' : 'Thêm vào'} yêu thích`);
        toast(active ? 'Đã lưu sản phẩm yêu thích.' : 'Đã bỏ khỏi danh sách yêu thích.');
      });
    });
  }

  renderHeaderFooter();
  bindHeader();
  updateCartBadges();
  window.addEventListener('ouut:cart-updated', updateCartBadges);

  window.OUtUI = {
    root,
    page,
    asset,
    icon,
    escapeHtml,
    productCard,
    categoryStrip,
    trustStrip,
    toast,
    updateCartBadges,
    bindGlobalProductActions,
  };
})(window, document);
