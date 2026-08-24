@extends('layouts.app')

@php($isTeacherPortal = ($portal ?? request('portal')) === 'teacher')

@section('title', $isTeacherPortal ? 'Đăng nhập giáo viên - VITA' : 'Đăng nhập tài khoản')
@section('body-class', $isTeacherPortal ? 'teacher-login-body' : 'auth-page-body')

@if($isTeacherPortal)
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/teacher-login.css') }}">
    @endpush
@endif

@section('content')
@if($isTeacherPortal)
    <section class="teacher-login-page">
        <a href="{{ route('dashboard') }}" class="teacher-login-return"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Về website VITA</a>
        <div class="teacher-login-shell">
            <aside class="teacher-login-brief">
                <div class="teacher-login-brand"><span><i class="fa-solid fa-spa" aria-hidden="true"></i></span><p><strong>VITA</strong><small>Teacher Portal</small></p></div>
                <div class="teacher-login-message">
                    <span>Cổng vận hành lớp học</span>
                    <h1>Mỗi buổi dạy,<br>một hành trình.</h1>
                    <p>Không gian dành riêng cho giáo viên theo dõi lớp, học viên và chất lượng từng buổi Yoga.</p>
                </div>
                <div class="teacher-login-capabilities">
                    <div><span><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></span><p><strong>Điểm danh nhanh</strong><small>Cập nhật tình trạng học viên theo buổi</small></p></div>
                    <div><span><i class="fa-regular fa-star" aria-hidden="true"></i></span><p><strong>Theo dõi phản hồi</strong><small>Nắm bắt đánh giá của từng lớp</small></p></div>
                </div>
            </aside>

            <div class="teacher-login-panel">
                <div class="teacher-login-heading">
                    <span><i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i></span>
                    <div><small>Khu vực dành cho giáo viên</small><h2>Đăng nhập cổng giảng dạy</h2><p>Sử dụng tài khoản được quản trị viên VITA cấp.</p></div>
                </div>

                @if ($errors->any())
                    <div class="teacher-login-alert" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>{{ $errors->first() }}</span></div>
                @endif

                <form method="POST" action="{{ route('teacher.login.submit') }}" class="teacher-login-form">
                    @csrf
                    <input type="hidden" name="portal" value="teacher">
                    <div class="teacher-login-field">
                        <label for="teacherEmail">Email giáo viên</label>
                        <div><i class="fa-regular fa-envelope" aria-hidden="true"></i><input type="email" id="teacherEmail" name="email" required autocomplete="email" placeholder="giaovien@vita.vn" value="{{ old('email') }}" autofocus></div>
                    </div>
                    <div class="teacher-login-field">
                        <label for="teacherPassword">Mật khẩu</label>
                        <div><i class="fa-solid fa-lock" aria-hidden="true"></i><input type="password" id="teacherPassword" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu"><button type="button" class="teacher-password-toggle" aria-label="Hiện mật khẩu" aria-pressed="false" onclick="togglePassword(this, 'teacherPassword')"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>
                    </div>
                    <button type="submit" class="teacher-login-submit"><span>Vào không gian giảng dạy</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                </form>

                <div class="teacher-login-support"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p><strong>Chưa có hoặc quên tài khoản?</strong><span>Vui lòng liên hệ quản trị viên trung tâm để được cấp lại thông tin.</span></p></div>
            </div>
        </div>
    </section>
@else
    <section class="auth-page">
        <div class="auth-shell">
            <aside class="auth-intro">
                <div class="auth-intro__brand"><span><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>VITA</div>
                <div class="auth-intro__content">
                    <p class="auth-intro__eyebrow">Chào mừng trở lại</p>
                    <h1>Tiếp tục hành trình sống khỏe của bạn.</h1>
                    <p>Theo dõi lớp học, lịch tập và tiến độ của bạn trong một không gian duy nhất.</p>
                </div>
                <div class="auth-intro__note"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i><span><strong>An toàn & riêng tư</strong><small>Thông tin tài khoản của bạn luôn được bảo vệ.</small></span></div>
            </aside>

            <div class="auth-form-panel">
                <div class="auth-form-heading"><span class="auth-form-icon"><i class="fa-solid fa-user" aria-hidden="true"></i></span><div><p>Tài khoản thành viên</p><h2>Đăng nhập</h2></div></div>
                @if ($errors->any())
                    <div class="auth-alert" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>{{ $errors->first() }}</span></div>
                @endif
                <form id="loginForm" method="POST" action="{{ route('account.login.submit') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                    <div class="auth-field"><label for="email">Email</label><div class="auth-input-wrap"><i class="fa-regular fa-envelope" aria-hidden="true"></i><input type="email" id="email" name="email" required autocomplete="email" placeholder="ban@example.com" value="{{ old('email') }}" autofocus></div></div>
                    <div class="auth-field"><label for="password">Mật khẩu</label><div class="auth-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu"><button type="button" class="password-toggle" aria-label="Hiện mật khẩu" aria-pressed="false" onclick="togglePassword(this, 'password')"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div></div>
                    <button type="submit" class="auth-submit"><span>Đăng nhập</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                </form>
                <p class="auth-register-link">Chưa có tài khoản? <a href="{{ route('account.register', ['redirect' => request('redirect')]) }}">Tạo tài khoản mới</a></p>
            </div>
        </div>
    </section>
@endif
@endsection

@push('scripts')
<script>
function togglePassword(button, inputId) {
    const input = document.getElementById(inputId);
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    button.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
    button.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
    button.querySelector('i').className = isVisible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}
</script>
@endpush
