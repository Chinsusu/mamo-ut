(function (window, document) {
  'use strict';

  const D = window.OUtData;
  const U = window.OUtUI;
  if (!D || !U) return;
  const root = document.body.dataset.root || '../';
  const page = document.body.dataset.adminPage || '';
  const icon = U.icon;
  const esc = U.escapeHtml;
  const asset = U.asset;

  function q(name, fallback = '') { return new URLSearchParams(window.location.search).get(name) || fallback; }

  function navLink(href, label, key, iconName, badge = '') {
    return `<a class="${page === key ? 'is-active' : ''}" href="${root}admin/${href}">${icon(iconName)}<span>${label}</span>${badge ? `<span class="badge">${badge}</span>` : ''}</a>`;
  }

  function adminSidebar() {
    const newCount = D.getOrders().filter(o => o.status === 'new').length;
    return `
      <aside class="admin-sidebar" data-admin-sidebar>
        <div class="admin-sidebar__brand"><img src="${root}assets/images/logo-mark.png" alt=""><span><strong>O Út</strong><span>Quản trị cửa hàng</span></span></div>
        <nav class="admin-nav" aria-label="Quản trị">
          <div class="admin-nav__label">Tổng quan</div>
          ${navLink('index.html','Dashboard','dashboard','chart')}
          ${navLink('orders.html','Đơn hàng','orders','receipt',newCount || '')}
          <div class="admin-nav__label">Nội dung bán hàng</div>
          ${navLink('products.html','Sản phẩm','products','box')}
          ${navLink('product-form.html','Thêm sản phẩm','product-form','plus')}
          ${navLink('posts.html','Bài viết','posts','file-text')}
          ${navLink('post-editor.html','Viết bài mới','post-editor','edit')}
          <div class="admin-nav__label">Hệ thống</div>
          ${navLink('settings.html','Cấu hình cửa hàng','settings','settings')}
          <a href="${root}index.html" target="_blank">${icon('eye')}<span>Xem website</span></a>
        </nav>
        <div class="admin-sidebar__footer">
          <div class="admin-user"><span class="admin-user__avatar">OU</span><span><strong>Chủ cửa hàng</strong><span>owner@ouut.vn</span></span><button type="button" data-admin-logout aria-label="Đăng xuất">${icon('logout')}</button></div>
        </div>
      </aside>
      <div class="admin-mobile-overlay" data-admin-overlay></div>`;
  }

  function adminTopbar() {
    return `
      <header class="admin-topbar">
        <div class="admin-topbar__left">
          <button class="icon-btn admin-menu-toggle" type="button" data-admin-menu aria-label="Mở menu">${icon('menu')}</button>
          <div class="admin-search"><input type="search" data-admin-global-search placeholder="Tìm đơn hàng, sản phẩm..."><span>${icon('search')}</span></div>
        </div>
        <div class="admin-topbar__actions">
          <button class="icon-btn" type="button" data-demo-reset title="Khôi phục dữ liệu demo">${icon('clock')}</button>
          <a class="icon-btn" href="${root}admin/orders.html?status=new" aria-label="Đơn mới">${icon('bell')}<span class="badge-count">${D.getOrders().filter(o => o.status === 'new').length}</span></a>
          <a class="icon-btn" href="${root}index.html" target="_blank" aria-label="Xem website">${icon('eye')}</a>
        </div>
      </header>`;
  }

  function renderAdminShell() {
    const sidebar = document.querySelector('[data-admin-sidebar-slot]');
    const topbar = document.querySelector('[data-admin-topbar-slot]');
    if (sidebar) sidebar.innerHTML = adminSidebar();
    if (topbar) topbar.innerHTML = adminTopbar();

    const button = document.querySelector('[data-admin-menu]');
    const panel = document.querySelector('[data-admin-sidebar]');
    const overlay = document.querySelector('[data-admin-overlay]');
    const close = () => { panel?.classList.remove('is-open'); overlay?.classList.remove('is-open'); };
    button?.addEventListener('click', () => { panel?.classList.toggle('is-open'); overlay?.classList.toggle('is-open'); });
    overlay?.addEventListener('click', close);
    document.querySelector('[data-admin-logout]')?.addEventListener('click', () => { U.toast('Đã đăng xuất khỏi phiên demo.'); window.setTimeout(() => { window.location.href = `${root}admin/login.html`; }, 400); });
    document.querySelector('[data-demo-reset]')?.addEventListener('click', () => {
      if (window.confirm('Khôi phục toàn bộ dữ liệu demo ban đầu?')) { D.resetDemo(); U.toast('Đã khôi phục dữ liệu demo.'); window.setTimeout(() => window.location.reload(), 350); }
    });
    const search = document.querySelector('[data-admin-global-search]');
    search?.addEventListener('keydown', event => {
      if (event.key !== 'Enter') return;
      const term = search.value.trim();
      if (!term) return;
      window.location.href = `${root}admin/orders.html?q=${encodeURIComponent(term)}`;
    });
  }

  function statusBadge(status) {
    const labels = D.statusLabels;
    return `<span class="status-badge status-${status}">${labels[status] || status}</span>`;
  }

  function productStatus(product) {
    const stock = product.variants.reduce((sum,v) => sum + Number(v.stock || 0), 0);
    if (product.status !== 'active') return `<span class="status-badge status-draft">Đã ẩn</span>`;
    if (stock <= 0) return `<span class="status-badge status-out">Hết hàng</span>`;
    return `<span class="status-badge status-active">Đang bán</span>`;
  }

  function initLogin() {
    const form = document.querySelector('[data-admin-login-form]');
    if (!form) return;
    form.addEventListener('submit', event => {
      event.preventDefault();
      const email = form.elements.email.value.trim();
      const password = form.elements.password.value;
      if (!/^\S+@\S+\.\S+$/.test(email) || password.length < 6) {
        U.toast('Nhập email hợp lệ và mật khẩu tối thiểu 6 ký tự.', { type: 'error' });
        return;
      }
      const button = form.querySelector('button[type="submit"]');
      button.disabled = true; button.textContent = 'Đang đăng nhập...';
      window.setTimeout(() => { window.location.href = `${root}admin/index.html`; }, 600);
    });
  }

  function initDashboard() {
    const orders = D.getOrders();
    const today = '2026-07-28';
    const todayOrders = orders.filter(o => String(o.createdAt).slice(0,10) === today);
    const todayRevenue = todayOrders.filter(o => o.status !== 'cancelled').reduce((sum,o) => sum + D.orderTotal(o).total, 0);
    const content = document.querySelector('[data-dashboard-content]');
    if (!content) return;
    const products = D.getProducts();
    const lowStock = products.filter(p => p.variants.reduce((s,v) => s+v.stock,0) < 25).length;
    content.innerHTML = `
      <div class="admin-kpis">
        <div class="kpi-card"><div class="kpi-card__top"><span class="kpi-card__icon">${icon('receipt')}</span><span class="kpi-card__change">+12%</span></div><div class="kpi-card__label">Đơn hôm nay</div><div class="kpi-card__value">${todayOrders.length}</div><div class="kpi-card__sub">${orders.filter(o=>o.status==='new').length} đơn cần xác nhận</div></div>
        <div class="kpi-card"><div class="kpi-card__top"><span class="kpi-card__icon">${icon('money')}</span><span class="kpi-card__change">+8%</span></div><div class="kpi-card__label">Doanh thu hôm nay</div><div class="kpi-card__value">${D.currency(todayRevenue)}</div><div class="kpi-card__sub">Không tính đơn đã hủy</div></div>
        <div class="kpi-card"><div class="kpi-card__top"><span class="kpi-card__icon">${icon('package')}</span><span class="kpi-card__change">Ổn định</span></div><div class="kpi-card__label">Đang chuẩn bị / giao</div><div class="kpi-card__value">${orders.filter(o=>['preparing','shipping'].includes(o.status)).length}</div><div class="kpi-card__sub">Ưu tiên xử lý trong hôm nay</div></div>
        <div class="kpi-card"><div class="kpi-card__top"><span class="kpi-card__icon">${icon('warning')}</span><span class="kpi-card__change">Cần xem</span></div><div class="kpi-card__label">Sản phẩm tồn thấp</div><div class="kpi-card__value">${lowStock}</div><div class="kpi-card__sub">Ngưỡng demo dưới 25 đơn vị</div></div>
      </div>
      <div class="admin-grid">
        <div class="admin-card">
          <div class="admin-card__header"><span><h2>Doanh thu 7 ngày</h2><p>Dữ liệu minh họa để khóa bố cục dashboard</p></span><a class="text-link" href="orders.html">Xem đơn ${icon('arrow-right','icon--sm')}</a></div>
          <div class="admin-card__body chart-wrap"><div class="bar-chart">${[38,55,44,72,61,88,76].map((h,i)=>`<div class="bar-chart__item"><div class="bar-chart__bar" style="height:${h}%" title="${D.currency(h*25000)}"></div><span class="bar-chart__label">T${i+2}</span></div>`).join('')}</div><div class="chart-legend"><span><i></i> Doanh thu theo ngày</span><span>Đơn trung bình: 2–4/ngày trong dữ liệu demo</span></div></div>
        </div>
        <div class="admin-card"><div class="admin-card__header"><span><h2>Cần xử lý</h2><p>Đi thẳng vào công việc quan trọng</p></span></div><div class="admin-card__body"><div class="quick-list">
          <a class="quick-row" href="orders.html?status=new"><span class="quick-row__icon">${icon('bell')}</span><span><strong>Đơn mới chưa xác nhận</strong><span>Kiểm tra tồn và gọi khách nếu cần</span></span><b class="quick-row__value">${orders.filter(o=>o.status==='new').length}</b></a>
          <a class="quick-row" href="orders.html?status=preparing"><span class="quick-row__icon">${icon('package')}</span><span><strong>Đơn đang chuẩn bị</strong><span>Đóng gói và cập nhật trạng thái</span></span><b class="quick-row__value">${orders.filter(o=>o.status==='preparing').length}</b></a>
          <a class="quick-row" href="posts.html"><span class="quick-row__icon">${icon('file-text')}</span><span><strong>Bài viết nháp</strong><span>Kiểm tra SEO trước khi xuất bản</span></span><b class="quick-row__value">${D.getPosts().filter(p=>p.status==='draft').length}</b></a>
          <a class="quick-row" href="products.html"><span class="quick-row__icon">${icon('box')}</span><span><strong>Tồn kho thấp</strong><span>Cập nhật trước khi chạy quảng cáo</span></span><b class="quick-row__value">${lowStock}</b></a>
        </div></div></div>
      </div>
      <div class="admin-card" style="margin-top:18px"><div class="admin-card__header"><span><h2>Đơn gần đây</h2><p>Cập nhật theo thời gian tạo đơn</p></span><a class="btn btn--secondary btn--sm" href="orders.html">Xem tất cả</a></div><div class="admin-card__body--flush admin-table-wrap"><table class="admin-table"><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thời gian</th><th></th></tr></thead><tbody>${orders.slice(0,5).map(o=>{const total=D.orderTotal(o);return `<tr><td><strong>${esc(o.code)}</strong></td><td class="table-customer"><strong>${esc(o.customer)}</strong><span>${esc(o.phone)}</span></td><td>${o.items.reduce((s,i)=>s+i.quantity,0)} món</td><td><strong>${D.currency(total.total)}</strong></td><td>${statusBadge(o.status)}</td><td>${D.dateTime(o.createdAt)}</td><td><div class="table-actions"><a href="order-detail.html?id=${o.id}" aria-label="Xem đơn">${icon('eye')}</a></div></td></tr>`}).join('')}</tbody></table></div></div>`;
  }

  function initProducts() {
    const table = document.querySelector('[data-admin-products-table]');
    const filters = document.querySelector('[data-admin-product-filters]');
    const search = document.querySelector('[data-admin-product-search]');
    if (!table) return;
    let state = { status: 'all', q: '' };
    if (filters) filters.querySelectorAll('[data-product-status]').forEach(btn => btn.addEventListener('click', () => { state.status = btn.dataset.productStatus; filters.querySelectorAll('.admin-filter').forEach(b=>b.classList.toggle('is-active',b===btn)); render(); }));
    search?.addEventListener('input', () => { state.q = search.value.trim().toLowerCase(); render(); });

    function render() {
      let products = D.getProducts();
      if (state.status === 'active') products = products.filter(p=>p.status==='active');
      if (state.status === 'hidden') products = products.filter(p=>p.status!=='active');
      if (state.status === 'low') products = products.filter(p=>p.variants.reduce((s,v)=>s+v.stock,0)<25);
      if (state.q) products = products.filter(p=>`${p.name} ${p.slug}`.toLowerCase().includes(state.q));
      table.innerHTML = products.map(p=>{const stock=p.variants.reduce((s,v)=>s+v.stock,0);return `<tr data-product-row="${p.id}"><td><input type="checkbox" aria-label="Chọn ${esc(p.name)}"></td><td><div class="table-product"><img src="${asset(p.image)}" alt=""><span><strong>${esc(p.name)}</strong><span>${esc(p.slug)}</span></span></div></td><td>${esc(p.categoryLabel)}</td><td>${p.variants.length} biến thể</td><td><strong>${D.currency(D.productMinPrice(p))}</strong></td><td>${stock}</td><td>${productStatus(p)}</td><td><div class="table-actions"><a href="product-form.html?id=${p.id}" aria-label="Sửa">${icon('edit')}</a><a href="${root}chi-tiet-san-pham.html?slug=${p.slug}" target="_blank" aria-label="Xem">${icon('eye')}</a><button type="button" data-toggle-product="${p.id}" aria-label="Ẩn hoặc hiện">${icon(p.status==='active'?'x':'check')}</button><button type="button" data-delete-product="${p.id}" aria-label="Lưu trữ">${icon('trash')}</button></div></td></tr>`}).join('');
      table.querySelectorAll('[data-toggle-product]').forEach(btn=>btn.addEventListener('click',()=>{const list=D.getProducts();const p=list.find(x=>x.id===Number(btn.dataset.toggleProduct));p.status=p.status==='active'?'hidden':'active';D.saveProducts(list);U.toast(p.status==='active'?'Đã đưa sản phẩm trở lại cửa hàng.':'Đã ẩn sản phẩm khỏi storefront.');render();}));
      table.querySelectorAll('[data-delete-product]').forEach(btn=>btn.addEventListener('click',()=>{if(!confirm('Lưu trữ sản phẩm này? Dữ liệu đơn cũ vẫn được giữ trong hệ thống thật.'))return;const list=D.getProducts();const p=list.find(x=>x.id===Number(btn.dataset.deleteProduct));p.status='hidden';D.saveProducts(list);U.toast('Sản phẩm đã được lưu trữ (soft delete demo).');render();}));
    }
    render();
  }

  function initProductForm() {
    const form = document.querySelector('[data-admin-product-form]');
    if (!form) return;
    const id = Number(q('id',0));
    const products = D.getProducts();
    const existing = products.find(p=>p.id===id);
    const product = existing ? JSON.parse(JSON.stringify(existing)) : {
      id: Math.max(0,...products.map(p=>p.id))+1, slug:'', name:'', category:'tom-chua', categoryLabel:'Tôm chua', short:'', description:'', image:'assets/images/tom-chua.jpg', gallery:['assets/images/tom-chua.jpg'], featured:false, status:'hidden', badge:'', rating:5, reviews:0, sold:0, ingredients:'', usage:'', storage:'', shelfLife:'', variants:[{id:`new-${Date.now()}`,label:'Hũ 500g',weight:500,price:0,compareAt:0,stock:0,sku:''}]
    };
    document.querySelector('[data-form-title]').textContent = existing ? `Sửa ${existing.name}` : 'Thêm sản phẩm mới';
    const fields = ['name','slug','short','description','ingredients','usage','storage','shelfLife','badge'];
    fields.forEach(name=>{if(form.elements[name]) form.elements[name].value=product[name]||'';});
    form.elements.category.value=product.category;
    form.elements.status.checked=product.status==='active';
    form.elements.featured.checked=Boolean(product.featured);
    const preview = document.querySelector('[data-product-image-preview]');
    preview.src=asset(product.image);

    const tabs=document.querySelectorAll('[data-admin-tab]');
    const panes=document.querySelectorAll('[data-admin-pane]');
    tabs.forEach(tab=>tab.addEventListener('click',()=>{tabs.forEach(t=>t.classList.toggle('is-active',t===tab));panes.forEach(p=>p.hidden=p.dataset.adminPane!==tab.dataset.adminTab);}));

    const variantsBody=document.querySelector('[data-variants-body]');
    function renderVariants(){variantsBody.innerHTML=product.variants.map((v,i)=>`<tr data-variant-index="${i}"><td><input data-v="label" value="${esc(v.label)}"></td><td><input data-v="sku" value="${esc(v.sku)}"></td><td><input data-v="weight" type="number" min="1" value="${v.weight}"></td><td><input data-v="price" type="number" min="0" step="1000" value="${v.price}"></td><td><input data-v="stock" type="number" min="0" value="${v.stock}"></td><td><button class="icon-btn" type="button" data-remove-variant="${i}" aria-label="Xóa biến thể">${icon('trash')}</button></td></tr>`).join('');variantsBody.querySelectorAll('input[data-v]').forEach(input=>input.addEventListener('input',()=>{const row=input.closest('[data-variant-index]');product.variants[Number(row.dataset.variantIndex)][input.dataset.v]=input.type==='number'?Number(input.value):input.value;}));variantsBody.querySelectorAll('[data-remove-variant]').forEach(btn=>btn.addEventListener('click',()=>{if(product.variants.length<=1)return U.toast('Mỗi sản phẩm cần ít nhất một biến thể.',{type:'error'});product.variants.splice(Number(btn.dataset.removeVariant),1);renderVariants();}));}
    renderVariants();
    document.querySelector('[data-add-variant]').addEventListener('click',()=>{product.variants.push({id:`v-${Date.now()}`,label:'Biến thể mới',sku:'',weight:500,price:0,compareAt:0,stock:0});renderVariants();});
    document.querySelector('[data-image-upload]').addEventListener('click',()=>U.toast('Prototype FE hiển thị trạng thái upload. Backend Laravel sẽ xử lý file thật.'));

    form.addEventListener('submit',event=>{
      event.preventDefault();
      product.name=form.elements.name.value.trim();
      product.slug=(form.elements.slug.value.trim()||product.name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''));
      product.short=form.elements.short.value.trim();product.description=form.elements.description.value.trim();product.ingredients=form.elements.ingredients.value.trim();product.usage=form.elements.usage.value.trim();product.storage=form.elements.storage.value.trim();product.shelfLife=form.elements.shelfLife.value.trim();product.badge=form.elements.badge.value.trim();product.category=form.elements.category.value;product.categoryLabel=form.elements.category.options[form.elements.category.selectedIndex].text;product.status=form.elements.status.checked?'active':'hidden';product.featured=form.elements.featured.checked;
      if(product.name.length<3||!product.slug||product.variants.some(v=>!v.sku||v.price<=0)){U.toast('Cần tên, slug và SKU/giá hợp lệ cho tất cả biến thể.',{type:'error'});return;}
      const list=D.getProducts();const idx=list.findIndex(p=>p.id===product.id);if(idx>=0)list[idx]=product;else list.push(product);D.saveProducts(list);U.toast('Đã lưu sản phẩm vào dữ liệu demo.');window.setTimeout(()=>{window.location.href='products.html';},450);
    });
  }

  function initOrders() {
    const body=document.querySelector('[data-admin-orders-table]');
    const filters=document.querySelector('[data-admin-order-filters]');
    const search=document.querySelector('[data-admin-order-search]');
    if(!body)return;
    let state={status:q('status','all'),q:q('q','').toLowerCase()};
    if(search)search.value=q('q','');
    filters?.querySelectorAll('[data-order-status]').forEach(btn=>{btn.classList.toggle('is-active',btn.dataset.orderStatus===state.status);btn.addEventListener('click',()=>{state.status=btn.dataset.orderStatus;filters.querySelectorAll('.admin-filter').forEach(b=>b.classList.toggle('is-active',b===btn));render();});});
    search?.addEventListener('input',()=>{state.q=search.value.trim().toLowerCase();render();});
    function render(){let orders=D.getOrders();if(state.status!=='all')orders=orders.filter(o=>o.status===state.status);if(state.q)orders=orders.filter(o=>`${o.code} ${o.customer} ${o.phone}`.toLowerCase().includes(state.q));body.innerHTML=orders.map(o=>{const t=D.orderTotal(o);return `<tr><td><strong>${esc(o.code)}</strong><span class="small muted" style="display:block">${esc(o.source)}</span></td><td class="table-customer"><strong>${esc(o.customer)}</strong><span>${esc(o.phone)}</span></td><td>${o.items.reduce((s,i)=>s+i.quantity,0)} món</td><td><strong>${D.currency(t.total)}</strong></td><td>${o.payment==='bank'?'Chuyển khoản':'COD'}</td><td>${statusBadge(o.status)}</td><td>${D.dateTime(o.createdAt)}</td><td><div class="table-actions"><a href="order-detail.html?id=${o.id}" aria-label="Xem đơn">${icon('eye')}</a><button type="button" data-print-order="${o.id}" aria-label="In đơn">${icon('printer')}</button></div></td></tr>`}).join('');body.querySelectorAll('[data-print-order]').forEach(btn=>btn.addEventListener('click',()=>U.toast(`Đã mở hành vi in đơn #${btn.dataset.printOrder} ở prototype.`)));}
    render();
  }

  const statusNext={new:['confirmed','cancelled'],confirmed:['preparing','cancelled'],preparing:['shipping','cancelled'],shipping:['delivered'],delivered:[],cancelled:[]};
  function initOrderDetail(){
    const shell=document.querySelector('[data-order-detail]');if(!shell)return;const id=Number(q('id',1));let order=D.getOrders().find(o=>o.id===id)||D.getOrders()[0];
    function render(){const total=D.orderTotal(order);document.querySelector('[data-order-heading]').textContent=`Đơn ${order.code}`;shell.innerHTML=`
      <div class="order-detail-grid">
        <div>
          <div class="admin-form-card"><div class="admin-form-card__header"><h2>Sản phẩm trong đơn</h2><p>Snapshot tại thời điểm khách đặt</p></div><div class="admin-form-card__body"><div class="order-products">${order.items.map(i=>`<div class="order-product"><img src="${asset(i.image)}" alt=""><span><strong>${esc(i.name)}</strong><span>${esc(i.variant)} · ${esc(i.sku)} × ${i.quantity}</span></span><span class="order-product__price">${D.currency(i.price*i.quantity)}</span></div>`).join('')}</div><div class="summary-lines" style="margin-top:18px"><div class="summary-line"><span>Tạm tính</span><strong>${D.currency(total.subtotal)}</strong></div><div class="summary-line"><span>Phí giao</span><strong>${D.currency(order.shippingFee)}</strong></div><div class="summary-line"><span>Giảm giá</span><strong>−${D.currency(order.discount)}</strong></div><div class="summary-line summary-line--total"><span>Tổng cộng</span><strong>${D.currency(total.total)}</strong></div></div></div></div>
          <div class="admin-form-card"><div class="admin-form-card__header"><h2>Thông tin giao hàng</h2></div><div class="admin-form-card__body"><div class="order-info-grid"><div class="info-box"><span>Khách hàng</span><strong>${esc(order.customer)}</strong></div><div class="info-box"><span>Số điện thoại</span><strong>${esc(order.phone)}</strong></div><div class="info-box" style="grid-column:1/-1"><span>Địa chỉ</span><strong>${esc(order.address)}</strong></div><div class="info-box"><span>Thanh toán</span><strong>${order.payment==='bank'?'Chuyển khoản':'COD'} · ${order.paymentStatus}</strong></div><div class="info-box"><span>Nguồn đơn</span><strong>${esc(order.source)}</strong></div></div><div class="admin-field" style="margin-top:14px"><label>Ghi chú nội bộ</label><textarea class="admin-control" data-internal-note placeholder="Chỉ người quản trị nhìn thấy"></textarea></div></div></div>
        </div>
        <aside>
          <div class="admin-form-card"><div class="admin-form-card__header"><h2>Trạng thái đơn</h2><p>${statusBadge(order.status)}</p></div><div class="admin-form-card__body"><div class="order-status-actions">${(statusNext[order.status]||[]).map(s=>`<button class="btn ${s==='cancelled'?'btn--danger':'btn--primary'} btn--sm" type="button" data-next-status="${s}">${D.statusLabels[s]}</button>`).join('')||'<span class="small muted">Đơn đã kết thúc quy trình.</span>'}</div></div></div>
          <div class="admin-form-card"><div class="admin-form-card__header"><h2>Lịch sử trạng thái</h2></div><div class="admin-form-card__body"><div class="admin-timeline">${[...(order.history||[])].reverse().map(h=>`<div class="admin-timeline__item"><span class="admin-timeline__dot">${icon('check','icon--sm')}</span><span class="admin-timeline__copy"><strong>${esc(h.label)}</strong><span>${D.dateTime(h.at)}</span></span></div>`).join('')}</div></div></div>
          <div class="admin-form-card"><div class="admin-form-card__body"><button class="btn btn--secondary btn--block" type="button" data-print-detail>${icon('printer')} In phiếu đơn</button></div></div>
        </aside>
      </div>`;
      shell.querySelectorAll('[data-next-status]').forEach(btn=>btn.addEventListener('click',()=>{if(btn.dataset.nextStatus==='cancelled'&&!confirm('Hủy đơn này? Trong hệ thống thật tồn kho phải được hoàn đúng một lần.'))return;order=D.updateOrderStatus(order.id,btn.dataset.nextStatus);U.toast(`Đã chuyển trạng thái: ${D.statusLabels[order.status]}.`);render();}));
      shell.querySelector('[data-print-detail]').addEventListener('click',()=>window.print());
    }render();
  }

  function initPosts(){
    const body=document.querySelector('[data-admin-posts-table]');if(!body)return;
    function render(){const posts=D.getPosts();body.innerHTML=posts.map(p=>`<tr><td><div class="table-product"><img src="${asset(p.cover)}" alt=""><span><strong>${esc(p.title)}</strong><span>/${esc(p.slug)}</span></span></div></td><td>${esc(p.category)}</td><td>${p.status==='published'?'<span class="status-badge status-active">Đã xuất bản</span>':'<span class="status-badge status-draft">Bản nháp</span>'}</td><td>${esc(p.readTime)}</td><td>${new Intl.DateTimeFormat('vi-VN').format(new Date(p.publishedAt))}</td><td><div class="table-actions"><a href="post-editor.html?id=${p.id}" aria-label="Sửa">${icon('edit')}</a><a href="${root}bai-viet.html?slug=${p.slug}" target="_blank" aria-label="Xem">${icon('eye')}</a><button type="button" data-toggle-post="${p.id}" aria-label="Đổi trạng thái">${icon(p.status==='published'?'x':'check')}</button></div></td></tr>`).join('');body.querySelectorAll('[data-toggle-post]').forEach(btn=>btn.addEventListener('click',()=>{const list=D.getPosts();const p=list.find(x=>x.id===Number(btn.dataset.togglePost));p.status=p.status==='published'?'draft':'published';D.savePosts(list);U.toast(p.status==='published'?'Đã xuất bản bài viết.':'Đã chuyển bài về bản nháp.');render();}));}render();
  }

  function initPostEditor(){
    const form=document.querySelector('[data-post-editor-form]');if(!form)return;const id=Number(q('id',0));const posts=D.getPosts();const existing=posts.find(p=>p.id===id);const post=existing?JSON.parse(JSON.stringify(existing)):{id:Math.max(0,...posts.map(p=>p.id))+1,title:'',slug:'',excerpt:'',cover:'assets/images/blog-1.jpg',category:'Cách dùng',status:'draft',publishedAt:new Date().toISOString().slice(0,10),readTime:'5 phút đọc',featured:false};document.querySelector('[data-post-editor-title]').textContent=existing?'Sửa bài viết':'Viết bài mới';['title','slug','excerpt','category','publishedAt','readTime'].forEach(n=>{if(form.elements[n])form.elements[n].value=post[n]||'';});form.elements.status.checked=post.status==='published';document.querySelector('[data-post-cover]').src=asset(post.cover);
    const editor=document.querySelector('[data-editor-area]');editor.innerHTML=existing?`<h2>${esc(post.title)}</h2><p>${esc(post.excerpt)}</p><p>Nội dung demo có thể chỉnh trực tiếp trong vùng editor này. Khi tích hợp Laravel, dùng editor có sanitize HTML phía server.</p>`:'<h2>Tiêu đề phần nội dung</h2><p>Bắt đầu viết câu chuyện, hướng dẫn hoặc công thức tại đây...</p>';
    document.querySelectorAll('[data-editor-command]').forEach(btn=>btn.addEventListener('click',()=>document.execCommand(btn.dataset.editorCommand,false,null)));
    form.addEventListener('submit',event=>{event.preventDefault();post.title=form.elements.title.value.trim();post.slug=form.elements.slug.value.trim()||post.title.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');post.excerpt=form.elements.excerpt.value.trim();post.category=form.elements.category.value;post.publishedAt=form.elements.publishedAt.value;post.readTime=form.elements.readTime.value;post.status=form.elements.status.checked?'published':'draft';if(post.title.length<5||post.excerpt.length<20){U.toast('Tiêu đề và đoạn giới thiệu cần đầy đủ hơn.',{type:'error'});return;}const list=D.getPosts();const idx=list.findIndex(p=>p.id===post.id);if(idx>=0)list[idx]=post;else list.push(post);D.savePosts(list);U.toast('Đã lưu bài viết demo.');window.setTimeout(()=>window.location.href='posts.html',420);});
  }

  function initSettings(){
    const form=document.querySelector('[data-settings-form]');if(!form)return;const settings=D.getSettings();Object.keys(settings).forEach(k=>{if(form.elements[k])form.elements[k].value=settings[k];});form.addEventListener('submit',event=>{event.preventDefault();const next={...settings};Object.keys(next).forEach(k=>{if(form.elements[k])next[k]=form.elements[k].type==='number'?Number(form.elements[k].value):form.elements[k].value.trim();});D.saveSettings(next);U.toast('Đã lưu cấu hình cửa hàng.');});
  }

  renderAdminShell();
  const inits={login:initLogin,dashboard:initDashboard,products:initProducts,'product-form':initProductForm,orders:initOrders,'order-detail':initOrderDetail,posts:initPosts,'post-editor':initPostEditor,settings:initSettings};
  if(inits[page])inits[page]();
})(window, document);
