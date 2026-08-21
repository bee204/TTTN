<nav class="main-nav">
	<div class="container main-nav-container">
		<a href="{{ route('dashboard') }}" class="nav-brand">Yoga & Gym Center</a>
		<ul class="nav-list">
			<li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">🏠 Trang chủ</a></li>
			<li><a href="{{ route('classes') }}" class="{{ request()->routeIs('classes') || request()->routeIs('class.detail') ? 'active' : '' }}">🧘‍♀️ Danh sách lớp học</a></li>
			<li><a href="{{ route('teachers') }}" class="{{ request()->routeIs('teachers') || request()->routeIs('teacher.detail') ? 'active' : '' }}">👨‍🏫 Giáo viên</a></li>
			<li><a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">📝 Đăng ký lớp học</a></li>
		</ul>
		@if(!request()->routeIs('teacher.login'))
		<div class="account-menu">
			@if(Auth::check())
				<button type="button" class="account-trigger" onclick="toggleDropdown()" aria-haspopup="true" aria-controls="dropdown-menu">
					👤 {{ Auth::user()->name }} <span aria-hidden="true">⌄</span>
				</button>
				<div class="dropdown-menu" id="dropdown-menu">
					<a href="{{ route('account.profile') }}">👤 Thông tin tài khoản</a>
					<a href="{{ route('registered.classes') }}">📚 Lớp đã đăng ký</a>
					<form method="POST" action="{{ route('account.logout') }}">
						@csrf
						<button type="submit">🚪 Đăng xuất</button>
					</form>
				</div>
			@else
				<a class="account-trigger guest-account" href="{{ route('account.login') }}">🔐 Đăng nhập</a>
			@endif
		</div>
		@endif
	</div>
</nav>

<style>
.main-nav {
	position: sticky;
	top: 0;
	z-index: 999;
	background: #18181a;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.16);
}

.main-nav-container {
	display: flex;
	align-items: center;
	min-height: 72px;
	gap: 28px;
}

.nav-brand {
	flex: 0 0 180px;
	color: #667eea;
	font-size: 1.2rem;
	font-weight: 700;
	line-height: 1.25;
	text-decoration: none;
}

.main-nav .nav-list {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 20px;
	flex: 1;
	margin: 0;
	padding: 0;
	list-style: none;
}

.main-nav .nav-list a {
	display: block;
	padding: 24px 4px;
	color: #f1f1f1;
	text-decoration: none;
	white-space: nowrap;
}

.main-nav .nav-list a:hover,
.main-nav .nav-list a.active {
	color: #ffc107;
}

.account-menu {
	position: relative;
	flex: 0 0 auto;
}

.account-trigger {
	display: block;
	border: 0;
	border-bottom: 3px solid transparent;
	padding: 23px 12px 20px;
	background: transparent;
	color: #f1f1f1;
	font: inherit;
	font-weight: 600;
	cursor: pointer;
	white-space: nowrap;
}

.account-trigger:hover,
.account-menu:focus-within .account-trigger {
	border-bottom-color: #ffc107;
	color: #ffc107;
}

.guest-account {
	text-decoration: none;
}

.account-menu .dropdown-menu {
	display: none;
	position: absolute;
	top: calc(100% - 4px);
	right: 0;
	min-width: 220px;
	padding: 8px 0;
	background: #25262a;
	border: 1px solid #3b3c42;
	border-radius: 6px;
	box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.account-menu:focus-within .dropdown-menu,
.account-menu .dropdown-menu[style*="display: block"] {
	display: block;
}

.account-menu .dropdown-menu a,
.account-menu .dropdown-menu button {
	display: block;
	width: 100%;
	padding: 11px 16px;
	border: 0;
	background: transparent;
	color: #f1f1f1;
	font: inherit;
	text-align: left;
	text-decoration: none;
	cursor: pointer;
}

.account-menu .dropdown-menu a:hover,
.account-menu .dropdown-menu button:hover {
	background: #34353b;
	color: #ffc107;
}

@media (max-width: 768px) {
	.main-nav-container {
		flex-wrap: wrap;
		gap: 0;
		padding: 0 14px;
	}

	.nav-brand {
		flex: 1;
		padding: 16px 0;
	}

	.main-nav .nav-list {
		order: 3;
		flex-basis: 100%;
		flex-direction: column;
		align-items: stretch;
		gap: 0;
		padding: 8px 0;
	}

	.main-nav .nav-list a {
		padding: 10px 4px;
	}

	.account-trigger {
		padding: 18px 4px;
	}
}
</style>
