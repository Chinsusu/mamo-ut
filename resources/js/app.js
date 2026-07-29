const formatVnd = (amount) => new Intl.NumberFormat('vi-VN').format(Number(amount)) + ' đ';

const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (mobileMenuButton instanceof HTMLButtonElement && mobileMenu instanceof HTMLElement) {
    mobileMenuButton.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('is-open');
        mobileMenuButton.setAttribute('aria-expanded', String(isOpen));
        mobileMenu.setAttribute('aria-hidden', String(! isOpen));
        document.body.classList.toggle('menu-open', isOpen);
    });
}

const galleryMain = document.querySelector('[data-gallery-main]');
const galleryButtons = document.querySelectorAll('[data-gallery-thumb]');

if (galleryMain instanceof HTMLImageElement) {
    galleryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!(button instanceof HTMLElement)) return;
            galleryMain.src = button.dataset.galleryUrl ?? galleryMain.src;
            galleryMain.alt = button.dataset.galleryAlt ?? galleryMain.alt;
            galleryButtons.forEach((item) => item.classList.toggle('is-active', item === button));
        });
    });
}

const productForm = document.querySelector('[data-product-add-form]');

if (productForm instanceof HTMLFormElement) {
    const variantButtons = productForm.querySelectorAll('[data-product-variant]');
    const quantityInput = productForm.querySelector('[data-quantity-input]');
    const quantityMinus = productForm.querySelector('[data-quantity-minus]');
    const quantityPlus = productForm.querySelector('[data-quantity-plus]');
    const price = document.querySelector('[data-product-price]');
    const stock = document.querySelector('[data-product-stock]');
    const sku = document.querySelector('[data-product-sku]');
    const quantityLimit = document.querySelector('[data-quantity-limit]');
    const addButton = productForm.querySelector('[data-add-to-cart]');
    let selected = productForm.querySelector('.is-active[data-product-variant]');

    const refreshProduct = () => {
        if (!(selected instanceof HTMLElement) || !(quantityInput instanceof HTMLInputElement)) return;
        const unitPrice = Number(selected.dataset.price ?? 0);
        const max = Math.max(0, Number(selected.dataset.stock ?? 0));
        const quantity = Math.max(1, Math.min(max || 1, Number(quantityInput.value || 1)));
        quantityInput.value = String(quantity);
        quantityInput.max = String(Math.min(99, max));
        productForm.action = selected.dataset.cartUrl ?? productForm.action;

        if (price instanceof HTMLElement) price.textContent = formatVnd(unitPrice);
        if (sku instanceof HTMLElement) sku.textContent = selected.dataset.sku ?? '';
        if (quantityLimit instanceof HTMLElement) quantityLimit.textContent = `Tối đa ${max}`;
        if (stock instanceof HTMLElement) {
            stock.textContent = max > 0 ? `Còn ${max} sản phẩm` : 'Tạm hết hàng';
            stock.classList.toggle('stock-label--out', max <= 0);
        }
        if (addButton instanceof HTMLButtonElement) addButton.disabled = max <= 0;
    };

    variantButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!(button instanceof HTMLElement) || button.hasAttribute('disabled')) return;
            selected = button;
            variantButtons.forEach((item) => item.classList.toggle('is-active', item === button));
            if (quantityInput instanceof HTMLInputElement) quantityInput.value = '1';
            refreshProduct();
        });
    });

    quantityMinus?.addEventListener('click', () => {
        if (quantityInput instanceof HTMLInputElement) {
            quantityInput.value = String(Math.max(1, Number(quantityInput.value || 1) - 1));
            refreshProduct();
        }
    });
    quantityPlus?.addEventListener('click', () => {
        if (quantityInput instanceof HTMLInputElement) {
            quantityInput.value = String(Number(quantityInput.value || 1) + 1);
            refreshProduct();
        }
    });
    quantityInput?.addEventListener('change', refreshProduct);
    refreshProduct();
}

const checkoutForm = document.querySelector('[data-checkout-form]');

if (checkoutForm instanceof HTMLFormElement) {
    const provinceInput = checkoutForm.querySelector('[data-shipping-province]');
    const shippingFee = checkoutForm.querySelector('[data-shipping-fee]');
    const orderTotal = checkoutForm.querySelector('[data-order-total]');
    const quoteStatus = checkoutForm.querySelector('[data-quote-status]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const subtotalText = orderTotal?.textContent?.match(/[\d.]+/)?.[0]?.replaceAll('.', '') ?? '0';
    const subtotal = Number(subtotalText);
    let quoteTimer;

    const fetchQuote = async () => {
        if (!(provinceInput instanceof HTMLInputElement) || !csrfToken) return;
        const province = provinceInput.value.trim();

        if (province === '') {
            if (shippingFee instanceof HTMLElement) shippingFee.textContent = 'Nhập tỉnh/thành';
            if (orderTotal instanceof HTMLElement) orderTotal.textContent = `${formatVnd(subtotal)} + phí giao`;
            if (quoteStatus instanceof HTMLElement) quoteStatus.textContent = '';
            return;
        }

        if (quoteStatus instanceof HTMLElement) quoteStatus.textContent = 'Đang tính phí giao hàng...';

        try {
            const response = await fetch(checkoutForm.dataset.quoteUrl ?? '', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ shipping_province: province }),
            });
            if (!response.ok) throw new Error('Quote failed');
            const quote = await response.json();
            if (shippingFee instanceof HTMLElement) shippingFee.textContent = formatVnd(quote.shipping_fee_vnd);
            if (orderTotal instanceof HTMLElement) orderTotal.textContent = formatVnd(quote.total_vnd);
            if (quoteStatus instanceof HTMLElement) quoteStatus.textContent = 'Phí giao đã được cập nhật.';
        } catch {
            if (quoteStatus instanceof HTMLElement) quoteStatus.textContent = 'Chưa thể tính phí giao. Hãy kiểm tra lại tỉnh/thành.';
        }
    };

    provinceInput?.addEventListener('input', () => {
        window.clearTimeout(quoteTimer);
        quoteTimer = window.setTimeout(fetchQuote, 350);
    });
    if (provinceInput instanceof HTMLInputElement && provinceInput.value.trim() !== '') fetchQuote();
}