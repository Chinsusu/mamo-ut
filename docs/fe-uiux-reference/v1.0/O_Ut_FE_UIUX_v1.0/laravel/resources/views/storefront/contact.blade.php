<x-layouts.storefront title="Liên hệ O Út đặc sản Huế" page="contact">
    <section class="page-hero"><div class="container"><span class="eyebrow">O Út luôn lắng nghe</span><h1>Liên hệ và hỗ trợ đơn hàng</h1><p>Hotline, email và địa chỉ phải lấy từ StoreSettings thay vì hard-code khi tích hợp.</p></div></section>
    <section class="section"><div class="container content-grid"><div class="panel"><div class="panel__body"><h2>Thông tin liên hệ</h2><p>Hotline: {{ $settings->hotline }}</p><p>Email: {{ $settings->email }}</p><p>Địa chỉ: {{ $settings->address }}</p></div></div><livewire:storefront.contact-form /></div></section>
</x-layouts.storefront>
