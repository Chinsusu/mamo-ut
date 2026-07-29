# Hồ sơ baseline O Út v1.0

Bộ hồ sơ này được chép nguyên trạng từ
`O_Ut_Dac_San_Hue_Bo_Ho_So_Thiet_Ke_Ky_Thuat_v1.0.zip`.

- Ngày phát hành: 28/07/2026
- Trạng thái: BASELINE ĐÃ KHÓA
- Nội dung: 7 DOCX, 7 PDF xem nhanh và 1 ảnh tham chiếu UI
- Tổng số trang PDF: 66
- Kết quả đối chiếu: nội dung PDF phủ 99,9-100% token của DOCX tương ứng

> Bộ v1.0 là hồ sơ gốc bất biến. Baseline đang hiệu lực là
> [v1.1](../v1.1/README.md), gồm v1.0 và CR-001.

## Cấu trúc

```text
v1.0/
├── README_BAN_GIAO.txt
├── 01_TAI_LIEU_EDITABLE_DOCX/
├── 02_PDF_XEM_NHANH/
└── 03_TAI_SAN_THAM_CHIEU/
```

Không đưa ảnh `UI_Reference_O_Ut.png` vào `public/`: đây là bảng tham chiếu thiết kế,
không phải asset sản phẩm dùng ở runtime.

## Chỉ mục tài liệu

| Mã | Tài liệu | DOCX | PDF | Trang |
| --- | --- | --- | --- | ---: |
| OUT-GEN-001 | Bàn giao tổng quan dự án | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/00_Ban_giao_Tong_quan_Du_an_O_Ut.docx) | [PDF](02_PDF_XEM_NHANH/00_Ban_giao_Tong_quan_Du_an_O_Ut.pdf) | 6 |
| OUT-PRD-001 | Đặc tả sản phẩm và phạm vi | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/01_Dac_ta_San_pham_va_Pham_vi.docx) | [PDF](02_PDF_XEM_NHANH/01_Dac_ta_San_pham_va_Pham_vi.pdf) | 9 |
| OUT-UX-001 | Đặc tả UI/UX và Design System | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/02_Dac_ta_UI_UX_va_Design_System.docx) | [PDF](02_PDF_XEM_NHANH/02_Dac_ta_UI_UX_va_Design_System.pdf) | 10 |
| OUT-TECH-001 | Thiết kế kỹ thuật Laravel VPS | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/03_Thiet_ke_Ky_thuat_Laravel_VPS.docx) | [PDF](02_PDF_XEM_NHANH/03_Thiet_ke_Ky_thuat_Laravel_VPS.pdf) | 10 |
| OUT-DATA-001 | Dữ liệu, nghiệp vụ đơn hàng và route contract | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/04_Du_lieu_Nghiep_vu_Don_hang_va_Route_Contract.docx) | [PDF](02_PDF_XEM_NHANH/04_Du_lieu_Nghiep_vu_Don_hang_va_Route_Contract.pdf) | 13 |
| OUT-SEO-001 | SEO, nội dung và đo lường | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/05_SEO_Noi_dung_va_Do_luong.docx) | [PDF](02_PDF_XEM_NHANH/05_SEO_Noi_dung_va_Do_luong.pdf) | 8 |
| OUT-OPS-001 | Triển khai, vận hành, kiểm thử và nghiệm thu | [DOCX](01_TAI_LIEU_EDITABLE_DOCX/06_Trien_khai_Van_hanh_Kiem_thu_va_Nghiem_thu.docx) | [PDF](02_PDF_XEM_NHANH/06_Trien_khai_Van_hanh_Kiem_thu_va_Nghiem_thu.pdf) | 10 |

Tài sản thiết kế: [UI Reference O Út](03_TAI_SAN_THAM_CHIEU/UI_Reference_O_Ut.png).

## Thứ tự áp dụng

Đọc hồ sơ gốc theo thứ tự `00` đến `06`, sau đó áp dụng
[baseline v1.1](../v1.1/README.md). Với hành vi sản phẩm, quyết định mới hơn đã
được phê duyệt bằng change request được ưu tiên hơn nội dung v1.0.

Các tài liệu dưới đây bổ sung quy tắc thực thi cho repository:

- [Coding standards](../../CODING_STANDARDS.md)
- [Development workflow](../../DEVELOPMENT_WORKFLOW.md)
- [Development deployment](../../DEPLOYMENT_DEV.md)
- [Repository settings](../../REPOSITORY_SETTINGS.md)