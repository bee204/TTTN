# Legacy và code không thuộc luồng demo hiện tại

Tài liệu này đánh dấu những phần còn nằm trong source nhưng **không được giao diện hiện tại sử dụng** hoặc chưa đủ an toàn để tái sử dụng. Khi review dự án, vẫn có thể ghi nhận chúng là nợ kỹ thuật, nhưng không nên kết luận rằng chúng làm hỏng luồng demo trên trình duyệt.

Cập nhật lần cuối: 24/08/2026.

## Luồng chuẩn đang được sử dụng

- Học viên đăng ký tài khoản qua `/account/register` và đăng ký lớp qua `/register`.
- Học viên đánh giá lớp qua `POST /registered-classes/{id}/review`.
- Admin tạo hồ sơ học viên tại `/admin/customers/create`, giáo viên tại `/admin/teachers/create` và đơn tại quầy tại `/admin/registrations/create`.
- Admin đăng nhập bằng `resources/views/admin/login.blade.php`.
- Học viên và giáo viên đăng nhập bằng `resources/views/pages/login_account.blade.php`.

## Blade legacy hoặc không thể truy cập từ route hiện hành

| File | Trạng thái | Thay thế hiện tại |
|---|---|---|
| `resources/views/pages/contact.blade.php` | Không có `GET /contact` hoặc link điều hướng. Form POST sẽ lỗi vì redirect tới named route `contact` không tồn tại. | Chưa có màn liên hệ trong demo. |
| `resources/views/pages/login.blade.php` | Màn admin login cũ, không có route sử dụng. | `resources/views/admin/login.blade.php` và `resources/views/pages/login_account.blade.php`. |
| `resources/views/pages/members.blade.php` | `WebController::members()` không có route. | Danh sách học viên trong `/admin/customers`. |
| `resources/views/pages/team.blade.php` | `WebController::team()` không có route. | `resources/views/pages/teachers.blade.php`. |
| `resources/views/pages/authors.blade.php` | File không tồn tại nhưng method `WebController::authors()` cũ vẫn tham chiếu tới nó; method không có route. | Không thuộc sản phẩm Yoga hiện tại. |

Các method cũ `contact`, `authors`, `team`, `members`, `loginSubmit`, `adminDashboard`, `adminClasses`, `adminTeachers` và `adminRegistrations` trong `WebController` cũng không nằm trong route hiện hành.

## API không được UI hiện tại sử dụng

### Đăng ký công khai cũ

- Khai báo `POST /api/registrations` trỏ tới `UnifiedRegistrationController` bị route `apiResource('registrations')` trong nhóm admin ghi đè.
- Route có hiệu lực hiện tại là API admin, yêu cầu `auth:sanctum` và role `admin`.
- Không có Blade hoặc JavaScript hiện tại gọi API public cũ. Đăng ký trên website dùng `POST /register`.
- Không được mở lại `UnifiedRegistrationController` thành public trước khi sửa:
  - khả năng nhận `customer_id` hoặc cập nhật hồ sơ theo phone/email mà chưa xác minh chủ sở hữu;
  - thiếu `package_months`, `discount`, `final_price` trong khi database yêu cầu;
  - `idempotency_key` chưa nằm trong `$fillable` của `Registration`;
  - kiểm tra sức chứa và thời gian lớp.

### Class review API

- `/api/class-reviews*` tồn tại và yêu cầu Sanctum nhưng không được Blade/JavaScript của demo gọi.
- Luồng web hiện hành dùng `WebController::submitClassReview()` và ràng buộc review với `customer_id` của tài khoản đăng nhập.
- API mutation chỉ cho tài khoản customer thao tác review của chính mình; `customer_id` được lấy từ tài khoản Sanctum, không lấy từ request.
- Tạo mới yêu cầu registration `CONFIRMED`, có attendance `PRESENT`/`LATE`, và nằm trong 30 ngày sau khi lớp kết thúc. Review chỉ sửa được trong 7 ngày từ lúc tạo; xóa chỉ dành cho admin.
- API vẫn là luồng riêng với UI Blade; nếu kết nối client mới, giữ đồng bộ các điều kiện này và giới hạn quyền đọc phù hợp với yêu cầu riêng tư.

### Attendance summary API

- `/api/attendance/summary` không được UI hiện tại gọi; màn giáo viên/admin sử dụng web controller.
- Endpoint cho phép role teacher nhưng chưa kiểm tra lớp có thuộc giáo viên đang đăng nhập hay không.
- Chỉ tái sử dụng sau khi áp dụng cùng ownership check như các thao tác attendance store/update.

## JavaScript legacy liên quan

- `resources/js/register.js` là bản cũ, không được import bởi `resources/js/app.js` và đang có lỗi cú pháp do dùng `package` làm tên biến.
- `public/js/admin.js` và `resources/js/admin.js` chứa logic servlet cũ, không được Blade hiện tại tải.
- Các file `authors.js`, `members.js`, `team.js`, `contact.js` không thuộc luồng giao diện hiện tại.

Không import hoặc gắn lại các file trên chỉ để “tận dụng code cũ”; cần review và sửa trước khi kích hoạt.

## Quy ước cho lần review sau

1. Kiểm tra route và script inclusion trước khi phân loại lỗi runtime.
2. Phần trong tài liệu này được báo là **legacy/unused technical debt**, không phải lỗi làm chết luồng demo hiện tại.
3. Nếu một route hoặc view được kích hoạt lại, bỏ nhãn legacy tương ứng và bổ sung test cho authorization, validation và response thành công/thất bại.
