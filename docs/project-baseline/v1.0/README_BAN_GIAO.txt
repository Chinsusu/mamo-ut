O ÚT ĐẶC SẢN HUẾ
BỘ HỒ SƠ THIẾT KẾ & KỸ THUẬT v1.0
Ngày phát hành: 28/07/2026
Trạng thái: BASELINE ĐÃ KHÓA

1. QUYẾT ĐỊNH NỀN TẢNG
- Một Laravel monolith duy nhất trên một VPS.
- Laravel 13, PHP 8.3, Blade, Livewire 4, Tailwind CSS 4, Alpine.js.
- Filament 5 cho trang quản trị.
- MySQL 8, database queue, database session, file cache.
- Ảnh public lưu trên VPS, tạo bản WebP/AVIF theo kích thước khi upload.
- Backup database và uploads ra ngoài VPS.
- Không dùng Cloudflare Pages, Workers hoặc D1 trong phiên bản 1.

2. PHẠM VI ĐÃ BAO GỒM
- Storefront responsive theo mẫu O Út đã chọn.
- Sản phẩm, biến thể trọng lượng, giá, tồn kho và hình ảnh.
- Giỏ hàng, guest checkout, COD/chuyển khoản và tra cứu đơn.
- Admin quản lý sản phẩm, đơn hàng, blog và cấu hình cửa hàng.
- Blog, metadata, structured data, sitemap, canonical và redirect SEO.
- Database schema, order state machine, transaction, validation và route contract.
- VPS, deploy, queue, backup/restore, monitoring, security, test và nghiệm thu.

3. CẤU TRÚC BÀN GIAO
01_TAI_LIEU_EDITABLE_DOCX/
  00_Ban_giao_Tong_quan_Du_an_O_Ut.docx
  01_Dac_ta_San_pham_va_Pham_vi.docx
  02_Dac_ta_UI_UX_va_Design_System.docx
  03_Thiet_ke_Ky_thuat_Laravel_VPS.docx
  04_Du_lieu_Nghiep_vu_Don_hang_va_Route_Contract.docx
  05_SEO_Noi_dung_va_Do_luong.docx
  06_Trien_khai_Van_hanh_Kiem_thu_va_Nghiem_thu.docx

02_PDF_XEM_NHANH/
  Bản PDF tương ứng của 7 tài liệu trên.

03_TAI_SAN_THAM_CHIEU/
  UI_Reference_O_Ut.png

4. THỨ TỰ ĐỌC VÀ THI CÔNG
1) 00 - Đọc đầu tiên để khóa quyết định và chống scope creep.
2) 01 - Khóa chức năng, user flow và tiêu chí nghiệp vụ.
3) 02 - Dựng design system, storefront, checkout và admin UX.
4) 03 - Tạo kiến trúc project, module, bảo mật và runtime VPS.
5) 04 - Tạo migration, model, transaction, state machine và route.
6) 05 - Hoàn thiện SEO kỹ thuật, nội dung và analytics.
7) 06 - Deploy staging, kiểm thử, backup restore, UAT và go-live.

5. NHỮNG THỨ CỐ Ý KHÔNG ĐƯA VÀO PHIÊN BẢN 1
- Mobile app native, loyalty, marketplace, đa chi nhánh.
- Microservice, Kubernetes, nhiều VPS, Redis cluster.
- ERP/POS/CRM phức tạp, AI chatbot, recommendation engine.
- Cổng thanh toán và API hãng vận chuyển khi chưa có nhu cầu vận hành thật.

6. KIỂM TRA CHẤT LƯỢNG HỒ SƠ
- 7 DOCX đã được render và kiểm tra trực quan toàn bộ 66 trang.
- 7 PDF đã được preflight và đối chiếu với bản DOCX.
- Không có trang vỡ bố cục, ảnh lỗi, chữ bị cắt hoặc bảng chồng lấn.
- Ảnh trong DOCX đã có mô tả thay thế; không còn lỗi accessibility mức cao.

Bộ hồ sơ này là đầu vào trực tiếp cho thiết kế chi tiết, lập trình và nghiệm thu dự án O Út. Các thay đổi ảnh hưởng database, order flow, URL SEO, quyền truy cập hoặc hạ tầng phải được ghi thành change request riêng.
