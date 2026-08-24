@extends('layouts.admin')
@section('title', $class->name.' - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-class-detail.css') }}">@endpush

@section('content')
@php
    $confirmedCount = $registrations->count();
    $availableSlots = max(0, $class->quantity - $confirmedCount);
    $occupancy = $class->quantity ? min(100, round($confirmedCount / $class->quantity * 100)) : 0;
    $isFull = $availableSlots === 0;
    $today = today();
    $phase = $today->lt($class->start_date) ? ['Sắp diễn ra','upcoming','fa-calendar-plus'] : ($today->gt($class->end_date) ? ['Đã kết thúc','ended','fa-flag-checkered'] : ['Đang diễn ra','ongoing','fa-circle-play']);
    $isTeacher = Auth::user()->role === 'teacher';
@endphp
<div class="class-detail-page {{ $isFull ? 'is-full' : '' }}">
    <header class="class-detail-header">
        <div>
            <a href="{{ $isTeacher ? route('teacher.dashboard') : route('admin.classes') }}" class="class-detail-back"><i class="fa-solid fa-arrow-left"></i> {{ $isTeacher ? 'Lớp phụ trách' : 'Danh sách lớp Yoga' }}</a>
            <span class="class-detail-eyebrow"><i class="fa-solid fa-circle"></i> Hồ sơ lớp học</span>
            <div class="class-detail-title"><h1>{{ $class->name }}</h1><span class="class-detail-phase class-detail-phase--{{ $phase[1] }}"><i class="fa-solid {{ $phase[2] }}"></i> {{ $phase[0] }}</span>@if($isFull)<span class="class-detail-full"><i class="fa-solid fa-user-lock"></i> Đã đủ chỗ</span>@endif</div>
            <p>Lớp #{{ str_pad((string)$class->id,3,'0',STR_PAD_LEFT) }} · {{ $class->start_date->format('d/m/Y') }} – {{ $class->end_date->format('d/m/Y') }}</p>
        </div>
        @unless($isTeacher)<a href="{{ route('admin.classes.edit',$class->id) }}" class="class-detail-edit"><i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa lớp</a>@endunless
    </header>

    <div class="class-detail-layout">
        <main class="class-detail-main">
            <section class="class-detail-hero">
                <div><small>Lịch học</small><strong>{{ $class->lich_hoc }}</strong><span><i class="fa-regular fa-clock"></i> {{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}</span></div>
                <div class="class-detail-hero__price"><small>Học phí</small><strong>{{ number_format($class->price,0,',','.') }}<span>đ/tháng</span></strong></div>
            </section>
            <section class="class-detail-card">
                <div class="class-detail-card__header"><span><i class="fa-solid fa-circle-info"></i></span><div><h2>Thông tin vận hành</h2><p>Giáo viên, phòng tập và thời gian tổ chức lớp.</p></div></div>
                <div class="class-detail-facts">
                    <div><span><i class="fa-solid fa-chalkboard-user"></i></span><p><small>Giáo viên</small><strong>{{ $class->teacher?->name ?? 'Chưa cập nhật' }}</strong></p></div>
                    <div><span><i class="fa-solid fa-location-dot"></i></span><p><small>Địa điểm</small><strong>{{ $class->location }}</strong></p></div>
                    <div><span><i class="fa-regular fa-calendar-check"></i></span><p><small>Ngày bắt đầu</small><strong>{{ $class->start_date->format('d/m/Y') }}</strong></p></div>
                    <div><span><i class="fa-regular fa-calendar-xmark"></i></span><p><small>Ngày kết thúc</small><strong>{{ $class->end_date->format('d/m/Y') }}</strong></p></div>
                </div>
                @if($class->description)<div class="class-detail-description"><small>Mô tả lớp</small><p>{{ $class->description }}</p></div>@endif
            </section>

            <section class="class-detail-card">
                <div class="class-detail-card__header class-student-heading"><span><i class="fa-solid fa-user-group"></i></span><div><h2>Học viên đã xác nhận</h2><p>{{ $confirmedCount }} học viên đang có trong lớp.</p></div></div>
                <div class="class-student-list">
                    @forelse($registrations as $registration)
                        <article class="class-student-row">
                            <span class="class-student-avatar">{{ mb_strtoupper(mb_substr($registration->customer?->name ?? 'H',0,1)) }}</span>
                            <div class="class-student-identity"><strong>{{ $registration->customer?->name ?? 'Không có thông tin' }}</strong><small>{{ $registration->customer?->email ?? 'Chưa có email' }} · {{ $registration->customer?->phone ?? 'Chưa có SĐT' }}</small></div>
                            <div class="class-student-date"><small>Ngày đăng ký</small><strong>{{ $registration->created_at->format('d/m/Y') }}</strong></div>
                            @unless($isTeacher)<a href="{{ route('admin.registrations.detail',$registration->id) }}" aria-label="Xem đơn đăng ký #{{ $registration->id }}"><i class="fa-solid fa-chevron-right"></i></a>@endunless
                        </article>
                    @empty
                        <div class="class-student-empty"><span><i class="fa-regular fa-user"></i></span><h3>Chưa có học viên</h3><p>Lớp chưa có đơn đăng ký nào được xác nhận.</p></div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="class-detail-sidebar">
            <section class="class-capacity-card {{ $isFull ? 'is-full' : '' }}">
                <div class="class-capacity-ring" style="--value:{{ $occupancy }}"><div><strong>{{ $occupancy }}%</strong><small>lấp đầy</small></div></div>
                <div class="class-capacity-copy"><small>Sức chứa lớp</small><h2>{{ $confirmedCount }}/{{ $class->quantity }} học viên</h2><p>{{ $isFull ? 'Lớp đã đạt sức chứa tối đa.' : 'Còn '.$availableSlots.' chỗ có thể đăng ký.' }}</p></div>
            </section>
            <section class="class-detail-actions">
                <h2>Vận hành lớp học</h2><p>Điểm danh và theo dõi phản hồi của học viên.</p>
                @if($class->start_date->isFuture())<span class="class-action-disabled"><i class="fa-regular fa-clock"></i> Chưa đến ngày điểm danh</span>@else<a href="{{ $isTeacher ? route('teacher.classes.attendance',$class->id) : route('admin.classes.attendance',$class->id) }}" class="class-detail-action class-detail-action--primary"><i class="fa-solid fa-clipboard-check"></i> Điểm danh lớp</a>@endif
                <a href="{{ $isTeacher ? route('teacher.classes.reviews',$class->id) : route('admin.classes.reviews',$class->id) }}" class="class-detail-action"><i class="fa-regular fa-star"></i> Xem đánh giá</a>
                @unless($isTeacher)<a href="{{ route('admin.classes.edit',$class->id) }}" class="class-detail-action"><i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa thông tin</a>@endunless
            </section>
        </aside>
    </div>
</div>
@endsection
