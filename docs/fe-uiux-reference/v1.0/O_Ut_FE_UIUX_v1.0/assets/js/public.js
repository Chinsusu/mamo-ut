(function (window, document) {
  'use strict';

  const D = window.OUtData;
  const U = window.OUtUI;
  if (!D || !U) return;

  const page = U.page;
  const root = U.root;
  const icon = U.icon;
  const esc = U.escapeHtml;
  const asset = U.asset;

  function query(name, fallback = '') {
    const params = new URLSearchParams(window.location.search);
    return params.get(name) || fallback;
  }

  function setMeta(title, description) {
    if (title) document.title = title;
    const meta = document.querySelector('meta[name="description"]');
    if (meta && description) meta.setAttribute('content', description);
  }

  function articleCard(post) {
    return `
      <article class="article-card">
        <a class="article-card__media" href="${root}bai-viet.html?slug=${encodeURIComponent(post.slug)}"><img src="${asset(post.cover)}" alt="${esc(post.title)}" width="800" height="450" loading="eager"></a>
        <div class="article-card__body">
          <div class="article-card__meta"><span>${esc(post.category)}</span><span>•</span><span>${esc(post.readTime)}</span></div>
          <h3><a href="${root}bai-viet.html?slug=${encodeURIComponent(post.slug)}">${esc(post.title)}</a></h3>
          <p>${esc(post.excerpt)}</p>
          <a class="text-link article-card__link" href="${root}bai-viet.html?slug=${encodeURIComponent(post.slug)}">Đọc bài ${icon('arrow-right','icon--sm')}</a>
        </div>
      </article>`;
  }

  function initHome() {
    const categories = document.querySelector('[data-category-strip]');
    const trust = document.querySelector('[data-trust-strip]');
    const featured = document.querySelector('[data-featured-products]');
    const posts = document.querySelector('[data-featured-posts]');
    if (categories) categories.innerHTML = U.categoryStrip('all');
    if (trust) trust.innerHTML = U.trustStrip();
    if (featured) {
      featured.innerHTML = D.getProducts().filter(p => p.featured && p.status === 'active').slice(0, 5).map((p, i) => U.productCard(p, { eager: i < 2 })).join('');
      U.bindGlobalProductActions(featured);
    }
    if (posts) posts.innerHTML = D.getPosts().filter(p => p.status === 'published').slice(0, 3).map(articleCard).join('');
    document.querySelectorAll('[data-category-filter]').forEach(button => {
      button.addEventListener('click', () => {
        const category = button.dataset.categoryFilter;
        window.location.href = `${root}san-pham.html${category !== 'all' ? `?category=${encodeURIComponent(category)}` : ''}`;
      });
    });
  }

  function initProducts() {
    const grid = document.querySelector('[data-products-grid]');
    const chips = document.querySelector('[data-product-filters]');
    const summary = document.querySelector('[data-results-summary]');
    const sort = document.querySelector('[data-sort-products]');
    const empty = document.querySelector('[data-products-empty]');
    if (!grid || !chips) return;

    const categories = [
      ['all','Tất cả'], ['tom-chua','Tôm chua'], ['tep-chua','Tép chua'], ['cha-ca-xay','Chả cá xay'], ['mam-ro','Mắm rò'], ['mam-ruot','Mắm ruốt'],
    ];
    let state = {
      category: query('category','all'),
      q: query('q','').trim().toLowerCase(),
      sort: 'featured',
    };

    chips.innerHTML = categories.map(([value,label]) => `<button type="button" class="filter-chip ${state.category === value ? 'is-active' : ''}" data-list-category="${value}">${label}</button>`).join('');

    function render() {
      let products = D.getProducts().filter(p => p.status === 'active');
      if (state.category !== 'all') products = products.filter(p => p.category === state.category);
      if (state.q) products = products.filter(p => `${p.name} ${p.short} ${p.categoryLabel}`.toLowerCase().includes(state.q));
      if (state.sort === 'price-asc') products.sort((a,b) => D.productMinPrice(a) - D.productMinPrice(b));
      if (state.sort === 'price-desc') products.sort((a,b) => D.productMinPrice(b) - D.productMinPrice(a));
      if (state.sort === 'name') products.sort((a,b) => a.name.localeCompare(b.name,'vi'));
      if (state.sort === 'featured') products.sort((a,b) => Number(b.featured) - Number(a.featured) || b.sold - a.sold);
      grid.innerHTML = products.map(p => U.productCard(p)).join('');
      U.bindGlobalProductActions(grid);
      empty.hidden = products.length > 0;
      grid.hidden = products.length === 0;
      if (summary) summary.textContent = state.q ? `${products.length} kết quả cho “${query('q')}”` : `Hiển thị ${products.length} sản phẩm nhà làm`;
    }

    chips.querySelectorAll('[data-list-category]').forEach(button => {
      button.addEventListener('click', () => {
        state.category = button.dataset.listCategory;
        chips.querySelectorAll('.filter-chip').forEach(item => item.classList.toggle('is-active', item === button));
        render();
      });
    });
    if (sort) sort.addEventListener('change', () => { state.sort = sort.value; render(); });
    render();
  }

  function initProduct() {
    const rootEl = document.querySelector('[data-product-detail]');
    if (!rootEl) return;
    const slug = query('slug','tom-chua-hue');
    const product = D.getProduct(slug) || D.getProducts()[0];
    let selected = product.variants.find(v => v.stock > 0) || product.variants[0];
    let quantity = 1;

    setMeta(`${product.name} – O Út đặc sản Huế`, product.short);

    function galleryMarkup() {
      return `
        <div class="product-gallery">
          <div class="gallery-thumbs" role="list" aria-label="Ảnh sản phẩm">
            ${product.gallery.map((img,i) => `<button class="gallery-thumb ${i === 0 ? 'is-active' : ''}" type="button" data-gallery-thumb="${i}"><img src="${asset(img)}" alt="${esc(product.name)} - ảnh ${i+1}" width="160" height="160"></button>`).join('')}
          </div>
          <div class="gallery-main"><img data-gallery-main src="${asset(product.gallery[0])}" alt="${esc(product.name)}" width="900" height="840"><span class="gallery-badge">${esc(product.badge || 'Nhà làm')}</span></div>
        </div>`;
    }

    function buyMarkup() {
      const out = selected.stock <= 0 || product.status !== 'active';
      return `
        <div class="product-buy" data-buy-panel>
          <span class="eyebrow">Đặc sản Huế nhà làm</span>
          <h1>${esc(product.name)}</h1>
          <p class="product-buy__sub">${esc(product.short)}</p>
          <div class="product-buy__rating"><span class="rating-stars">★★★★★</span><strong>${product.rating}</strong><span>(${product.reviews} đánh giá)</span><span>•</span><span>Đã bán ${product.sold.toLocaleString('vi-VN')}</span></div>
          <div class="product-buy__price" data-product-price>${D.currency(selected.price)}</div>
          <span class="stock-label ${out ? 'stock-label--out' : ''}" data-product-stock>${out ? 'Tạm hết hàng' : `Còn ${selected.stock} sản phẩm`}</span>
          <div class="option-group">
            <div class="option-group__label"><span>Chọn khối lượng</span><span class="muted small">SKU: <b data-product-sku>${esc(selected.sku)}</b></span></div>
            <div class="variant-list">${product.variants.map(v => `<button class="variant-btn ${v.id === selected.id ? 'is-active' : ''}" type="button" data-variant-id="${v.id}" ${v.stock <= 0 ? 'disabled' : ''}>${esc(v.label)}</button>`).join('')}</div>
          </div>
          <div class="option-group">
            <div class="option-group__label"><span>Số lượng</span><span class="muted small">Tối đa ${selected.stock}</span></div>
            <div class="quantity-control"><button type="button" data-qty-minus aria-label="Giảm số lượng">${icon('minus')}</button><input data-qty-input value="1" inputmode="numeric" aria-label="Số lượng"><button type="button" data-qty-plus aria-label="Tăng số lượng">${icon('plus')}</button></div>
          </div>
          <div class="buy-actions">
            <button class="btn btn--primary btn--lg" type="button" data-add-product ${out ? 'disabled' : ''}>${icon('cart')} Thêm vào giỏ</button>
            <button class="btn btn--secondary btn--lg" type="button" data-buy-now ${out ? 'disabled' : ''}>Mua ngay</button>
          </div>
          <div class="product-assurances">
            <span class="product-assurance">${icon('leaf')} Nguyên liệu rõ ràng</span>
            <span class="product-assurance">${icon('shield')} Hướng dẫn bảo quản</span>
            <span class="product-assurance">${icon('truck')} Giao toàn quốc</span>
          </div>
        </div>`;
    }

    rootEl.innerHTML = `
      <div class="container product-detail__grid">${galleryMarkup()}${buyMarkup()}</div>
      <div class="container product-content">
        <article class="product-copy">
          <span class="eyebrow">Thông tin sản phẩm</span>
          <h2>Vị Huế dễ dùng trong bữa cơm nhà</h2>
          <p>${esc(product.description)}</p>
          <div class="accordion">
            <details open><summary>Mô tả & thành phần ${icon('chevron-down')}</summary><div class="accordion__content"><p>${esc(product.ingredients)}</p></div></details>
            <details><summary>Cách dùng gợi ý ${icon('chevron-down')}</summary><div class="accordion__content"><p>${esc(product.usage)}</p></div></details>
            <details><summary>Bảo quản & hạn dùng ${icon('chevron-down')}</summary><div class="accordion__content"><p>${esc(product.storage)}</p><p>${esc(product.shelfLife)}</p></div></details>
            <details><summary>Giao hàng & đổi trả ${icon('chevron-down')}</summary><div class="accordion__content"><p>Hàng được đóng gói kín, hỗ trợ giao toàn quốc. Liên hệ O Út trong 24 giờ nếu sản phẩm móp, vỡ hoặc sai đơn.</p></div></details>
          </div>
        </article>
        <aside class="product-facts">
          <div class="fact-card"><strong>${icon('leaf')} Nguyên liệu</strong><p>${esc(product.ingredients)}</p></div>
          <div class="fact-card"><strong>${icon('clock')} Hạn dùng</strong><p>${esc(product.shelfLife)}</p></div>
          <div class="fact-card"><strong>${icon('package')} Quy cách</strong><p>Nhãn rõ khối lượng, hướng dẫn dùng và bảo quản. Dữ liệu minh họa cần thay bằng thông tin sản phẩm thật trước khi go-live.</p></div>
        </aside>
      </div>`;

    const main = rootEl.querySelector('[data-gallery-main]');
    rootEl.querySelectorAll('[data-gallery-thumb]').forEach(button => {
      button.addEventListener('click', () => {
        const index = Number(button.dataset.galleryThumb);
        main.src = asset(product.gallery[index]);
        rootEl.querySelectorAll('[data-gallery-thumb]').forEach(b => b.classList.toggle('is-active', b === button));
      });
    });

    function refreshBuyPanel() {
      const out = selected.stock <= 0 || product.status !== 'active';
      rootEl.querySelector('[data-product-price]').textContent = D.currency(selected.price * quantity);
      rootEl.querySelector('[data-product-stock]').textContent = out ? 'Tạm hết hàng' : `Còn ${selected.stock} sản phẩm`;
      rootEl.querySelector('[data-product-stock]').classList.toggle('stock-label--out', out);
      rootEl.querySelector('[data-product-sku]').textContent = selected.sku;
      rootEl.querySelector('[data-qty-input]').value = quantity;
      rootEl.querySelectorAll('[data-add-product],[data-buy-now]').forEach(btn => { btn.disabled = out; });
      const mobilePrice = document.querySelector('[data-mobile-product-price]');
      if (mobilePrice) mobilePrice.textContent = D.currency(selected.price * quantity);
    }

    rootEl.querySelectorAll('[data-variant-id]').forEach(button => {
      button.addEventListener('click', () => {
        selected = product.variants.find(v => v.id === button.dataset.variantId) || selected;
        quantity = 1;
        rootEl.querySelectorAll('[data-variant-id]').forEach(b => b.classList.toggle('is-active', b === button));
        refreshBuyPanel();
      });
    });

    const qtyInput = rootEl.querySelector('[data-qty-input]');
    rootEl.querySelector('[data-qty-minus]').addEventListener('click', () => { quantity = Math.max(1, quantity - 1); refreshBuyPanel(); });
    rootEl.querySelector('[data-qty-plus]').addEventListener('click', () => { quantity = Math.min(selected.stock || 1, quantity + 1); refreshBuyPanel(); });
    qtyInput.addEventListener('change', () => { quantity = Math.max(1, Math.min(selected.stock || 1, Number(qtyInput.value || 1))); refreshBuyPanel(); });

    function add(goCheckout) {
      const result = D.addToCart(product.id, selected.id, quantity);
      if (!result.ok) return U.toast('Sản phẩm hiện tạm hết hàng.', { type: 'error' });
      U.toast(`${product.name} – ${selected.label} đã vào giỏ.`, { title: 'Đã thêm vào giỏ' });
      if (goCheckout) window.setTimeout(() => { window.location.href = `${root}dat-hang.html`; }, 350);
    }
    rootEl.querySelector('[data-add-product]').addEventListener('click', () => add(false));
    rootEl.querySelector('[data-buy-now]').addEventListener('click', () => add(true));

    const mobile = document.querySelector('[data-mobile-buy-bar]');
    if (mobile) {
      document.body.classList.add('has-mobile-buy');
      mobile.innerHTML = `<div><span class="small muted">Tạm tính</span><strong class="text-brand" data-mobile-product-price>${D.currency(selected.price)}</strong></div><button class="btn btn--primary" type="button" data-mobile-add>${icon('cart')} Thêm vào giỏ</button>`;
      mobile.querySelector('[data-mobile-add]').addEventListener('click', () => add(false));
    }

    const related = document.querySelector('[data-related-products]');
    if (related) {
      related.innerHTML = D.getProducts().filter(p => p.id !== product.id && p.status === 'active').slice(0,4).map(p => U.productCard(p)).join('');
      U.bindGlobalProductActions(related);
    }
  }

  function getCoupon() {
    try { return JSON.parse(sessionStorage.getItem('ouut:coupon') || 'null'); } catch (_) { return null; }
  }
  function setCoupon(coupon) {
    try { if (coupon) sessionStorage.setItem('ouut:coupon', JSON.stringify(coupon)); else sessionStorage.removeItem('ouut:coupon'); } catch (_) { /* no-op */ }
  }
  function couponDiscount(subtotal) {
    const coupon = getCoupon();
    if (!coupon || coupon.code !== 'OUUT10') return 0;
    return Math.min(Math.round(subtotal * .1), 30000);
  }

  function renderCart() {
    const wrap = document.querySelector('[data-cart-page]');
    if (!wrap) return;
    const details = D.cartDetails();
    const settings = D.getSettings();
    const shipping = details.subtotal >= settings.freeShippingThreshold ? 0 : settings.shippingStandard;
    const discount = couponDiscount(details.subtotal);
    const total = details.subtotal + shipping - discount;
    const itemsEl = wrap.querySelector('[data-cart-items]');
    const emptyEl = wrap.querySelector('[data-cart-empty]');

    if (!details.items.length) {
      itemsEl.innerHTML = '';
      emptyEl.hidden = false;
      wrap.querySelector('[data-cart-layout]').hidden = true;
      return;
    }
    emptyEl.hidden = true;
    wrap.querySelector('[data-cart-layout]').hidden = false;
    itemsEl.innerHTML = details.items.map(line => `
      <article class="cart-item" data-cart-line="${line.productId}:${line.variantId}">
        <a href="${root}chi-tiet-san-pham.html?slug=${encodeURIComponent(line.product.slug)}"><img src="${asset(line.product.image)}" alt="${esc(line.product.name)}" width="102" height="102"></a>
        <div>
          <h3><a href="${root}chi-tiet-san-pham.html?slug=${encodeURIComponent(line.product.slug)}">${esc(line.product.name)}</a></h3>
          <div class="cart-item__variant">${esc(line.variant.label)} · ${esc(line.variant.sku)}</div>
          <div class="cart-item__price">${D.currency(line.variant.price)}</div>
          <div class="cart-item__actions">
            <div class="quantity-control"><button type="button" data-cart-minus aria-label="Giảm">${icon('minus')}</button><input value="${line.quantity}" inputmode="numeric" data-cart-qty aria-label="Số lượng"><button type="button" data-cart-plus aria-label="Tăng">${icon('plus')}</button></div>
            <button class="btn btn--ghost btn--sm" type="button" data-cart-remove>${icon('trash','icon--sm')} Bỏ</button>
          </div>
        </div>
        <div class="cart-item__total">${D.currency(line.lineTotal)}</div>
      </article>`).join('');

    wrap.querySelector('[data-summary-subtotal]').textContent = D.currency(details.subtotal);
    wrap.querySelector('[data-summary-shipping]').textContent = shipping ? D.currency(shipping) : 'Miễn phí';
    wrap.querySelector('[data-summary-discount]').textContent = discount ? `−${D.currency(discount)}` : D.currency(0);
    wrap.querySelector('[data-summary-total]').textContent = D.currency(total);
    wrap.querySelector('[data-free-ship-note]').textContent = shipping ? `Mua thêm ${D.currency(Math.max(0, settings.freeShippingThreshold - details.subtotal))} để được miễn phí giao hàng.` : 'Đơn hàng đã được miễn phí giao tiêu chuẩn.';

    itemsEl.querySelectorAll('[data-cart-line]').forEach(row => {
      const [productId, variantId] = row.dataset.cartLine.split(':');
      const input = row.querySelector('[data-cart-qty]');
      const update = value => { D.updateCartItem(Number(productId), variantId, value); renderCart(); };
      row.querySelector('[data-cart-minus]').addEventListener('click', () => update(Number(input.value) - 1));
      row.querySelector('[data-cart-plus]').addEventListener('click', () => update(Number(input.value) + 1));
      input.addEventListener('change', () => update(Number(input.value || 1)));
      row.querySelector('[data-cart-remove]').addEventListener('click', () => { D.removeCartItem(Number(productId), variantId); U.toast('Đã bỏ sản phẩm khỏi giỏ.'); renderCart(); });
    });

    const couponForm = wrap.querySelector('[data-coupon-form]');
    const couponInput = couponForm.querySelector('input');
    const existing = getCoupon();
    if (existing) couponInput.value = existing.code;
    couponForm.onsubmit = event => {
      event.preventDefault();
      const code = couponInput.value.trim().toUpperCase();
      if (code === 'OUUT10') {
        setCoupon({ code });
        U.toast('Đã áp dụng giảm 10%, tối đa 30.000đ.');
      } else {
        setCoupon(null);
        U.toast('Mã chưa đúng. Dùng OUUT10 để thử luồng demo.', { type: 'error' });
      }
      renderCart();
    };
  }

  function initCheckout() {
    const form = document.querySelector('[data-checkout-form]');
    if (!form) return;
    const details = D.cartDetails();
    if (!details.items.length) {
      document.querySelector('[data-checkout-empty]').hidden = false;
      form.closest('.checkout-layout').hidden = true;
      return;
    }
    const settings = D.getSettings();
    const summaryItems = document.querySelector('[data-checkout-items]');
    let shippingMethod = 'standard';
    let payment = 'cod';
    let submitting = false;

    summaryItems.innerHTML = details.items.map(line => `<div class="checkout-item"><img src="${asset(line.product.image)}" alt=""><span><strong>${esc(line.product.name)}</strong><span>${esc(line.variant.label)} × ${line.quantity}</span></span><span class="checkout-item__price">${D.currency(line.lineTotal)}</span></div>`).join('');

    function totals() {
      const shippingFee = shippingMethod === 'express' ? settings.shippingExpress : (details.subtotal >= settings.freeShippingThreshold ? 0 : settings.shippingStandard);
      const discount = couponDiscount(details.subtotal);
      const total = details.subtotal + shippingFee - discount;
      document.querySelector('[data-checkout-subtotal]').textContent = D.currency(details.subtotal);
      document.querySelector('[data-checkout-shipping]').textContent = shippingFee ? D.currency(shippingFee) : 'Miễn phí';
      document.querySelector('[data-checkout-discount]').textContent = discount ? `−${D.currency(discount)}` : D.currency(0);
      document.querySelector('[data-checkout-total]').textContent = D.currency(total);
      document.querySelector('[data-place-order-amount]').textContent = D.currency(total);
      return { shippingFee, discount, total };
    }
    totals();

    form.querySelectorAll('input[name="shipping_method"]').forEach(input => input.addEventListener('change', () => { shippingMethod = input.value; totals(); }));
    form.querySelectorAll('input[name="payment"]').forEach(input => input.addEventListener('change', () => { payment = input.value; document.querySelector('[data-bank-hint]').hidden = payment !== 'bank'; }));

    function validate() {
      const rules = [
        ['customer_name', value => value.trim().length >= 2, 'Nhập họ tên người nhận.'],
        ['phone', value => /^0\d{9}$/.test(D.normalizePhone(value)), 'Số điện thoại chưa đúng. Kiểm tra lại giúp O Út.'],
        ['province', value => value.trim().length >= 2, 'Chọn tỉnh/thành.'],
        ['district', value => value.trim().length >= 2, 'Nhập quận/huyện.'],
        ['ward', value => value.trim().length >= 2, 'Nhập phường/xã.'],
        ['address', value => value.trim().length >= 5, 'Nhập địa chỉ giao hàng chi tiết.'],
      ];
      const errors = [];
      rules.forEach(([name, check, message]) => {
        const field = form.elements[name];
        const error = form.querySelector(`[data-error-for="${name}"]`);
        const ok = check(String(field.value || ''));
        field.classList.toggle('is-invalid', !ok);
        field.setAttribute('aria-invalid', String(!ok));
        if (error) error.textContent = ok ? '' : message;
        if (!ok) errors.push({ field, message });
      });
      const email = form.elements.email;
      const emailError = form.querySelector('[data-error-for="email"]');
      if (email.value && !/^\S+@\S+\.\S+$/.test(email.value)) {
        email.classList.add('is-invalid'); emailError.textContent = 'Email chưa đúng định dạng.'; errors.push({ field: email, message: 'Email chưa đúng định dạng.' });
      } else { email.classList.remove('is-invalid'); emailError.textContent = ''; }
      const summary = document.querySelector('[data-error-summary]');
      if (errors.length) {
        summary.innerHTML = `<strong>Kiểm tra lại ${errors.length} mục:</strong><ul>${errors.map(e => `<li>${esc(e.message)}</li>`).join('')}</ul>`;
        summary.hidden = false;
        errors[0].field.focus();
      } else summary.hidden = true;
      return errors.length === 0;
    }

    form.addEventListener('submit', event => {
      event.preventDefault();
      if (submitting || !validate()) return;
      submitting = true;
      const totalData = totals();
      const button = document.querySelector('[data-place-order]');
      button.disabled = true;
      button.innerHTML = `<span class="spinner" style="width:20px;height:20px;margin:0;border-width:2px"></span> Đang tạo đơn...`;
      const data = new FormData(form);
      window.setTimeout(() => {
        const order = D.addOrder({
          customer: data.get('customer_name'),
          phone: data.get('phone'),
          email: data.get('email'),
          address: `${data.get('address')}, ${data.get('ward')}, ${data.get('district')}, ${data.get('province')}`,
          note: data.get('note'),
          payment,
          shippingMethod,
          shippingFee: totalData.shippingFee,
          discount: totalData.discount,
          items: details.items.map(line => ({ productId: line.product.id, variantId: line.variant.id, name: line.product.name, variant: line.variant.label, price: line.variant.price, quantity: line.quantity, image: line.product.image, sku: line.variant.sku })),
        });
        D.clearCart();
        try { sessionStorage.setItem('ouut:last-order', order.code); } catch (_) { /* no-op */ }
        window.location.href = `${root}dat-hang-thanh-cong.html?code=${encodeURIComponent(order.code)}`;
      }, 750);
    });
  }

  function initSuccess() {
    const shell = document.querySelector('[data-order-success]');
    if (!shell) return;
    let code = query('code');
    if (!code) { try { code = sessionStorage.getItem('ouut:last-order') || ''; } catch (_) { /* no-op */ } }
    const order = D.getOrders().find(o => o.code === code) || D.getOrders()[0];
    const totalData = D.orderTotal(order);
    shell.querySelector('[data-success-code]').textContent = order.code;
    shell.querySelector('[data-success-name]').textContent = order.customer;
    shell.querySelector('[data-success-phone]').textContent = order.phone;
    shell.querySelector('[data-success-address]').textContent = order.address;
    shell.querySelector('[data-success-total]').textContent = D.currency(totalData.total);
    shell.querySelector('[data-success-payment]').textContent = order.payment === 'bank' ? 'Chuyển khoản ngân hàng' : 'Thanh toán khi nhận hàng (COD)';
    const bank = shell.querySelector('[data-transfer-card]');
    bank.hidden = order.payment !== 'bank';
    if (order.payment === 'bank') shell.querySelector('[data-transfer-content]').textContent = order.code;
    const copy = shell.querySelector('[data-copy-order]');
    copy.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(order.code); U.toast('Đã sao chép mã đơn.'); }
      catch (_) { U.toast(`Mã đơn: ${order.code}`); }
    });
    const track = shell.querySelector('[data-track-order-link]');
    track.href = `${root}tra-cuu-don.html?code=${encodeURIComponent(order.code)}&phone=${encodeURIComponent(order.phone)}`;
  }

  function timelineMarkup(order) {
    const steps = [
      ['new','O Út đã nhận đơn','Đơn hàng đã vào hệ thống.'],
      ['confirmed','Đã xác nhận','O Út kiểm tra sản phẩm và thông tin giao.'],
      ['preparing','Đang chuẩn bị hàng','Đơn đang được đóng gói cẩn thận.'],
      ['shipping','Đang giao hàng','Đơn đã được bàn giao cho đơn vị vận chuyển.'],
      ['delivered','Giao thành công','Cảm ơn bạn đã chọn O Út.'],
    ];
    const currentIndex = steps.findIndex(([status]) => status === order.status);
    return `<div class="order-timeline">${steps.map(([status,label,desc],index) => {
      const event = [...(order.history || [])].reverse().find(h => h.status === status);
      const cls = index < currentIndex ? 'is-done' : index === currentIndex ? 'is-current' : '';
      return `<div class="timeline-step ${cls}"><span class="timeline-dot">${icon(index <= currentIndex ? 'check' : 'clock')}</span><span class="timeline-copy"><strong>${label}</strong><span>${event ? D.dateTime(event.at) : desc}</span></span></div>`;
    }).join('')}</div>`;
  }

  function initTracking() {
    const form = document.querySelector('[data-track-form]');
    const result = document.querySelector('[data-track-result]');
    if (!form || !result) return;
    form.elements.code.value = query('code');
    form.elements.phone.value = query('phone');

    function search() {
      const code = form.elements.code.value.trim().toUpperCase();
      const phone = D.normalizePhone(form.elements.phone.value);
      const order = D.getOrders().find(o => o.code.toUpperCase() === code && D.normalizePhone(o.phone) === phone);
      if (!order) {
        result.innerHTML = `<div class="empty-state"><span class="empty-state__icon">${icon('receipt','icon--lg')}</span><h2>Chưa tìm thấy đơn</h2><p>Kiểm tra lại mã đơn và số điện thoại. Dữ liệu demo có thể thử: OU-260728-8F3K / 0909123456.</p></div>`;
        return;
      }
      const totalData = D.orderTotal(order);
      result.innerHTML = `<div class="panel"><div class="panel__header"><h2>Đơn ${esc(order.code)}</h2><p class="muted small">Đặt lúc ${D.dateTime(order.createdAt)}</p></div><div class="panel__body">${timelineMarkup(order)}<div class="order-summary-box"><div class="summary-lines"><div class="summary-line"><span>Người nhận</span><strong>${esc(order.customer)}</strong></div><div class="summary-line"><span>Địa chỉ</span><strong>${esc(order.address)}</strong></div><div class="summary-line"><span>Thanh toán</span><strong>${order.payment === 'bank' ? 'Chuyển khoản' : 'COD'}</strong></div><div class="summary-line summary-line--total"><span>Tổng đơn</span><strong>${D.currency(totalData.total)}</strong></div></div></div></div></div>`;
    }
    form.addEventListener('submit', event => { event.preventDefault(); search(); });
    if (form.elements.code.value && form.elements.phone.value) search();
  }

  function initBlog() {
    const feature = document.querySelector('[data-blog-feature]');
    const grid = document.querySelector('[data-blog-grid]');
    const published = D.getPosts().filter(p => p.status === 'published');
    const featured = published.find(p => p.featured) || published[0];
    if (feature && featured) {
      feature.innerHTML = `<img src="${asset(featured.cover)}" alt="${esc(featured.title)}"><div class="blog-feature__copy"><span class="eyebrow" style="color:#f0c9ad">${esc(featured.category)}</span><h2>${esc(featured.title)}</h2><p>${esc(featured.excerpt)}</p><a class="btn btn--light" href="${root}bai-viet.html?slug=${encodeURIComponent(featured.slug)}">Đọc bài ${icon('arrow-right')}</a></div>`;
    }
    if (grid) grid.innerHTML = published.filter(p => !featured || p.id !== featured.id).map(articleCard).join('');
  }

  function initArticle() {
    const body = document.querySelector('[data-article-body]');
    if (!body) return;
    const post = D.getPost(query('slug','tom-chua-hue-an-voi-gi')) || D.getPosts().find(p => p.status === 'published');
    setMeta(`${post.title} – O Út`, post.excerpt);
    body.querySelector('[data-article-category]').textContent = post.category;
    body.querySelector('[data-article-title]').textContent = post.title;
    body.querySelector('[data-article-meta]').textContent = `${post.readTime} · Cập nhật ${new Intl.DateTimeFormat('vi-VN').format(new Date(post.publishedAt))}`;
    body.querySelector('[data-article-cover]').src = asset(post.cover);
    body.querySelector('[data-article-cover]').alt = post.title;
    const inline = body.querySelector('[data-inline-product]');
    const product = D.getProducts()[0];
    inline.innerHTML = `<img src="${asset(product.image)}" alt="${esc(product.name)}"><span><strong>${esc(product.name)}</strong><span class="muted small">${esc(product.short)}</span><span class="product-card__price">${D.currency(D.productMinPrice(product))}</span></span><a class="btn btn--primary btn--sm" href="${root}chi-tiet-san-pham.html?slug=${product.slug}">Xem sản phẩm</a>`;
  }

  function initContact() {
    document.querySelectorAll('[data-contact-form]').forEach(form => {
      form.addEventListener('submit', event => {
        event.preventDefault();
        const name = form.elements.name.value.trim();
        const phone = D.normalizePhone(form.elements.phone.value);
        if (name.length < 2 || !/^0\d{9}$/.test(phone)) {
          U.toast('Nhập họ tên và số điện thoại hợp lệ giúp O Út.', { type: 'error' });
          return;
        }
        U.toast('O Út đã nhận lời nhắn. Đây là hành vi demo frontend.', { title: 'Đã gửi lời nhắn' });
        form.reset();
      });
    });
  }

  const initializers = {
    home: initHome,
    products: initProducts,
    product: initProduct,
    cart: renderCart,
    checkout: initCheckout,
    success: initSuccess,
    tracking: initTracking,
    blog: initBlog,
    article: initArticle,
    contact: initContact,
  };

  if (initializers[page]) initializers[page]();
  if (page !== 'home' && page !== 'products' && page !== 'product') U.bindGlobalProductActions(document);
})(window, document);
