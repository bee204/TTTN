<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VITA Yoga Center')</title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/classes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/team.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @stack('styles')
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/readability.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login-chooser.css') }}">
</head>
<body class="@yield('body-class')">
    @include('components.header')

    <main>
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    @include('components.footer')

    <!-- JavaScript -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('js/search.js') }}"></script>
    <script src="{{ asset('js/login-chooser.js') }}"></script>
    @stack('scripts')
    
    <script>
    function toggleMainNav() {
        var navList = document.querySelector('.main-nav .nav-list');
        var navToggle = document.querySelector('.main-nav .nav-toggle');
        var isOpen = navList.style.display !== 'none';
        navList.style.display = isOpen ? 'none' : 'flex';
        navToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    }
    function handleMainNavResize() {
        var navToggle = document.querySelector('.main-nav .nav-toggle');
        var navList = document.querySelector('.main-nav .nav-list');
        if(window.innerWidth <= 768) {
            navToggle.style.display = 'block';
            navList.style.display = 'none';
            navToggle.setAttribute('aria-expanded', 'false');
        } else {
            navToggle.style.display = 'none';
            navList.style.display = 'flex';
            navToggle.setAttribute('aria-expanded', 'false');
        }
    }
    window.addEventListener('resize', handleMainNavResize);
    document.addEventListener('DOMContentLoaded', handleMainNavResize);

    // Dropdown logic
    function toggleDropdown() {
        var menu = document.getElementById('dropdown-menu');
        var trigger = document.querySelector('.account-trigger[aria-controls="dropdown-menu"]');
        var isOpen = menu.style.display === 'block';
        menu.style.display = isOpen ? 'none' : 'block';
        if(trigger) trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    }
    document.addEventListener('click', function(e) {
        var dropdown = document.querySelector('.account-menu');
        var menu = document.getElementById('dropdown-menu');
        if(menu && dropdown && !dropdown.contains(e.target)) {
            if(menu) menu.style.display = 'none';
            var trigger = document.querySelector('.account-trigger[aria-controls="dropdown-menu"]');
            if(trigger) trigger.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', function(e) {
        if(e.key !== 'Escape') return;
        var menu = document.getElementById('dropdown-menu');
        var trigger = document.querySelector('.account-trigger[aria-controls="dropdown-menu"]');
        if(menu && menu.style.display === 'block') {
            menu.style.display = 'none';
            if(trigger) {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            }
        }
    });
    </script>
    
    <style>
    .main-nav .container {
        padding: 0 10px;
        position: relative;
    }
    .main-nav .nav-list {
        transition: all 0.3s;
    }
    @media (max-width: 768px) {
        .main-nav .nav-list {
            flex-direction: column;
            gap: 0;
            background: #10372f;
            box-shadow: 0 16px 30px rgba(10,38,32,.2);
            border-radius: 0 0 14px 14px;
            position: absolute;
            top: 66px;
            left: 10px;
            right: 10px;
            z-index: 100;
            padding: 10px 0;
        }
        .main-nav .nav-list li {
            margin: 1px 0;
        }
        .main-nav .nav-toggle {
            display: block !important;
        }
    }
    
    .alert {
        padding: 15px;
        margin: 20px 0;
        border-radius: 10px;
        font-weight: 500;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
</body>
</html>
