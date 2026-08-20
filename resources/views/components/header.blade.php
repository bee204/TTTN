<nav class="main-nav" style="position:sticky;top:0;z-index:999;background:#18181a;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
	<div class="container" style="display:flex;align-items:center;">
		<span style="font-size:1.3rem;font-weight:700;color:#667eea;letter-spacing:0.5px;margin-right:36px;">Yoga & Gym Center</span>
		<ul class="nav-list" style="display:flex;align-items:center;gap:24px;">
			<li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">🏠 Trang chủ</a></li>
			<li><a href="{{ route('classes') }}" class="{{ request()->routeIs('classes') || request()->routeIs('class.detail') ? 'active' : '' }}">🧘‍♀️ Danh sách lớp học</a></li>
			<li><a href="{{ route('teachers') }}" class="{{ request()->routeIs('teachers') || request()->routeIs('teacher.detail') ? 'active' : '' }}">👨‍🏫 Giáo viên</a></li>
			<li><a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">📝 Đăng ký lớp học</a></li>
			<li><a href="{{ route('authors') }}" class="{{ request()->routeIs('authors') ? 'active' : '' }}">✍️ Tác giả</a></li>
			@if(Auth::check())
				@if(Auth::user()->role === 'teacher')
					<li><a href="{{ route('teacher.dashboard') }}">👨‍🏫 Teacher Panel</a></li>
				@elseif(Auth::user()->role === 'customer')
					<li><a href="{{ route('registered.classes') }}">📚 Lớp đã đăng ký</a></li>
				@endif
				<li>
					<form method="POST" action="{{ route('account.logout') }}" style="display:inline;">
						@csrf
						<button type="submit" style="background:none;border:0;color:inherit;font:inherit;cursor:pointer;padding:15px 0;">🚪 Đăng xuất</button>
					</form>
				</li>
			@else
				<li><a href="{{ route('account.login') }}">🔐 Đăng nhập</a></li>
				<li><a href="{{ route('account.register') }}">📝 Tạo tài khoản</a></li>
			@endif
		</ul>
	</div>
</nav>
