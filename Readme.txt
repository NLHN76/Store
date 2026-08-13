0.1 Cài đặt những công cụ cần thiết
0.1.1 Yêu cầu hệ thống
Để triển khai và chạy hệ thống, máy tính cần đáp ứng một số yêu cầu tối thiểu
như sau:
• Hệ điều hành: Windows 10/11 hoặc tương đương.
• RAM: Tối thiểu 4 GB (khuyến nghị 8 GB trở lên).
• Dung lượng ổ cứng trống: Tối thiểu 2 GB.
• Trình duyệt web: Google Chrome, Microsoft Edge hoặc Mozilla Firefox.
• Kết nối Internet để tải mã nguồn và các thư viện cần thiết.
0.1.2 XAMPP
Đầu tiên, cài đặt phần mềm XAMPP và khởi động hai dịch vụ Apache và
MySQL. Tiếp theo, sao chép thư mục source code của hệ thống vào thư mục ht-
docs trong thư mục cài đặt XAMPP. Sau đó, truy cập phpMyAdmin để tạo cơ sở
dữ liệu mới và import tệp cơ sở dữ liệu (.sql) của hệ thống.
Tiếp tục kiểm tra và cập nhật các thông số kết nối cơ sở dữ liệu trong tệp cấu
hình của dự án (nếu cần), bao gồm tên cơ sở dữ liệu, tên người dùng, mật khẩu và
cổng kết nối.
Cuối cùng, mở trình duyệt và truy cập địa chỉ:
http://localhost/tên_thư_mục_dự_án
Nếu quá trình cài đặt thành công, hệ thống sẽ hiển thị giao diện trang chủ và
người dùng có thể sử dụng các chức năng của website.
0.1.3 Môi trường chạy chương trình
Để chỉnh sửa và quản lý mã nguồn của hệ thống, đề tài sử dụng Visual Studio
Code (VS Code). Đây là trình soạn thảo mã nguồn miễn phí do Microsoft phát
triển, hỗ trợ nhiều ngôn ngữ lập trình như PHP, HTML, CSS và JavaScript.
Ngoài ra, VS Code còn hỗ trợ nhiều tiện ích mở rộng (Extensions) như PHP
Intelephense, PHP Debug, Prettier và GitLens, giúp việc lập trình, kiểm tra lỗi và
quản lý phiên bản được thực hiện thuận tiện hơn.
0.1.4 GitHub
Để cài đặt và chạy chương trình, người dùng cần có tài khoản GitHub để truy
cập kho lưu trữ mã nguồn của dự án. Sau đó, tiến hành tải mã nguồn về máy tính
bằng cách sử dụng chức năng Download ZIP hoặc công cụ Git thông qua đường
dẫn sau:
1
https://github.com/NLHN76/Store.git
Sau khi tải xuống thành công, giải nén (nếu tải dưới dạng tệp ZIP) và mở thư
mục dự án bằng Visual Studio Code để tiếp tục quá trình cấu hình và triển khai hệ
thống.
0.1.5 Cấu hình cơ sở dữ liệu
Sau khi import cơ sở dữ liệu, tiến hành kiểm tra các thông số kết nối trong tệp
cấu hình của hệ thống. Các thông số cần khai báo bao gồm:
• Tên cơ sở dữ liệu.
• Tên tài khoản MySQL.
• Mật khẩu.
• Địa chỉ máy chủ (localhost).
• Cổng kết nối (3306 nếu sử dụng mặc định).
Sau khi hoàn tất cấu hình, lưu tệp và khởi động lại Apache cùng MySQL nếu
cần thiết.
0.1.6 Khởi động hệ thống
Sau khi hoàn thành toàn bộ các bước cài đặt, mở trình duyệt và truy cập vào địa
chỉ của dự án để kiểm tra hoạt động của hệ thống. Nếu giao diện trang chủ hiển thị
bình thường và các chức năng như đăng nhập, quản lý dữ liệu và truy xuất cơ sở dữ
liệu hoạt động ổn định thì quá trình cài đặt được xem là thành công.