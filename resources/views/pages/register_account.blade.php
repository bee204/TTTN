@extends('layouts.app')

@section('title', 'Tạo tài khoản thành viên')
@section('body-class', 'auth-page-body')

@section('content')
<section class="auth-page">
    <div class="auth-shell register-auth-shell">
        <aside class="auth-intro register-auth-intro">
            <div class="auth-intro__brand">
                <span><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>
                VITA
            </div>
            <div class="auth-intro__content">
                <p class="auth-intro__eyebrow">Bắt đầu hành trình mới</p>
                <h1>Xây dựng thói quen khỏe mạnh theo cách của bạn.</h1>
                <p>Tạo tài khoản để đăng ký lớp học, theo dõi lịch tập và lưu lại tiến độ trong suốt hành trình.</p>
            </div>
            <div class="register-benefits" aria-label="Quyền lợi tài khoản">
                <span><i class="fa-solid fa-check" aria-hidden="true"></i> Quản lý lớp đã đăng ký</span>
                <span><i class="fa-solid fa-check" aria-hidden="true"></i> Theo dõi lịch và chuyên cần</span>
                <span><i class="fa-solid fa-check" aria-hidden="true"></i> Đánh giá sau khóa học</span>
            </div>
        </aside>

        <div class="auth-form-panel register-form-panel">
            <div class="auth-form-heading register-form-heading">
                <span class="auth-form-icon"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span>
                <div>
                    <p>Tài khoản thành viên</p>
                    <h2>Tạo tài khoản</h2>
                </div>
            </div>

            @if(session('info'))
                <div class="auth-alert auth-alert--info" role="status">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="auth-alert" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('account.register.submit') }}" class="auth-form register-form">
                @csrf
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                <div class="auth-field">
                    <label for="name">Họ và tên</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <input type="text" name="name" id="name" required autocomplete="name" placeholder="Nguyễn Văn An" value="{{ old('name') }}" autofocus>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        <input type="email" name="email" id="email" required autocomplete="email" placeholder="ban@example.com" value="{{ old('email') }}">
                    </div>
                </div>

                <div class="register-password-grid">
                    <div class="auth-field">
                        <label for="password">Mật khẩu</label>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input type="password" name="password" id="password" required minlength="6" autocomplete="new-password" placeholder="Tối thiểu 6 ký tự">
                            <button type="button" class="password-toggle" aria-label="Hiện mật khẩu" aria-pressed="false" onclick="toggleRegisterPassword(this, 'password')">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Nhập lại mật khẩu</label>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6" autocomplete="new-password" placeholder="Nhập lại mật khẩu">
                            <button type="button" class="password-toggle" aria-label="Hiện mật khẩu xác nhận" aria-pressed="false" onclick="toggleRegisterPassword(this, 'password_confirmation')">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <span>Tạo tài khoản</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <p class="auth-register-link">
                Đã có tài khoản?
                <a href="{{ route('account.login', ['redirect' => request('redirect')]) }}">Đăng nhập ngay</a>
            </p>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function toggleRegisterPassword(button, inputId) {
    const input = document.getElementById(inputId);
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    button.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
    button.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
    button.querySelector('i').className = isVisible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}
</script>
@endpush
