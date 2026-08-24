<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Đăng nhập quản trị - VITA Yoga Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/readability.css') }}">
</head>
<body class="admin-login-page">
    <div class="admin-login-shell">
        <aside class="admin-login-intro">
            <div class="admin-login-brand">
                <span><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>
                <div><strong>VITA</strong><small>YOGA CENTER · CONTROL</small></div>
            </div>

            <div class="admin-login-intro__content">
                <span class="admin-internal-badge"><i class="fa-solid fa-lock" aria-hidden="true"></i> Khu vực nội bộ</span>
                <h1>Quản trị trung tâm,<br><span>từ một nơi.</span></h1>
                <p>Theo dõi lớp học, học viên và đơn đăng ký trong không gian vận hành dành riêng cho quản trị viên.</p>

                <div class="admin-login-modules" aria-label="Các phân hệ quản trị">
                    <div><span>01</span><p><strong>Đơn đăng ký</strong><small>Theo dõi và xét duyệt</small></p></div>
                    <div><span>02</span><p><strong>Lớp Yoga</strong><small>Lịch học và sĩ số</small></p></div>
                    <div><span>03</span><p><strong>Học viên</strong><small>Hồ sơ và trạng thái</small></p></div>
                </div>
            </div>

            <div class="admin-system-note">
                <span class="admin-system-note__pulse"></span>
                <div><strong>Cổng quản trị được bảo vệ</strong><small>Chỉ tài khoản có quyền Admin mới có thể truy cập.</small></div>
            </div>
        </aside>

        <main class="admin-login-panel">
            <div class="admin-login-mobile-brand">
                <span><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>
                <strong>VITA CONTROL</strong>
            </div>

            <div class="admin-login-form-wrap">
                <div class="admin-login-heading">
                    <span class="admin-login-heading__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                    <div><p>Secure access</p><h2>Đăng nhập quản trị</h2></div>
                </div>
                <p class="admin-login-description">Sử dụng tên đăng nhập quản trị được cấp để tiếp tục.</p>

                @if(session('success'))
                    <div class="admin-login-alert admin-login-alert--success" role="status">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="admin-login-alert admin-login-alert--error" role="alert">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form id="adminLoginForm" class="admin-login-form" method="POST" action="{{ route('admin.login.submit') }}" autocomplete="on">
                    @csrf
                    <div class="admin-login-field">
                        <label for="username">Tên đăng nhập</label>
                        <div class="admin-login-control">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input type="text" id="username" name="username" required maxlength="255" autocomplete="username" autofocus placeholder="Nhập tên đăng nhập" value="{{ old('username') }}">
                        </div>
                    </div>

                    <div class="admin-login-field">
                        <div class="admin-login-field__label">
                            <label for="adminPassword">Mật khẩu</label>
                            <span>Phân biệt chữ hoa và chữ thường</span>
                        </div>
                        <div class="admin-login-control">
                            <i class="fa-solid fa-key" aria-hidden="true"></i>
                            <input type="password" id="adminPassword" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu">
                            <button type="button" class="admin-password-toggle" id="adminPasswordToggle" aria-label="Hiện mật khẩu" aria-pressed="false">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="admin-login-submit" id="adminLoginSubmit">
                        Truy cập hệ thống <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="admin-login-security">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <p>Không chia sẻ tài khoản quản trị. Mọi thao tác trong hệ thống có thể ảnh hưởng đến dữ liệu vận hành.</p>
                </div>
            </div>

            <footer class="admin-login-footer">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trở về website VITA</a>
                <span>© {{ date('Y') }} VITA Yoga Center</span>
            </footer>
        </main>
    </div>

    <script src="{{ asset('js/admin-login.js') }}"></script>
</body>
</html>
