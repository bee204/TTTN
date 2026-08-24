@extends('layouts.admin')

@section('title', 'Lớp phụ trách - VITA Teacher Portal')
@section('body-class', 'teacher-dashboard-body')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teacher-dashboard.css') }}">
@endpush

@section('content')
<div class="teacher-workspace">
    <header class="teacher-workspace__hero">
        <div class="teacher-workspace__welcome">
            <span class="teacher-workspace__eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Teacher workspace</span>
            <h1>Chào {{ $teacher->name }},</h1>
            <p>Mọi thông tin cần thiết để chuẩn bị và vận hành các lớp Yoga bạn phụ trách.</p>
        </div>
        <div class="teacher-workspace__today">
            <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>
            <div><small>Hôm nay</small><strong>{{ now()->format('d/m/Y') }}</strong></div>
        </div>
    </header>

    <section class="teacher-metrics" aria-label="Tổng quan lớp phụ trách">
        <article><span><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><div><small>Tổng lớp phụ trách</small><strong>{{ number_format($stats['total']) }}</strong></div></article>
        <article class="is-active"><span><i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i></span><div><small>Đang diễn ra</small><strong>{{ number_format($stats['active']) }}</strong></div></article>
        <article class="is-upcoming"><span><i class="fa-regular fa-calendar-plus" aria-hidden="true"></i></span><div><small>Sắp khai giảng</small><strong>{{ number_format($stats['upcoming']) }}</strong></div></article>
        <article class="is-students"><span><i class="fa-solid fa-user-group" aria-hidden="true"></i></span><div><small>Lượt học viên</small><strong>{{ number_format($stats['students']) }}</strong></div></article>
    </section>

    <section class="teacher-class-section">
        <div class="teacher-class-section__heading">
            <div><span>Lịch giảng dạy</span><h2>Lớp Yoga của tôi</h2><p>Danh sách ưu tiên lớp đang diễn ra và lớp sắp khai giảng.</p></div>
            <span class="teacher-class-section__count">{{ $classes->count() }} lớp</span>
        </div>

        <div class="teacher-class-grid">
            @forelse($classes as $class)
                @php
                    $today = today();
                    $phase = $today->lt($class->start_date)
                        ? ['Sắp khai giảng', 'upcoming', 'fa-calendar-plus']
                        : ($today->gt($class->end_date)
                            ? ['Đã kết thúc', 'ended', 'fa-flag-checkered']
                            : ['Đang diễn ra', 'active', 'fa-circle-play']);
                    $confirmed = $class->registrations_count;
                    $available = max(0, $class->quantity - $confirmed);
                    $occupancy = $class->quantity ? min(100, round($confirmed / $class->quantity * 100)) : 0;
                @endphp
                <article class="teacher-class-card teacher-class-card--{{ $phase[1] }}">
                    <header>
                        <span class="teacher-class-card__number">#{{ str_pad((string) $class->id, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="teacher-class-card__phase"><i class="fa-solid {{ $phase[2] }}" aria-hidden="true"></i> {{ $phase[0] }}</span>
                    </header>
                    <div class="teacher-class-card__title">
                        <span><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                        <div><h3>{{ $class->name }}</h3><p>{{ $class->start_date->format('d/m/Y') }} – {{ $class->end_date->format('d/m/Y') }}</p></div>
                    </div>
                    <div class="teacher-class-card__facts">
                        <div><span><i class="fa-regular fa-calendar" aria-hidden="true"></i></span><p><small>Lịch học</small><strong>{{ $class->lich_hoc }}</strong></p></div>
                        <div><span><i class="fa-regular fa-clock" aria-hidden="true"></i></span><p><small>Khung giờ</small><strong>{{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}</strong></p></div>
                        <div><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span><p><small>Địa điểm</small><strong>{{ $class->location }}</strong></p></div>
                    </div>
                    <div class="teacher-class-card__capacity">
                        <div><span>Học viên đã xác nhận</span><strong>{{ $confirmed }}/{{ $class->quantity }}</strong></div>
                        <div class="teacher-capacity-track"><span style="width: {{ $occupancy }}%"></span></div>
                        <small>{{ $available > 0 ? 'Còn '.$available.' vị trí trong lớp' : 'Lớp đã đủ học viên' }}</small>
                    </div>
                    <footer>
                        <a href="{{ route('teacher.classes.detail', $class->id) }}" class="teacher-class-action teacher-class-action--detail"><i class="fa-regular fa-eye" aria-hidden="true"></i> Chi tiết lớp</a>
                        @if($class->start_date->isFuture())
                            <span class="teacher-class-action is-disabled"><i class="fa-regular fa-clock" aria-hidden="true"></i> Chưa thể điểm danh</span>
                        @else
                            <a href="{{ route('teacher.classes.attendance', $class->id) }}" class="teacher-class-action teacher-class-action--attendance"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Điểm danh</a>
                        @endif
                        <a href="{{ route('teacher.classes.reviews', $class->id) }}" class="teacher-class-action teacher-class-action--reviews" aria-label="Xem đánh giá lớp {{ $class->name }}"><i class="fa-regular fa-star" aria-hidden="true"></i></a>
                    </footer>
                </article>
            @empty
                <div class="teacher-class-empty">
                    <span><i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i></span>
                    <h2>Chưa có lớp được phân công</h2>
                    <p>Khi quản trị viên giao lớp, lịch giảng dạy sẽ xuất hiện tại đây.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
