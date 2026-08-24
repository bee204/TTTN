<nav class="main-nav">
	<div class="container main-nav-container">
		<a href="{{ route('dashboard') }}" class="nav-brand" aria-label="VITA Yoga Center - Trang chủ">
			<span class="nav-brand-mark"><i class="fa-solid fa-leaf" aria-hidden="true"></i></span>
			<span><strong>VITA</strong><small>Yoga Center</small></span>
		</a>
		<button type="button" class="nav-toggle" onclick="toggleMainNav()" aria-label="Mở menu điều hướng" aria-expanded="false">
			<i class="fa-solid fa-bars" aria-hidden="true"></i>
		</button>
		<ul class="nav-list">
			<li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Trang chủ</a></li>
			<li><a href="{{ route('classes') }}" class="{{ request()->routeIs('classes') || request()->routeIs('class.detail') ? 'active' : '' }}">Lớp học</a></li>
			<li><a href="{{ route('teachers') }}" class="{{ request()->routeIs('teachers') || request()->routeIs('teacher.detail') ? 'active' : '' }}">Giáo viên Yoga</a></li>
			<li><a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">Đăng ký</a></li>
		</ul>
		@if(!request()->routeIs('teacher.login'))
		<div class="account-menu">
			@if(Auth::check())
				<button type="button" class="account-trigger" onclick="toggleDropdown()" aria-haspopup="true" aria-controls="dropdown-menu" aria-expanded="false">
					<span class="account-avatar"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
					<span class="account-trigger__name">{{ Auth::user()->name }}</span>
					<i class="fa-solid fa-chevron-down account-chevron" aria-hidden="true"></i>
				</button>
				<div class="dropdown-menu" id="dropdown-menu">
					<div class="account-summary">
						<span class="account-summary__avatar"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
						<span><strong>{{ Auth::user()->name }}</strong><small>{{ Auth::user()->email }}</small></span>
					</div>
					<div class="account-menu__links">
						<a href="{{ route('account.profile') }}">
							<span class="account-menu__icon"><i class="fa-regular fa-id-card" aria-hidden="true"></i></span>
							<span><strong>Thông tin tài khoản</strong><small>Xem hồ sơ cá nhân</small></span>
						</a>
						<a href="{{ route('registered.classes') }}">
							<span class="account-menu__icon"><i class="fa-solid fa-person-running" aria-hidden="true"></i></span>
							<span><strong>Lớp đã đăng ký</strong><small>Lịch học và trạng thái</small></span>
						</a>
					</div>
					<form method="POST" action="{{ route('account.logout') }}" class="account-logout">
						@csrf
						<button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Đăng xuất</button>
					</form>
				</div>
			@else
				<button type="button" class="account-trigger guest-account" data-login-chooser-open aria-haspopup="dialog" aria-controls="loginChooserModal" aria-expanded="false"><i class="fa-regular fa-user" aria-hidden="true"></i> Đăng nhập</button>
			@endif
		</div>
		@endif
	</div>
</nav>

@guest
	@if(!request()->routeIs('teacher.login'))
	<div class="login-chooser" id="loginChooserModal" hidden aria-hidden="true">
		<div class="login-chooser__backdrop" data-login-chooser-close></div>
		<section class="login-chooser__dialog" role="dialog" aria-modal="true" aria-labelledby="loginChooserTitle" aria-describedby="loginChooserDescription">
			<button type="button" class="login-chooser__close" data-login-chooser-close aria-label="Đóng cửa sổ chọn tài khoản"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
			<div class="login-chooser__heading">
				<span><i class="fa-solid fa-fingerprint" aria-hidden="true"></i></span>
				<div><small>Chọn cổng truy cập</small><h2 id="loginChooserTitle">Bạn đăng nhập với vai trò nào?</h2><p id="loginChooserDescription">Mỗi loại tài khoản có không gian và chức năng riêng tại VITA.</p></div>
			</div>
			<div class="login-chooser__options">
				<a href="{{ route('account.login') }}" class="login-option login-option--customer">
					<span class="login-option__icon"><i class="fa-solid fa-person-running" aria-hidden="true"></i></span>
					<span class="login-option__copy"><small>Tài khoản thành viên</small><strong>Học viên Yoga</strong><span>Xem lớp đã đăng ký và quản lý hồ sơ cá nhân.</span></span>
					<i class="fa-solid fa-arrow-right login-option__arrow" aria-hidden="true"></i>
				</a>
				<a href="{{ route('teacher.login') }}" class="login-option login-option--teacher">
					<span class="login-option__icon"><i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i></span>
					<span class="login-option__copy"><small>Cổng giảng dạy</small><strong>Giáo viên Yoga</strong><span>Quản lý lớp phụ trách, điểm danh và đánh giá.</span></span>
					<i class="fa-solid fa-arrow-right login-option__arrow" aria-hidden="true"></i>
				</a>
				<a href="{{ route('admin.login') }}" class="login-option login-option--admin">
					<span class="login-option__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
					<span class="login-option__copy"><small>Control center</small><strong>Quản trị viên</strong><span>Điều hành dữ liệu và hoạt động của trung tâm.</span></span>
					<i class="fa-solid fa-arrow-right login-option__arrow" aria-hidden="true"></i>
				</a>
			</div>
			<div class="login-chooser__footer"><i class="fa-solid fa-lock" aria-hidden="true"></i> Hãy chọn đúng cổng tương ứng với tài khoản được cấp.</div>
		</section>
	</div>
	@endif
