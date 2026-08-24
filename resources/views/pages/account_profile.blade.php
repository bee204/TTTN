@extends('layouts.app')

@section('title', 'Thông tin tài khoản')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush

@php
    $customer = $user->customer;
    $profileFields = [
        $user->name,
        $user->email,
        $customer?->phone,
        $customer?->birthday,
        $customer?->gender,
        $customer?->address,
    ];
    $completedFields = collect($profileFields)->filter(fn ($value) => filled($value))->count();
    $profileCompletion = (int) round($completedFields / count($profileFields) * 100);
	$displayName = $user->name ?: $user->email;
@endphp

@section('content')
<div class="account-page">
    <header class="profile-hero">
        <div class="profile-identity">
            <div class="profile-avatar" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
            </div>
            <div>
                <span class="profile-eyebrow">Hồ sơ thành viên</span>
                <h1>{{ $displayName }}</h1>
                <p><i class="fa-regular fa-envelope" aria-hidden="true"></i> {{ $user->email }}</p>
            </div>
        </div>
        <div class="profile-status">
            <span class="profile-status__badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Tài khoản đang hoạt động</span>
            <small>Thành viên từ {{ $user->created_at?->format('m/Y') ?? 'chưa xác định' }}</small>
        </div>
    </header>

	<div class="account-layout">
		<div class="account-main-stack">
		<section class="profile-main-card" id="profile-information">
			<div class="profile-section-heading">
				<div>
					<span>Thông tin cá nhân</span>
					<h2>Cập nhật hồ sơ</h2>
				</div>
				<span class="profile-id">ID #{{ str_pad((string) ($customer?->id ?? $user->id), 5, '0', STR_PAD_LEFT) }}</span>
			</div>

			<form method="POST" action="{{ route('account.profile.update') }}" class="profile-edit-form">
				@csrf
				@method('PUT')
				<div class="profile-form-grid">
					<div class="profile-field">
						<label for="profile_name">Họ và tên <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-regular fa-user" aria-hidden="true"></i>
							<input type="text" id="profile_name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" autocomplete="name" required>
						</div>
						@error('name', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>

					<div class="profile-field">
						<label for="profile_email">Email đăng nhập</label>
						<div class="profile-input-wrap profile-input-wrap--locked">
							<i class="fa-regular fa-envelope" aria-hidden="true"></i>
							<input type="email" id="profile_email" value="{{ $user->email }}" disabled aria-describedby="email-lock-note">
							<i class="fa-solid fa-lock profile-lock" aria-hidden="true"></i>
						</div>
						<small id="email-lock-note" class="profile-field-note">Email dùng để đăng nhập nên không thể thay đổi.</small>
					</div>

					<div class="profile-field">
						<label for="profile_phone">Số điện thoại <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-solid fa-phone" aria-hidden="true"></i>
							<input type="tel" id="profile_phone" name="phone" value="{{ old('phone', $customer?->phone) }}" maxlength="20" autocomplete="tel" placeholder="0901234567" required>
						</div>
						@error('phone', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>

					<div class="profile-field">
						<label for="profile_birthday">Ngày sinh <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-regular fa-calendar" aria-hidden="true"></i>
							<input type="date" id="profile_birthday" name="birthday" value="{{ old('birthday', $customer?->birthday?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" autocomplete="bday" required>
						</div>
						@error('birthday', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>

					<div class="profile-field">
						<label for="profile_gender">Giới tính <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-solid fa-venus-mars" aria-hidden="true"></i>
							<select id="profile_gender" name="gender" required>
								<option value="male" @selected(old('gender', $customer?->gender) === 'male')>Nam</option>
								<option value="female" @selected(old('gender', $customer?->gender) === 'female')>Nữ</option>
								<option value="other" @selected(old('gender', $customer?->gender) === 'other')>Khác</option>
							</select>
						</div>
						@error('gender', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>

					<div class="profile-field profile-field--wide">
						<label for="profile_address">Địa chỉ</label>
						<div class="profile-input-wrap">
							<i class="fa-solid fa-location-dot" aria-hidden="true"></i>
							<input type="text" id="profile_address" name="address" value="{{ old('address', $customer?->address) }}" maxlength="255" autocomplete="street-address" placeholder="Địa chỉ hiện tại của bạn">
						</div>
						@error('address', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>
				</div>

				<div class="profile-form-actions">
					<p><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Các thay đổi sẽ được đồng bộ với hồ sơ học viên.</p>
					<button type="submit"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> Lưu thay đổi</button>
				</div>
			</form>

            @if($customer?->note)
                <div class="profile-note">
                    <i class="fa-regular fa-note-sticky" aria-hidden="true"></i>
                    <div><strong>Ghi chú hồ sơ</strong><p>{{ $customer->note }}</p></div>
                </div>
            @endif
		</section>

		<section class="security-card" id="account-security">
			<div class="security-heading">
				<span class="security-heading__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
				<div><span>Bảo mật tài khoản</span><h2>Thay đổi mật khẩu</h2><p>Dùng tối thiểu 8 ký tự, bao gồm chữ và số.</p></div>
			</div>

			<form method="POST" action="{{ route('account.password.update') }}" class="password-change-form">
				@csrf
				@method('PUT')
				<div class="password-form-grid">
					<div class="profile-field profile-field--wide">
						<label for="current_password">Mật khẩu hiện tại <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-solid fa-key" aria-hidden="true"></i>
							<input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
							<button type="button" class="profile-password-toggle" onclick="toggleProfilePassword(this, 'current_password')" aria-label="Hiện mật khẩu hiện tại"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
						</div>
						@error('current_password', 'password')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>
					<div class="profile-field">
						<label for="new_password">Mật khẩu mới <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-solid fa-lock" aria-hidden="true"></i>
							<input type="password" id="new_password" name="password" minlength="8" autocomplete="new-password" required>
							<button type="button" class="profile-password-toggle" onclick="toggleProfilePassword(this, 'new_password')" aria-label="Hiện mật khẩu mới"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
						</div>
						@error('password', 'password')<small class="profile-field-error">{{ $message }}</small>@enderror
					</div>
					<div class="profile-field">
						<label for="new_password_confirmation">Xác nhận mật khẩu <span>*</span></label>
						<div class="profile-input-wrap">
							<i class="fa-solid fa-lock" aria-hidden="true"></i>
							<input type="password" id="new_password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
							<button type="button" class="profile-password-toggle" onclick="toggleProfilePassword(this, 'new_password_confirmation')" aria-label="Hiện mật khẩu xác nhận"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
						</div>
					</div>
				</div>
				<div class="security-actions"><button type="submit"><i class="fa-solid fa-shield" aria-hidden="true"></i> Cập nhật mật khẩu</button></div>
			</form>
		</section>
		</div>

        <aside class="account-sidebar">
            <section class="completion-card">
                <div class="completion-card__heading">
                    <span>Hoàn thiện hồ sơ</span>
                    <strong>{{ $profileCompletion }}%</strong>
                </div>
                <div class="completion-track" role="progressbar" aria-label="Mức độ hoàn thiện hồ sơ" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $profileCompletion }}">
                    <span style="width: {{ $profileCompletion }}%"></span>
                </div>
                <p>
                    {{ $profileCompletion === 100
                        ? 'Hồ sơ của bạn đã có đầy đủ thông tin.'
                        : 'Bổ sung thông tin còn thiếu để trung tâm hỗ trợ bạn tốt hơn.' }}
                </p>
            </section>

            <nav class="account-actions" aria-label="Thao tác tài khoản">
                <h2>Truy cập nhanh</h2>
                <a href="{{ route('registered.classes') }}">
                    <span class="account-action-icon"><i class="fa-solid fa-person-running" aria-hidden="true"></i></span>
                    <span><strong>Lớp đã đăng ký</strong><small>Xem lịch và trạng thái</small></span>
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </a>
				<a href="{{ route('classes') }}">
                    <span class="account-action-icon"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                    <span><strong>Khám phá lớp học</strong><small>Tìm lớp phù hợp với bạn</small></span>
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
				</a>
				<a href="#account-security">
					<span class="account-action-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
					<span><strong>Đổi mật khẩu</strong><small>Cập nhật bảo mật tài khoản</small></span>
					<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
				</a>
                <form method="POST" action="{{ route('account.logout') }}">
                    @csrf
                    <button type="submit">
                        <span class="account-action-icon"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i></span>
                        <span><strong>Đăng xuất</strong><small>Kết thúc phiên hiện tại</small></span>
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </form>
            </nav>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleProfilePassword(button, inputId) {
    const input = document.getElementById(inputId);
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    button.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
    button.querySelector('i').className = isVisible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}
</script>
@endpush
