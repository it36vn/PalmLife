class AppStrings {
  AppStrings(this.locale);

  final String locale;
  bool get isEn => locale == 'en';

  static AppStrings of(String locale) => AppStrings(locale);

  String get appName => isEn ? 'Palm Life' : 'Xem Chỉ Tay';
  String get login => isEn ? 'Log in' : 'Đăng nhập';
  String get register => isEn ? 'Register' : 'Đăng ký';
  String get email => 'Email';
  String get invalidEmail =>
      isEn ? 'Enter a valid email.' : 'Nhập email hợp lệ.';
  String get password => isEn ? 'Password' : 'Mật khẩu';
  String get currentPassword => isEn ? 'Current password' : 'Mật khẩu hiện tại';
  String get newPassword => isEn ? 'New password' : 'Mật khẩu mới';
  String get confirmPassword => isEn ? 'Confirm password' : 'Xác nhận mật khẩu';
  String get name => isEn ? 'Name' : 'Tên';
  String get requiredField =>
      isEn ? 'This field is required.' : 'Vui lòng nhập thông tin này.';
  String get continueText => isEn ? 'Continue' : 'Tiếp tục';
  String get logout => isEn ? 'Log out' : 'Đăng xuất';
  String get account => isEn ? 'Account' : 'Tài khoản';
  String get appNameLabel => isEn ? 'App name' : 'Tên ứng dụng';
  String get appVersion => isEn ? 'Version' : 'Phiên bản';
  String get subscription => isEn ? 'Subscription' : 'Gói dịch vụ';
  String get camera => isEn ? 'Camera' : 'Camera';
  String get gallery => isEn ? 'Photo' : 'Ảnh';
  String get analyze => isEn ? 'Analyze' : 'Xem';
  String get quotaUsed => isEn ? 'Used' : 'Đã dùng';
  String get remaining => isEn ? 'Remaining' : 'Còn lại';
  String get legalShort => isEn
      ? 'For entertainment and self-reflection only. Not medical, legal, financial, spiritual advice, or guaranteed predictions.'
      : 'Chỉ phục vụ giải trí và tự phản chiếu. Không phải lời khuyên y tế, pháp lý, tài chính, tâm linh hoặc dự đoán chắc chắn.';
  String get consent => isEn
      ? 'I agree to the terms, privacy notice, and entertainment-only disclaimer.'
      : 'Tôi đồng ý điều khoản, thông báo quyền riêng tư và tuyên bố nội dung chỉ dùng cho giải trí.';
  String get quotaExhausted => isEn
      ? 'You have used all readings in your plan. Upgrade to continue.'
      : 'Bạn đã dùng hết số lần xem trong gói. Để tiếp tục hãy nâng cấp.';
  String get upgradeNow => isEn ? 'Upgrade' : 'Nâng cấp';
  String get upgradeLifetime =>
      isEn ? 'Upgrade to Lifetime' : 'Nâng cấp gói Vĩnh viễn';
  String get upgradeLifetimeHint => isEn
      ? 'Unlock unlimited readings.'
      : 'Mở khoá số lượt xem không giới hạn.';
  String get planActivated =>
      isEn ? 'Plan activated.' : 'Đã nâng cấp gói dịch vụ.';
  String get maybeLater => isEn ? 'Later' : 'Để sau';
  String get deleteAccount => isEn ? 'Delete account' : 'Xoá tài khoản';
  String get deleteAccountTitle =>
      isEn ? 'Delete this account?' : 'Xoá tài khoản này?';
  String get deleteAccountMessage => isEn
      ? 'Enter your password to confirm. After 5 incorrect attempts, account deletion will be disabled for 30 days.'
      : 'Nhập mật khẩu để xác nhận. Nếu nhập sai 5 lần, chức năng xoá tài khoản sẽ bị vô hiệu trong 30 ngày.';
  String get confirmDelete => isEn ? 'Delete' : 'Xoá';
  String get cancel => isEn ? 'Cancel' : 'Huỷ';
  String get forgotPassword => isEn ? 'Forgot password' : 'Quên mật khẩu';
  String get enterEmailFirst => isEn
      ? 'Enter your login email first.'
      : 'Nhập email đăng nhập trước nhé.';
  String get forgotPasswordSent => isEn
      ? 'A temporary password valid for 1 minute has been sent if this email exists.'
      : 'Mật khẩu tạm thời có hiệu lực trong 1 phút đã được gửi nếu email này tồn tại.';
  String get changePassword => isEn ? 'Change password' : 'Đổi mật khẩu';
  String get changePasswordTitle =>
      isEn ? 'Set a new password' : 'Đặt mật khẩu mới';
  String get changePasswordHint => isEn
      ? 'Use the temporary password from email as your current password, then choose a stronger one.'
      : 'Dùng mật khẩu tạm trong email làm mật khẩu hiện tại, rồi chọn mật khẩu mới an toàn hơn.';
  String get loginWithTemporaryPassword => isEn
      ? 'Log in with the temporary password first'
      : 'Đăng nhập bằng mật khẩu tạm trước';
  String get loginWithTemporaryPasswordHint => isEn
      ? 'The email contains a 12-character temporary password. After logging in, open Change password again to set your new password.'
      : 'Email có mật khẩu tạm 12 ký tự. Sau khi đăng nhập, mở lại Đổi mật khẩu để đặt mật khẩu mới.';
  String get passwordChanged => isEn ? 'Password changed.' : 'Đã đổi mật khẩu.';
  String get passwordMismatch => isEn
      ? 'Confirmation does not match the new password.'
      : 'Xác nhận mật khẩu chưa khớp.';
  String get passwordMinLength =>
      isEn ? 'At least 12 characters.' : 'Mật khẩu từ 12 ký tự.';
  String get passwordUppercase =>
      isEn ? 'At least 1 uppercase letter.' : 'Ít nhất 1 ký tự viết hoa.';
  String get passwordLowercase =>
      isEn ? 'At least 1 lowercase letter.' : 'Ít nhất 1 ký tự viết thường.';
  String get passwordDigit => isEn ? 'At least 1 digit.' : 'Ít nhất 1 chữ số.';
  String get passwordSymbol =>
      isEn ? 'At least 1 special character.' : 'Ít nhất 1 ký tự đặc biệt.';
  String get passwordNoSpace =>
      isEn ? 'No spaces.' : 'Không bao gồm khoảng trắng.';
  String get save => isEn ? 'Save' : 'Lưu';
  String get language => isEn ? 'Language' : 'Ngôn ngữ';
  String get vietnamese => 'Tiếng Việt';
  String get english => 'English';
  String get lockedAccountTitle =>
      isEn ? 'Account locked' : 'Tài khoản bị khoá';
  String get lockedAccountMessage => isEn
      ? 'Your account is locked. Please contact support by email or phone.'
      : 'Tài khoản của bạn bị khoá. Vui lòng liên hệ bộ phận hỗ trợ qua email hoặc số điện thoại.';
  String get sendEmail => isEn ? 'Send email' : 'Gửi email';
  String get callSupport => isEn ? 'Call' : 'Gọi';
  String get close => isEn ? 'Close' : 'Đóng';
  String get selectPlan => isEn ? 'Choose plan' : 'Chọn gói';
  String get noAvailablePlans => isEn
      ? 'No plans are available yet. Please wait!'
      : 'Chưa có gói nào sẵn sằng. Bạn chờ nha!';
  String get analysisType => isEn ? 'Reading type' : 'Kiểu xem';
  String get combined => isEn ? 'Combined' : 'Tổng hợp';
  String get homeHeadline => isEn
      ? 'A gentle palm reading in a few taps'
      : 'Xem chỉ tay nhẹ nhàng trong vài chạm';
  String get homeSubtitle => isEn
      ? 'Upload a palm photo, choose a reading style, and receive entertainment-only reflections with clear safety boundaries.'
      : 'Chụp hoặc chọn ảnh bàn tay, chọn kiểu xem và nhận nội dung giải trí có ranh giới an toàn rõ ràng.';
  String get newReading => isEn ? 'New reading' : 'Lượt xem mới';
  String get history => isEn ? 'History' : 'Lịch sử';
  String get historyDetail => isEn ? 'Reading detail' : 'Chi tiết lịch sử';
  String get palmReading => isEn ? 'Palm reading' : 'Xem chỉ tay';
  String get notifications => isEn ? 'Notifications' : 'Thông báo';
  String get settings => isEn ? 'Settings' : 'Cài đặt';
  String get emptyHistory =>
      isEn ? 'No previous readings yet.' : 'Chưa có lịch sử xem.';
  String get emptyNotifications =>
      isEn ? 'No notifications.' : 'Chưa có thông báo.';
  String get loadMore => isEn ? 'Load more' : 'Tải thêm';
  String get markAllRead =>
      isEn ? 'Mark all as read' : 'Đánh dấu tất cả đã đọc';
  String get unread => isEn ? 'Unread' : 'Chưa đọc';
  String get deleteHistory => isEn ? 'Delete reading' : 'Xoá lượt xem';
  String get deleteHistoryTitle =>
      isEn ? 'Delete this reading?' : 'Xoá lượt xem này?';
  String get deleteHistoryMessage => isEn
      ? 'This item will be removed from your history.'
      : 'Mục này sẽ được xoá khỏi lịch sử của bạn.';
  String get nowText => isEn ? 'now' : 'bây giờ';
  String minutesAgo(int value) =>
      isEn ? '$value minutes ago' : '$value phút trước';
  String hoursAgo(int value) =>
      isEn ? '$value hours ago' : '$value tiếng trước';
  String get yesterday => isEn ? 'yesterday' : 'hôm qua';
  String daysAgo(int value) => isEn ? '$value days ago' : '$value ngày trước';
  String monthsAgo(int value) =>
      isEn ? '$value months ago' : '$value tháng trước';
  String get usePersonalInfo =>
      isEn ? 'Use my personal information' : 'Xem theo thông tin cá nhân';
  String get birthDate => isEn ? 'Birth date' : 'Ngày sinh';
  String get gender => isEn ? 'Gender' : 'Giới tính';
  String get female => isEn ? 'Female' : 'Nữ';
  String get male => isEn ? 'Male' : 'Nam';
  String get other => isEn ? 'Other' : 'Khác';
  String get cameraPermissionTitle =>
      isEn ? 'Camera permission required' : 'Cần quyền camera';
  String get cameraPermissionMessage => isEn
      ? 'Camera permission is disabled. Open device settings to enable it.'
      : 'Quyền camera đang bị tắt. Mở cài đặt thiết bị để bật quyền.';
  String get galleryPermissionTitle =>
      isEn ? 'Photo permission required' : 'Cần quyền thư viện ảnh';
  String get galleryPermissionMessage => isEn
      ? 'Photo library permission is disabled. Open device settings to enable it.'
      : 'Quyền thư viện ảnh đang bị tắt. Mở cài đặt thiết bị để bật quyền.';
  String mediaPermissionTitle(bool isCamera) =>
      isCamera ? cameraPermissionTitle : galleryPermissionTitle;
  String mediaPermissionMessage(bool isCamera) =>
      isCamera ? cameraPermissionMessage : galleryPermissionMessage;
  String get openSettings => isEn ? 'Open settings' : 'Mở cài đặt';
  String get updateProfile => isEn ? 'Update profile' : 'Cập nhật thông tin';
  String get notificationSettings =>
      isEn ? 'Notification settings' : 'Cài đặt thông báo';
  String get privacyPolicy =>
      isEn ? 'Privacy Policy' : 'Chính sách quyền riêng tư';
  String get privacyText => isEn
      ? 'We collect account data, reading requests, subscriptions, consent records, and minimal technical logs to provide and protect the service. Palm images are processed for entertainment-only readings; by default we store the result and image hash, not the original uploaded image. You can update or delete your account in the app.'
      : 'Chúng tôi thu thập dữ liệu tài khoản, yêu cầu xem, gói dịch vụ, ghi nhận đồng ý và log kỹ thuật tối thiểu để cung cấp và bảo vệ dịch vụ. Ảnh bàn tay được xử lý cho nội dung giải trí; mặc định hệ thống lưu kết quả và mã băm ảnh, không lưu ảnh gốc. Bạn có thể cập nhật hoặc xoá tài khoản trong ứng dụng.';
  String get reminders => isEn ? 'Reminders' : 'Nhắc nhở';
  String get addReminder => isEn ? 'Add reminder' : 'Thêm nhắc nhở';
  String get editReminder => isEn ? 'Edit reminder' : 'Sửa nhắc nhở';
  String get reminderDetail => isEn ? 'Reminder detail' : 'Chi tiết nhắc nhở';
  String get emptyReminders => isEn ? 'No reminders yet.' : 'Chưa có nhắc nhở.';
  String get title => isEn ? 'Title' : 'Tiêu đề';
  String get content => isEn ? 'Content' : 'Nội dung';
  String get dateTime => isEn ? 'Date and time' : 'Ngày giờ';
  String get priority => isEn ? 'Priority' : 'Mức độ ưu tiên';
  String get futureDateError => isEn
      ? 'Reminder time must be in the future.'
      : 'Thời gian nhắc nhở phải lớn hơn hiện tại.';
  String get noContent => isEn ? 'No content.' : 'Không có nội dung.';
}