@endguest

<style>
.main-nav {
	position: sticky;
	top: 0;
	z-index: 999;
	background: rgba(16, 55, 47, .97);
	box-shadow: 0 8px 30px rgba(16, 52, 45, .14);
	backdrop-filter: blur(14px);
}

.main-nav-container {
	display: flex;
	align-items: center;
	min-height: 78px;
	gap: 30px;
}

.nav-brand {
	flex: 0 0 210px;
	display: flex;
	align-items: center;
	gap: 11px;
	color: #fff;
	font-size: 1.05rem;
	font-weight: 700;
	line-height: 1.25;
	text-decoration: none;
}

.nav-brand-mark {
	display: grid;
	place-items: center;
	width: 40px;
	height: 40px;
	border-radius: 12px;
	background: #c8e66b;
	color: #143e36;
}

.nav-brand strong,
.nav-brand small { display: block; }
.nav-brand strong { letter-spacing: .14em; }
.nav-brand small { margin-top: 1px; color: #bcd2cc; font-size: .68rem; font-weight: 500; }

.nav-toggle {
	display: none;
	width: 42px;
	height: 42px;
	border: 1px solid rgba(255,255,255,.18);
	border-radius: 10px;
	background: rgba(255,255,255,.08);
	color: #fff;
	cursor: pointer;
}

.main-nav .nav-list {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	flex: 1;
	margin: 0;
	padding: 0;
	list-style: none;
}

.main-nav .nav-list a {
	display: block;
	padding: 10px 16px;
	border: 1px solid transparent;
	border-radius: 11px;
	color: #c9dbd5;
	text-decoration: none;
	white-space: nowrap;
	font-size: .91rem;
	font-weight: 650;
	letter-spacing: .015em;
	line-height: 1.25;
	transform: none;
	transition: color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.main-nav .nav-list a:hover {
	border-color: rgba(255,255,255,.08);
	background: rgba(255,255,255,.07);
	color: #fff;
}

.main-nav .nav-list a.active {
	border-color: rgba(200,230,107,.2);
	background: rgba(200,230,107,.13);
	box-shadow: inset 0 0 0 1px rgba(200,230,107,.04);
	color: #dcf59a;
}

.main-nav .nav-list a:focus-visible,
.account-trigger:focus-visible,
.nav-toggle:focus-visible {
	outline: 3px solid rgba(200,230,107,.55);
	outline-offset: 3px;
}

.account-menu {
	position: relative;
	flex: 0 0 auto;
}

.account-trigger {
	display: flex;
	align-items: center;
	gap: 9px;
	border: 1px solid rgba(255,255,255,.12);
	border-radius: 11px;
	padding: 10px 14px;
	background: rgba(255,255,255,.06);
	color: #e2eeea;
	font: inherit;
	font-size: .88rem;
	font-weight: 650;
	cursor: pointer;
	white-space: nowrap;
}

.account-avatar {
	display: grid;
	place-items: center;
	width: 29px;
	height: 29px;
	border-radius: 9px;
	background: rgba(200,230,107,.15);
	color: #d9f58a;
}

.account-trigger__name {
	max-width: 130px;
	overflow: hidden;
	text-overflow: ellipsis;
}

.account-chevron {
	margin-left: 2px;
	font-size: .64rem;
	transition: transform .18s ease;
}

.account-trigger[aria-expanded="true"] .account-chevron {
	transform: rotate(180deg);
}

.account-trigger:hover,
.account-menu:focus-within .account-trigger {
	border-color: rgba(200,230,107,.25);
	background: rgba(200,230,107,.11);
	color: #dcf59a;
}

.guest-account {
	text-decoration: none;
}

.account-menu .dropdown-menu {
	display: none;
	position: absolute;
	top: calc(100% + 11px);
	right: 0;
	width: 300px;
	padding: 10px;
	background: #f8fcfa;
	border: 1px solid #d5e5de;
	border-radius: 16px;
	box-shadow: 0 20px 48px rgba(14, 52, 44, .20);
}

.account-menu .dropdown-menu::before {
	content: '';
	position: absolute;
	top: -6px;
	right: 24px;
	width: 11px;
	height: 11px;
	transform: rotate(45deg);
	border-top: 1px solid #d5e5de;
	border-left: 1px solid #d5e5de;
	background: #f8fcfa;
}

.account-menu .dropdown-menu[style*="display: block"] {
	display: block;
}

.account-summary {
	display: flex;
	align-items: center;
	gap: 11px;
	padding: 8px 9px 14px;
	border-bottom: 1px solid #e1ebe7;
}

.account-summary__avatar {
	display: grid;
	place-items: center;
	width: 41px;
	height: 41px;
	flex: 0 0 auto;
	border-radius: 12px;
	background: #dff0e8;
	color: #237461;
}

.account-summary span:last-child { min-width: 0; }
.account-summary strong,
.account-summary small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.account-summary strong { color: #193d34; font-size: .86rem; }
.account-summary small { margin-top: 2px; color: #768b84; font-size: .69rem; }
.account-menu__links { display: grid; gap: 3px; padding: 8px 0; }

.account-menu .dropdown-menu a {
	display: flex;
	align-items: center;
	gap: 11px;
	width: 100%;
	padding: 10px;
	border: 0;
	border-radius: 10px;
	background: transparent;
	color: #284b42;
	font: inherit;
	text-align: left;
	text-decoration: none;
	cursor: pointer;
	transform: none;
}

.account-menu__icon {
	display: grid;
	place-items: center;
	width: 34px;
	height: 34px;
	flex: 0 0 auto;
	border-radius: 9px;
	background: #e9f3ef;
	color: #397c6a;
}

.account-menu .dropdown-menu a strong,
.account-menu .dropdown-menu a small { display: block; }
.account-menu .dropdown-menu a strong { font-size: .78rem; }
.account-menu .dropdown-menu a small { margin-top: 1px; color: #7e918b; font-size: .66rem; }
.account-menu .dropdown-menu a:hover { background: #eaf4ef; color: #185c4c; }
.account-menu .dropdown-menu a:hover .account-menu__icon { background: #d9ebe3; color: #1d6958; }
.account-menu .dropdown-menu a:focus-visible,
.account-menu .account-logout button:focus-visible {
	outline: 3px solid rgba(43,128,109,.22);
	outline-offset: 1px;
}
.account-logout { padding-top: 8px; border-top: 1px solid #e1ebe7; }

.account-menu .account-logout button {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	width: 100%;
	padding: 10px;
	border: 0;
	border-radius: 10px;
	background: #fff1ee;
	color: #a34438;
	font: inherit;
	font-size: .76rem;
	font-weight: 700;
	cursor: pointer;
}

.account-menu .account-logout button:hover {
	background: #fbe4df;
	color: #8d352b;
}

@media (max-width: 768px) {
	.main-nav-container {
		flex-wrap: wrap;
		gap: 0;
		padding: 0 14px;
	}

	.nav-brand {
		flex: 1;
		padding: 12px 0;
	}

	.nav-toggle { display: block; }

	.main-nav .nav-list {
		order: 3;
		flex-basis: 100%;
		flex-direction: column;
		align-items: stretch;
		gap: 5px;
		padding: 8px 0 16px;
	}

	.main-nav .nav-list a {
		padding: 12px 14px;
		border-bottom: 0;
		border-radius: 8px;
	}

	.main-nav .nav-list a.active {
		border-color: rgba(200,230,107,.18);
		background: rgba(200,230,107,.12);
	}

	.account-trigger {
		padding: 8px 10px;
	}

	.account-trigger__name { display: none; }
	.account-menu .dropdown-menu { right: 0; width: min(300px, calc(100vw - 28px)); }
}
</style>
