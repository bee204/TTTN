<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị - VITA Yoga Center')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/admin-shell.css') }}">
    <link rel="stylesheet" href="{{ asset('css/readability.css') }}">
</head>
<body class="admin-body @yield('body-class')">
    @auth
        <nav class="admin-nav" aria-label="Điều hướng quản trị">
            <div class="nav-container">
                <a href="{{ Auth::user()->role === 'teacher' ? route('teacher.dashboard') : route('admin.dashboard') }}" class="nav-brand">
                    <span class="nav-brand__mark"><i class="fas fa-spa" aria-hidden="true"></i></span>
                    <span><strong>VITA</strong><small>{{ Auth::user()->role === 'teacher' ? 'Cổng giáo viên' : 'Control Center' }}</small></span>
                </a>

                <button type="button" class="admin-nav-toggle" id="adminNavToggle" aria-label="Mở menu quản trị" aria-controls="adminNavMenu" aria-expanded="false">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>

                <div class="nav-menu" id="adminNavMenu">
                    @if(Auth::user()->role === 'teacher')
                        <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check" aria-hidden="true"></i> Lớp phụ trách
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-line" aria-hidden="true"></i> Tổng quan
                        </a>
                        <a href="{{ route('admin.registrations') }}" class="{{ request()->routeIs('admin.registrations*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt" aria-hidden="true"></i> Đơn đăng ký
                        </a>
                        <a href="{{ route('admin.classes') }}" class="{{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
                            <i class="fas fa-spa" aria-hidden="true"></i> Lớp Yoga
                        </a>
                        <a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
                            <i class="fas fa-users" aria-hidden="true"></i> Học viên
                        </a>
                        <a href="{{ route('admin.teachers') }}" class="{{ request()->routeIs('admin.teachers*') ? 'active' : '' }}">
                            <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i> Giáo viên
                        </a>
                    @endif
                </div>

                <div class="nav-user">
                    <div class="dropdown">
                        <button type="button" class="dropdown-toggle" id="adminAccountToggle" aria-haspopup="true" aria-controls="dropdown-menu" aria-expanded="false">
                            <span class="dropdown-toggle__avatar"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
                            <span class="dropdown-toggle__identity"><small>{{ Auth::user()->role === 'teacher' ? 'Giáo viên' : 'Quản trị viên' }}</small><strong>{{ Auth::user()->name ?: Auth::user()->user_name }}</strong></span>
                            <i class="fa-solid fa-chevron-down dropdown-toggle__chevron" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu" id="dropdown-menu">
                            @if(Auth::user()->role !== 'teacher')
                                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Xem website</a>
                            @endif
                            <form method="POST" action="{{ Auth::user()->role === 'admin' ? route('admin.logout') : route('account.logout') }}">
                                @csrf
                                <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <ul>
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <div class="admin-confirm-modal" id="adminDeleteModal" hidden aria-hidden="true">
        <div class="admin-confirm-modal__backdrop" data-delete-modal-close></div>
        <section class="admin-confirm-modal__dialog" role="alertdialog" aria-modal="true" aria-labelledby="adminDeleteModalTitle" aria-describedby="adminDeleteModalDescription">
            <span class="admin-confirm-modal__icon" aria-hidden="true"><i class="fa-regular fa-trash-can"></i></span>
            <div class="admin-confirm-modal__content">
                <span class="admin-confirm-modal__eyebrow">Thao tác cần xác nhận</span>
                <h2 id="adminDeleteModalTitle">Xác nhận xóa</h2>
                <p id="adminDeleteModalDescription">Bạn có chắc chắn muốn xóa dữ liệu này?</p>
                <small><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Hành động này không thể hoàn tác.</small>
            </div>
            <div class="admin-confirm-modal__actions">
                <button type="button" class="admin-confirm-modal__cancel" data-delete-modal-close>Giữ lại</button>
                <button type="button" class="admin-confirm-modal__confirm" id="adminDeleteConfirmButton"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Xóa dữ liệu</button>
            </div>
        </section>
    </div>

    @stack('scripts')
    <script src="{{ asset('js/admin-shell.js') }}"></script>
</body>
</html>
