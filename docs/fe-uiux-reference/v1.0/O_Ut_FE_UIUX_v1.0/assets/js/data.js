(function (window) {
  'use strict';

  const NS = 'ouut_fe_v1';
  const keys = {
    products: `${NS}:products`,
    posts: `${NS}:posts`,
    orders: `${NS}:orders`,
    cart: `${NS}:cart`,
    settings: `${NS}:settings`,
    favorites: `${NS}:favorites`,
  };

  const seedProducts = [
    {
      id: 1,
      slug: 'tom-chua-hue',
      name: 'Tôm chua Huế O Út',
      category: 'tom-chua',
      categoryLabel: 'Tôm chua',
      short: 'Tôm giòn, vị chua ngọt hài hòa, thơm riềng và ớt theo kiểu Huế.',
      description: 'Tôm chua O Út được làm theo mẻ nhỏ, cân bằng vị chua, ngọt, mặn và thơm. Món hợp dùng cùng thịt luộc, cơm nóng, bánh tráng cuốn hoặc làm quà Huế.',
      image: 'assets/images/tom-chua.jpg',
      gallery: ['assets/images/tom-chua-jar.jpg', 'assets/images/tom-chua.jpg', 'assets/images/tom-chua-lifestyle.jpg', 'assets/images/hero-ouut.jpg'],
      featured: true,
      status: 'active',
      badge: 'Bán chạy',
      rating: 4.9,
      reviews: 128,
      sold: 1264,
      ingredients: 'Tôm, riềng, tỏi, ớt, nước mắm, đường và gia vị theo công thức nhà làm.',
      usage: 'Dùng trực tiếp cùng thịt luộc, cơm nóng, bún hoặc bánh tráng cuốn. Ngon hơn khi dùng lạnh nhẹ.',
      storage: 'Bảo quản ngăn mát 2–5°C, dùng muỗng sạch và đậy kín sau khi mở.',
      shelfLife: '30 ngày kể từ ngày sản xuất khi bảo quản đúng hướng dẫn.',
      variants: [
        { id: 'tc-250', label: 'Hũ 250g', weight: 250, price: 75000, compareAt: 82000, stock: 34, sku: 'OUT-TC-250' },
        { id: 'tc-500', label: 'Hũ 500g', weight: 500, price: 125000, compareAt: 139000, stock: 42, sku: 'OUT-TC-500' },
        { id: 'tc-1000', label: 'Hũ 1kg', weight: 1000, price: 235000, compareAt: 255000, stock: 16, sku: 'OUT-TC-1000' },
      ],
    },
    {
      id: 2,
      slug: 'tep-chua-hue',
      name: 'Tép chua Huế O Út',
      category: 'tep-chua',
      categoryLabel: 'Tép chua',
      short: 'Tép nhỏ thấm vị, mềm vừa, thơm riềng tỏi và dễ ăn cùng cơm nóng.',
      description: 'Tép chua có vị đậm hơn tôm chua một chút, hạt tép nhỏ quyện gia vị, rất hợp với bữa cơm gia đình và các món cuốn.',
      image: 'assets/images/tep-chua.jpg',
      gallery: ['assets/images/tep-chua.jpg', 'assets/images/tep-chua-lifestyle.jpg', 'assets/images/hero-food-spread.jpg'],
      featured: true,
      status: 'active',
      badge: 'Đậm vị',
      rating: 4.8,
      reviews: 96,
      sold: 982,
      ingredients: 'Tép, riềng, tỏi, ớt, nước mắm, đường và gia vị nhà làm.',
      usage: 'Ăn cùng cơm nóng, thịt luộc, rau sống, bánh tráng hoặc dùng làm món chấm.',
      storage: 'Bảo quản lạnh 2–5°C. Sau khi mở nắp nên dùng trong 14 ngày.',
      shelfLife: '30 ngày kể từ ngày sản xuất.',
      variants: [
        { id: 'tepc-250', label: 'Hũ 250g', weight: 250, price: 69000, compareAt: 76000, stock: 25, sku: 'OUT-TEP-250' },
        { id: 'tepc-500', label: 'Hũ 500g', weight: 500, price: 115000, compareAt: 129000, stock: 31, sku: 'OUT-TEP-500' },
        { id: 'tepc-1000', label: 'Hũ 1kg', weight: 1000, price: 215000, compareAt: 235000, stock: 11, sku: 'OUT-TEP-1000' },
      ],
    },
    {
      id: 3,
      slug: 'cha-ca-xay-hue',
      name: 'Chả cá xay Huế O Út',
      category: 'cha-ca-xay',
      categoryLabel: 'Chả cá xay',
      short: 'Chả cá dai mềm tự nhiên, thơm tiêu và hành, tiện chiên, hấp hoặc nấu bún.',
      description: 'Chả cá xay được chia phần tiện dùng, vị vừa ăn, có độ dai mềm tự nhiên và phù hợp cho bữa cơm nhanh nhưng vẫn ngon miệng.',
      image: 'assets/images/cha-ca-xay.jpg',
      gallery: ['assets/images/cha-ca-xay.jpg', 'assets/images/cha-ca-lifestyle.jpg', 'assets/images/blog-2.jpg'],
      featured: true,
      status: 'active',
      badge: 'Mới làm',
      rating: 4.8,
      reviews: 102,
      sold: 756,
      ingredients: 'Cá, tiêu, hành, nước mắm và gia vị. Thành phần chi tiết cần cập nhật theo công thức thực tế.',
      usage: 'Vo viên hoặc tạo miếng, chiên áp chảo, hấp, nấu bún hoặc nấu canh.',
      storage: 'Bảo quản đông. Rã đông trong ngăn mát trước khi chế biến.',
      shelfLife: 'Tối đa 60 ngày khi bảo quản đông đúng nhiệt độ.',
      variants: [
        { id: 'cc-250', label: 'Gói 250g', weight: 250, price: 75000, compareAt: 82000, stock: 18, sku: 'OUT-CC-250' },
        { id: 'cc-500', label: 'Gói 500g', weight: 500, price: 135000, compareAt: 148000, stock: 24, sku: 'OUT-CC-500' },
        { id: 'cc-1000', label: 'Gói 1kg', weight: 1000, price: 255000, compareAt: 279000, stock: 9, sku: 'OUT-CC-1000' },
      ],
    },
    {
      id: 4,
      slug: 'mam-ro-hue',
      name: 'Mắm rò Huế O Út',
      category: 'mam-ro',
      categoryLabel: 'Mắm rò',
      short: 'Mắm rò thơm nồng vừa phải, vị đậm kiểu Huế, dùng chấm hoặc ăn cùng cơm.',
      description: 'Mắm rò O Út hướng đến vị đậm nhưng cân bằng, có thể dùng trực tiếp sau khi gia giảm hoặc làm nước chấm cho rau, thịt luộc và món cuốn.',
      image: 'assets/images/mam-ro.jpg',
      gallery: ['assets/images/mam-ro.jpg', 'assets/images/mam-ro-lifestyle.jpg', 'assets/images/blog-3.jpg'],
      featured: true,
      status: 'active',
      badge: 'Chuẩn vị',
      rating: 4.7,
      reviews: 74,
      sold: 688,
      ingredients: 'Cá rò, muối, ớt và gia vị. Cần xác nhận lại định lượng theo nhãn sản phẩm thật.',
      usage: 'Gia giảm tỏi, ớt, đường, chanh theo khẩu vị; dùng cùng thịt luộc, rau hoặc cơm nóng.',
      storage: 'Bảo quản ngăn mát và luôn dùng dụng cụ sạch.',
      shelfLife: '60 ngày kể từ ngày sản xuất khi bảo quản lạnh.',
      variants: [
        { id: 'mr-250', label: 'Hũ 250g', weight: 250, price: 55000, compareAt: 62000, stock: 22, sku: 'OUT-MR-250' },
        { id: 'mr-500', label: 'Hũ 500g', weight: 500, price: 95000, compareAt: 105000, stock: 17, sku: 'OUT-MR-500' },
      ],
    },
    {
      id: 5,
      slug: 'mam-ruot-hue',
      name: 'Mắm ruốt Huế O Út',
      category: 'mam-ruot',
      categoryLabel: 'Mắm ruốt',
      short: 'Mắm ruốt đậm vị, thơm đặc trưng, dùng chưng thịt, nấu bún bò hoặc làm nước chấm.',
      description: 'Mắm ruốt Huế là gia vị nền đặc trưng cho nhiều món miền Trung. O Út đóng hũ gọn, dễ bảo quản và có hướng dẫn dùng rõ ràng.',
      image: 'assets/images/mam-ruot.jpg',
      gallery: ['assets/images/mam-ruot.jpg', 'assets/images/mam-ruot-lifestyle.jpg', 'assets/images/blog-4.jpg'],
      featured: true,
      status: 'active',
      badge: 'Gia truyền',
      rating: 4.8,
      reviews: 82,
      sold: 641,
      ingredients: 'Ruốc, muối và gia vị theo quy trình truyền thống.',
      usage: 'Dùng chưng thịt, pha nước chấm, nêm bún bò hoặc các món kho.',
      storage: 'Đậy kín, bảo quản nơi mát; sau khi mở nên để ngăn mát.',
      shelfLife: '90 ngày kể từ ngày sản xuất.',
      variants: [
        { id: 'mruot-250', label: 'Hũ 250g', weight: 250, price: 49000, compareAt: 55000, stock: 37, sku: 'OUT-MRUOT-250' },
        { id: 'mruot-500', label: 'Hũ 500g', weight: 500, price: 85000, compareAt: 95000, stock: 26, sku: 'OUT-MRUOT-500' },
      ],
    },
  ];

  const seedPosts = [
    {
      id: 1,
      slug: 'tom-chua-hue-an-voi-gi',
      title: 'Tôm chua Huế ăn với gì để tròn vị nhất?',
      excerpt: 'Từ thịt luộc, cơm nóng đến bánh tráng cuốn: những cách dùng tôm chua dễ làm và đúng chất bữa cơm Huế.',
      cover: 'assets/images/blog-1.jpg',
      category: 'Cách dùng',
      status: 'published',
      publishedAt: '2026-07-25',
      readTime: '6 phút đọc',
      featured: true,
    },
    {
      id: 2,
      slug: 'cach-bao-quan-dac-san-nha-lam',
      title: 'Cách bảo quản đặc sản nhà làm sau khi mở nắp',
      excerpt: 'Một vài nguyên tắc nhỏ giúp tôm chua, tép chua và các loại mắm giữ vị tốt, an toàn và không bị lẫn mùi.',
      cover: 'assets/images/blog-2.jpg',
      category: 'Bảo quản',
      status: 'published',
      publishedAt: '2026-07-20',
      readTime: '5 phút đọc',
      featured: false,
    },
    {
      id: 3,
      slug: 'mam-ro-la-gi',
      title: 'Mắm rò là gì? Vị Huế trong một chén mắm nhỏ',
      excerpt: 'Tìm hiểu hương vị, cách gia giảm và những món ăn hợp với mắm rò — một đặc sản đậm chất miền Trung.',
      cover: 'assets/images/blog-3.jpg',
      category: 'Chuyện món Huế',
      status: 'published',
      publishedAt: '2026-07-16',
      readTime: '7 phút đọc',
      featured: false,
    },
    {
      id: 4,
      slug: 'mam-ruot-hue-dung-nau-mon-gi',
      title: 'Mắm ruốt Huế dùng nấu món gì?',
      excerpt: 'Bốn cách dùng mắm ruốt đơn giản: chưng thịt, pha chấm, nêm bún bò và làm món kho đậm đà.',
      cover: 'assets/images/blog-4.jpg',
      category: 'Công thức',
      status: 'draft',
      publishedAt: '2026-07-28',
      readTime: '8 phút đọc',
      featured: false,
    },
  ];

  const seedOrders = [
    {
      id: 1,
      code: 'OU-260728-8F3K',
      customer: 'Nguyễn Thị Hương',
      phone: '0909123456',
      email: 'huong@example.com',
      address: '12 Nguyễn Huệ, Phú Hội, TP. Huế, Thừa Thiên Huế',
      note: 'Giao giờ hành chính, gọi trước giúp mình.',
      payment: 'cod',
      paymentStatus: 'pending',
      shippingMethod: 'standard',
      shippingFee: 30000,
      discount: 0,
      status: 'preparing',
      source: 'Website',
      createdAt: '2026-07-28T08:42:00+07:00',
      items: [
        { productId: 1, variantId: 'tc-500', name: 'Tôm chua Huế O Út', variant: 'Hũ 500g', price: 125000, quantity: 2, image: 'assets/images/tom-chua.jpg', sku: 'OUT-TC-500' },
        { productId: 5, variantId: 'mruot-500', name: 'Mắm ruốt Huế O Út', variant: 'Hũ 500g', price: 85000, quantity: 1, image: 'assets/images/mam-ruot.jpg', sku: 'OUT-MRUOT-500' },
      ],
      history: [
        { status: 'new', label: 'Đã nhận đơn', at: '2026-07-28T08:42:00+07:00' },
        { status: 'confirmed', label: 'Đã xác nhận', at: '2026-07-28T09:05:00+07:00' },
        { status: 'preparing', label: 'Đang chuẩn bị hàng', at: '2026-07-28T10:10:00+07:00' },
      ],
    },
    {
      id: 2,
      code: 'OU-260728-A91P',
      customer: 'Trần Minh Đức',
      phone: '0935123456',
      email: '',
      address: '85 Lê Lợi, Đông Hà, Quảng Trị',
      note: '',
      payment: 'bank',
      paymentStatus: 'paid',
      shippingMethod: 'standard',
      shippingFee: 30000,
      discount: 20000,
      status: 'confirmed',
      source: 'Facebook',
      createdAt: '2026-07-28T07:15:00+07:00',
      items: [
        { productId: 2, variantId: 'tepc-500', name: 'Tép chua Huế O Út', variant: 'Hũ 500g', price: 115000, quantity: 2, image: 'assets/images/tep-chua.jpg', sku: 'OUT-TEP-500' },
        { productId: 3, variantId: 'cc-500', name: 'Chả cá xay Huế O Út', variant: 'Gói 500g', price: 135000, quantity: 1, image: 'assets/images/cha-ca-xay.jpg', sku: 'OUT-CC-500' },
      ],
      history: [
        { status: 'new', label: 'Đã nhận đơn', at: '2026-07-28T07:15:00+07:00' },
        { status: 'confirmed', label: 'Đã xác nhận', at: '2026-07-28T07:45:00+07:00' },
      ],
    },
    {
      id: 3,
      code: 'OU-260727-K2F7',
      customer: 'Lê Ngọc Hà',
      phone: '0914123456',
      email: '',
      address: 'Quận Hải Châu, Đà Nẵng',
      note: '',
      payment: 'cod',
      paymentStatus: 'pending',
      shippingMethod: 'express',
      shippingFee: 45000,
      discount: 0,
      status: 'shipping',
      source: 'Website',
      createdAt: '2026-07-27T16:32:00+07:00',
      items: [
        { productId: 1, variantId: 'tc-250', name: 'Tôm chua Huế O Út', variant: 'Hũ 250g', price: 75000, quantity: 1, image: 'assets/images/tom-chua.jpg', sku: 'OUT-TC-250' },
        { productId: 4, variantId: 'mr-500', name: 'Mắm rò Huế O Út', variant: 'Hũ 500g', price: 95000, quantity: 1, image: 'assets/images/mam-ro.jpg', sku: 'OUT-MR-500' },
      ],
      history: [
        { status: 'new', label: 'Đã nhận đơn', at: '2026-07-27T16:32:00+07:00' },
        { status: 'confirmed', label: 'Đã xác nhận', at: '2026-07-27T17:01:00+07:00' },
        { status: 'preparing', label: 'Đang chuẩn bị hàng', at: '2026-07-28T07:20:00+07:00' },
        { status: 'shipping', label: 'Đang giao hàng', at: '2026-07-28T13:15:00+07:00' },
      ],
    },
    {
      id: 4,
      code: 'OU-260726-M8Q2',
      customer: 'Phạm Thanh Mai',
      phone: '0988123456',
      email: 'mai@example.com',
      address: 'TP. Hồ Chí Minh',
      note: '',
      payment: 'bank',
      paymentStatus: 'paid',
      shippingMethod: 'standard',
      shippingFee: 30000,
      discount: 0,
      status: 'delivered',
      source: 'TikTok',
      createdAt: '2026-07-26T11:05:00+07:00',
      items: [
        { productId: 1, variantId: 'tc-1000', name: 'Tôm chua Huế O Út', variant: 'Hũ 1kg', price: 235000, quantity: 1, image: 'assets/images/tom-chua.jpg', sku: 'OUT-TC-1000' },
      ],
      history: [
        { status: 'new', label: 'Đã nhận đơn', at: '2026-07-26T11:05:00+07:00' },
        { status: 'confirmed', label: 'Đã xác nhận', at: '2026-07-26T11:40:00+07:00' },
        { status: 'preparing', label: 'Đang chuẩn bị hàng', at: '2026-07-26T13:00:00+07:00' },
        { status: 'shipping', label: 'Đang giao hàng', at: '2026-07-27T08:15:00+07:00' },
        { status: 'delivered', label: 'Giao thành công', at: '2026-07-28T15:20:00+07:00' },
      ],
    },
  ];

  const seedSettings = {
    storeName: 'O Út đặc sản Huế',
    hotline: '0909 123 456',
    email: 'hello@ouut.vn',
    address: '123 Nguyễn Huệ, TP. Huế, Việt Nam',
    bankName: 'Ngân hàng demo',
    bankAccount: '0123 456 789',
    bankOwner: 'O UT DAC SAN HUE',
    shippingStandard: 30000,
    shippingExpress: 45000,
    freeShippingThreshold: 500000,
    facebook: 'facebook.com/ouutdacsanhue',
    instagram: 'instagram.com/ouutdacsanhue',
    zalo: 'zalo.me/0909123456',
  };

  function clone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  function safeParse(raw, fallback) {
    try {
      const value = JSON.parse(raw);
      return value ?? fallback;
    } catch (_) {
      return fallback;
    }
  }

  function read(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      return raw ? safeParse(raw, clone(fallback)) : clone(fallback);
    } catch (_) {
      return clone(fallback);
    }
  }

  function write(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (_) {
      return false;
    }
  }

  function ensureSeeded() {
    try {
      if (!localStorage.getItem(keys.products)) write(keys.products, seedProducts);
      if (!localStorage.getItem(keys.posts)) write(keys.posts, seedPosts);
      if (!localStorage.getItem(keys.orders)) write(keys.orders, seedOrders);
      if (!localStorage.getItem(keys.settings)) write(keys.settings, seedSettings);
      if (!localStorage.getItem(keys.cart)) write(keys.cart, { updatedAt: Date.now(), items: [] });
      if (!localStorage.getItem(keys.favorites)) write(keys.favorites, []);
    } catch (_) { /* localStorage may be unavailable in private/file mode */ }
  }

  function currency(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(Number(value || 0));
  }

  function dateTime(value) {
    try {
      return new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value));
    } catch (_) {
      return value || '';
    }
  }

  function normalizePhone(value) {
    return String(value || '').replace(/\D/g, '').replace(/^84/, '0');
  }

  function getProducts() { return read(keys.products, seedProducts); }
  function saveProducts(products) { return write(keys.products, products); }
  function getProduct(slugOrId) {
    return getProducts().find(p => p.slug === String(slugOrId) || p.id === Number(slugOrId));
  }
  function getVariant(product, variantId) {
    if (!product) return null;
    return product.variants.find(v => v.id === variantId) || product.variants[0] || null;
  }
  function getPosts() { return read(keys.posts, seedPosts); }
  function savePosts(posts) { return write(keys.posts, posts); }
  function getPost(slugOrId) { return getPosts().find(p => p.slug === String(slugOrId) || p.id === Number(slugOrId)); }
  function getOrders() { return read(keys.orders, seedOrders); }
  function saveOrders(orders) { return write(keys.orders, orders); }
  function getSettings() { return read(keys.settings, seedSettings); }
  function saveSettings(settings) { return write(keys.settings, settings); }

  function productMinPrice(product) {
    return Math.min(...product.variants.map(v => Number(v.price || 0)));
  }

  function cartRead() {
    const cart = read(keys.cart, { updatedAt: Date.now(), items: [] });
    const maxAge = 7 * 24 * 60 * 60 * 1000;
    if (!cart.updatedAt || Date.now() - cart.updatedAt > maxAge) {
      return { updatedAt: Date.now(), items: [] };
    }
    return cart;
  }

  function cartWrite(cart) {
    cart.updatedAt = Date.now();
    write(keys.cart, cart);
    window.dispatchEvent(new CustomEvent('ouut:cart-updated', { detail: cart }));
    return cart;
  }

  function getCart() { return cartRead(); }
  function addToCart(productId, variantId, quantity = 1) {
    const product = getProduct(productId);
    const variant = getVariant(product, variantId);
    if (!product || !variant || product.status !== 'active' || variant.stock <= 0) {
      return { ok: false, reason: 'out-of-stock' };
    }
    const qty = Math.max(1, Math.min(Number(quantity || 1), variant.stock));
    const cart = cartRead();
    const existing = cart.items.find(i => i.productId === product.id && i.variantId === variant.id);
    if (existing) existing.quantity = Math.min(existing.quantity + qty, variant.stock);
    else cart.items.push({ productId: product.id, variantId: variant.id, quantity: qty });
    cartWrite(cart);
    return { ok: true, cart };
  }

  function updateCartItem(productId, variantId, quantity) {
    const cart = cartRead();
    const item = cart.items.find(i => i.productId === Number(productId) && i.variantId === variantId);
    const product = getProduct(productId);
    const variant = getVariant(product, variantId);
    if (!item || !variant) return cart;
    const qty = Math.max(0, Math.min(Number(quantity || 0), variant.stock));
    if (qty <= 0) cart.items = cart.items.filter(i => !(i.productId === Number(productId) && i.variantId === variantId));
    else item.quantity = qty;
    return cartWrite(cart);
  }

  function removeCartItem(productId, variantId) {
    const cart = cartRead();
    cart.items = cart.items.filter(i => !(i.productId === Number(productId) && i.variantId === variantId));
    return cartWrite(cart);
  }

  function clearCart() { return cartWrite({ updatedAt: Date.now(), items: [] }); }

  function cartDetails() {
    const cart = cartRead();
    const lines = cart.items.map(item => {
      const product = getProduct(item.productId);
      const variant = getVariant(product, item.variantId);
      if (!product || !variant) return null;
      const quantity = Math.max(1, Math.min(item.quantity, variant.stock || item.quantity));
      return {
        ...item,
        quantity,
        product,
        variant,
        lineTotal: variant.price * quantity,
      };
    }).filter(Boolean);
    return {
      items: lines,
      count: lines.reduce((sum, line) => sum + line.quantity, 0),
      subtotal: lines.reduce((sum, line) => sum + line.lineTotal, 0),
    };
  }

  function generateOrderCode() {
    const now = new Date();
    const y = String(now.getFullYear()).slice(-2);
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let tail = '';
    for (let i = 0; i < 4; i += 1) tail += chars[Math.floor(Math.random() * chars.length)];
    return `OU-${y}${m}${d}-${tail}`;
  }

  function addOrder(payload) {
    const orders = getOrders();
    const nextId = Math.max(0, ...orders.map(o => Number(o.id || 0))) + 1;
    const order = {
      id: nextId,
      code: payload.code || generateOrderCode(),
      customer: payload.customer,
      phone: normalizePhone(payload.phone),
      email: payload.email || '',
      address: payload.address,
      note: payload.note || '',
      payment: payload.payment || 'cod',
      paymentStatus: payload.payment === 'bank' ? 'pending' : 'pending',
      shippingMethod: payload.shippingMethod || 'standard',
      shippingFee: Number(payload.shippingFee || 0),
      discount: Number(payload.discount || 0),
      status: 'new',
      source: 'Website',
      createdAt: new Date().toISOString(),
      items: payload.items || [],
      history: [{ status: 'new', label: 'Đã nhận đơn', at: new Date().toISOString() }],
    };
    orders.unshift(order);
    saveOrders(orders);
    return order;
  }

  const statusLabels = {
    new: 'Đơn mới',
    confirmed: 'Đã xác nhận',
    preparing: 'Đang chuẩn bị',
    shipping: 'Đang giao',
    delivered: 'Giao thành công',
    cancelled: 'Đã hủy',
  };

  function updateOrderStatus(orderId, nextStatus) {
    const orders = getOrders();
    const order = orders.find(o => o.id === Number(orderId));
    if (!order || !statusLabels[nextStatus]) return null;
    order.status = nextStatus;
    order.history = order.history || [];
    order.history.push({ status: nextStatus, label: statusLabels[nextStatus], at: new Date().toISOString() });
    saveOrders(orders);
    return order;
  }

  function orderTotal(order) {
    const subtotal = (order.items || []).reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 0), 0);
    return {
      subtotal,
      total: subtotal + Number(order.shippingFee || 0) - Number(order.discount || 0),
    };
  }

  function getFavorites() { return read(keys.favorites, []); }
  function toggleFavorite(productId) {
    const favorites = getFavorites();
    const id = Number(productId);
    const index = favorites.indexOf(id);
    if (index >= 0) favorites.splice(index, 1); else favorites.push(id);
    write(keys.favorites, favorites);
    return favorites;
  }

  function resetDemo() {
    write(keys.products, seedProducts);
    write(keys.posts, seedPosts);
    write(keys.orders, seedOrders);
    write(keys.settings, seedSettings);
    write(keys.cart, { updatedAt: Date.now(), items: [] });
    write(keys.favorites, []);
  }

  ensureSeeded();

  window.OUtData = {
    seedProducts: clone(seedProducts),
    seedPosts: clone(seedPosts),
    seedOrders: clone(seedOrders),
    seedSettings: clone(seedSettings),
    getProducts,
    saveProducts,
    getProduct,
    getVariant,
    getPosts,
    savePosts,
    getPost,
    getOrders,
    saveOrders,
    getSettings,
    saveSettings,
    productMinPrice,
    currency,
    dateTime,
    normalizePhone,
    getCart,
    cartDetails,
    addToCart,
    updateCartItem,
    removeCartItem,
    clearCart,
    addOrder,
    updateOrderStatus,
    orderTotal,
    statusLabels,
    getFavorites,
    toggleFavorite,
    resetDemo,
  };
})(window);
