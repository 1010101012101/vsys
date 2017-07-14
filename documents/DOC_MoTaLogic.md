# MÔ TẢ LOGIC 
# DỰ ÁN QUẢN LÝ TỦ KÝ GỬI TỰ ĐỘNG - VSYS

# Mô tả nghiệp vụ

- Admin (Tin Tấn hoặc VSYS) cung cấp tài khoản cho Khách hàng và Đại lý.
- Admin (Tin Tấn hoặc VSYS) thêm và cấu hình bộ trung tâm và các thiết bị cho Đại lý của Khách hàng.
- Đại lý đã có thể bỏ hàng vào sử dụng.

# Mô tả cách thức giao tiếp giữa bộ trung tâm với web server
- Người dùng đưa thẻ vào máy đọc thẻ, bộ trung tâm xử lý để biết thẻ có hợp lệ hay không.
- Khi người dùng nhấn nút nhập hàng trên tủ, một request gửi về web server cùng với json chứa các thông tin cần thiết (người dùng, tủ, ...) cho việc nhập hàng.
- Tương tự cho khi người dùng nhấn nút lấy hàng.
- Sau khi hoàn tất tác vụ, người dùng rút thẻ ra.

- Chú ý: VSYS xử lý việc đăng nhập bằng thẻ của người dùng.

# Mô tả dữ liệu

## 1. Người dùng - User:
- Lưu trữ thông tin người dùng: admin, khách hàng, đại lý

## 2. Khách hàng - Supplier:
- Một khách hàng quản lý nhiều đại lý
- Xem được báo cáo của đại lý
- Khách hàng không sở hữu bộ trung tâm và thiết bị nào.
- Khách hàng chỉ có thẻ

## 3. Đại lý - Distributor:
- Một đại lý quản lý nhiều bộ trung tâm
- Không xem được báo cáo của khách hàng, chỉ xem được chính đại lý đó.
- Đại lý sở hữu bộ trung tâm và các thiết bị (Thẻ, Máy đọc thẻ, Máy nạp tiền, Tủ, Box)

## 4. Bộ trung tâm - IOCenter:
- Một bộ trung tâm thuộc một Đại lý
- Một bộ trung tâm có nhiều thiết bị kết nối đến.

## 5. Thiết bị - Device
- Gồm: 
  + Cabinet: Tủ             -> Tủ có nhiều ngăn
  + Box: Ngăn trong tủ      -> Ngăn thuộc 1 tủ
  + Card: Thẻ               -> Thẻ thuộc một Máy đọc thẻ
  + RFID: Máy đọc thẻ       -> Máy đọc thẻ có nhiều thẻ
  + CDM: Máy nạp tiền

- Một thiết bị thuộc một bộ trung tâm

## 6. Button_Products
- Bảng để biết được Box này đang để sản phẩm nào.

## 7. User_Cards
- Bảng để biết được User này đang sử dụng thẻ nào.

## 8. User_Card_Moneys
- Bảng lưu trữ lịch sử giao dịch của User

## 9. History_Input_Output
- Bảng lưu trữ lịch sử vào ra hàng trên ngăn của tủ

## 10. Sản phẩm - Product
- Giá sản phẩm lưu ở server chỉ là giá mặc định, giá thật sử của hàng do VSYS gửi trong mỗi lần request.

## Ghi chú












