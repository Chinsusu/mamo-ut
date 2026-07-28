# Security policy

## Báo cáo lỗ hổng

Không tạo public issue cho lỗ hổng, credential bị lộ hoặc dữ liệu nhạy cảm.
Hãy dùng **Security → Report a vulnerability** của repository để gửi báo cáo
riêng tư cho chủ dự án.

Báo cáo nên có:

- thành phần và phiên bản bị ảnh hưởng;
- điều kiện tái hiện;
- mức tác động;
- bằng chứng tối thiểu đã được ẩn dữ liệu nhạy cảm;
- đề xuất khắc phục nếu có.

Không khai thác vượt quá mức cần thiết để chứng minh lỗi và không truy cập,
sao chép hoặc thay đổi dữ liệu của người khác.

## Quản lý secret

- Không commit `.env`, private key, access token hoặc mật khẩu.
- GitHub Actions chỉ đọc secret từ Environment `development`.
- SSH phải kiểm tra `known_hosts`; không dùng
  `StrictHostKeyChecking=no`.
- Khi nghi ngờ secret bị lộ, thu hồi và thay mới trước khi xóa khỏi lịch sử
  Git.

## Phiên bản được hỗ trợ

Trong giai đoạn phát triển, chỉ commit mới nhất trên `master` được hỗ trợ.
