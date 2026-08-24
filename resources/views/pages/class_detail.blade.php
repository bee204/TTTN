@extends('layouts.app')

@section('title', $class->name . ' - VITA Yoga Center')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/class-detail.css') }}">
@endpush

@section('content')
@php
    $availableSlots = max(0, $availableSlots);
    $registeredCount = $registeredStudents->count();
    $occupancyPercent = $class->quantity > 0
        ? min(100, (int) round($registeredCount / $class->quantity * 100))
        : 100;
    $isEnded = $class->end_date->lt(today());
    $isStarted = ! $isEnded && $class->start_date->lte(today());
@endphp

<div class="yoga-class-detail">
    <nav class="class-breadcrumb" aria-label="Điều hướng">
        <a href="{{ route('classes') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Danh sách lớp Yoga</a>
        <span aria-hidden="true">/</span>
        <span>{{ $class->name }}</span>
    </nav>

    <section class="class-detail-hero">
        <div class="class-detail-hero__main">
            <div class="class-detail-hero__topline">
                <span class="class-detail-eyebrow"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Lớp Yoga tại VITA</span>
                @if($isEnded)
                    <span class="detail-status detail-status--muted">Đã kết thúc</span>
                @elseif($isStarted)
                    <span class="detail-status detail-status--active">Đang diễn ra</span>
                @elseif($availableSlots === 0)
                    <span class="detail-status detail-status--full">Đã đủ chỗ</span>
                @else
                    <span class="detail-status detail-status--open">Đang nhận đăng ký</span>
                @endif
            </div>

            <h1>{{ $class->name }}</h1>
            <p>{{ $class->description ?: 'Một lớp Yoga được thiết kế để giúp bạn cải thiện sức khỏe, hơi thở và sự cân bằng.' }}</p>

            <div class="class-detail-hero__teacher">
                <span>{{ mb_strtoupper(mb_substr($class->teacher?->name ?? 'V', 0, 1)) }}</span>
                <div>
                    <small>Giáo viên hướng dẫn</small>
                    <strong>{{ $class->teacher?->name ?? 'Đang cập nhật' }}</strong>
                </div>
            </div>
        </div>

        <div class="class-detail-hero__mark" aria-hidden="true">
            <span><i class="fa-solid fa-spa"></i></span>
            <small>Mind · Body · Balance</small>
        </div>
    </section>

    <section class="class-essential-grid" aria-label="Thông tin chính của lớp học">
        <article class="class-essential class-essential--schedule">
            <span class="class-essential__icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span>
            <div><small>Lịch học</small><strong>{{ $class->lich_hoc }}</strong></div>
        </article>
        <article class="class-essential class-essential--time">
            <span class="class-essential__icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
            <div><small>Khung giờ</small><strong>{{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}</strong></div>
        </article>
        <article class="class-essential">
            <span class="class-essential__icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
            <div><small>Địa điểm</small><strong>{{ $class->location }}</strong></div>
        </article>
        <article class="class-essential">
            <span class="class-essential__icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>
            <div><small>Thời gian khóa học</small><strong>{{ $class->start_date->format('d/m/Y') }} – {{ $class->end_date->format('d/m/Y') }}</strong></div>
        </article>
    </section>

    <div class="class-detail-layout">
        <div class="class-detail-main">
            <section class="class-detail-panel class-about">
                <div class="class-panel-heading">
                    <span>01</span>
                    <div><small>Về lớp học</small><h2>Thông tin giới thiệu</h2></div>
                </div>
                <p class="class-about__copy">{{ $class->description ?: 'Thông tin chi tiết về nội dung lớp học đang được cập nhật.' }}</p>
            </section>

            <section class="class-detail-panel class-teacher-panel">
                <div class="class-panel-heading">
                    <span>02</span>
                    <div><small>Người đồng hành</small><h2>Giáo viên hướng dẫn</h2></div>
                </div>

                @if($class->teacher)
                    <div class="class-teacher-card">
                        <div class="class-teacher-card__avatar">{{ mb_strtoupper(mb_substr($class->teacher->name, 0, 1)) }}</div>
                        <div class="class-teacher-card__content">
                            <span>Giáo viên Yoga</span>
                            <h3>{{ $class->teacher->name }}</h3>
                            <p>{{ $class->teacher->description ?: 'Giáo viên sẽ trực tiếp hướng dẫn kỹ thuật và đồng hành cùng học viên trong khóa học.' }}</p>
                            <div class="class-teacher-card__meta">
                                <span><i class="fa-solid fa-award" aria-hidden="true"></i> {{ $class->teacher->exp_year }} năm kinh nghiệm</span>
                                <span><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $class->teacher->phone }}</span>
                            </div>
                        </div>
                        <a href="{{ route('teacher.detail', $class->teacher->id) }}" target="_blank" rel="noopener noreferrer" aria-label="Xem thông tin giáo viên {{ $class->teacher->name }} trong tab mới">
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                @else
                    <p class="class-teacher-empty">Thông tin giáo viên đang được cập nhật.</p>
                @endif
            </section>
        </div>

        <aside class="class-enrollment-card">
            <span class="class-enrollment-card__label">Học phí</span>
            <div class="class-enrollment-card__price">
                <strong>{{ number_format($class->price, 0, ',', '.') }}</strong>
                <span>đ/tháng</span>
            </div>
            <p>Học phí niêm yết cho lớp {{ $class->name }}.</p>

            <div class="class-capacity-detail">
                <div class="class-capacity-detail__head">
                    <span>Tình trạng lớp</span>
                    <strong>{{ $registeredCount }}/{{ $class->quantity }} học viên</strong>
                </div>
                <div class="class-capacity-detail__track"><span style="width: {{ $occupancyPercent }}%"></span></div>
                <small>{{ $availableSlots > 0 ? 'Còn '.$availableSlots.' chỗ trống' : 'Lớp hiện không còn chỗ trống' }}</small>
            </div>

            <div class="class-enrollment-card__notice">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span>Đăng ký sẽ được trung tâm xác nhận trước khi bạn bắt đầu khóa học.</span>
            </div>

            @if($registrationStatus === 'CONFIRMED')
                <span class="class-enrollment-action class-enrollment-action--disabled"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Bạn đã được duyệt</span>
            @elseif($registrationStatus === 'PENDING')
                <span class="class-enrollment-action class-enrollment-action--disabled"><i class="fa-regular fa-clock" aria-hidden="true"></i> Đang chờ duyệt</span>
            @elseif($isEnded)
                <span class="class-enrollment-action class-enrollment-action--disabled">Khóa học đã kết thúc</span>
            @elseif($isStarted)
                <span class="class-enrollment-action class-enrollment-action--disabled">Khóa học đã bắt đầu</span>
            @elseif($availableSlots === 0)
                <span class="class-enrollment-action class-enrollment-action--disabled">Lớp đã đủ chỗ</span>
            @else
                <a href="{{ route('register', ['class_id' => $class->id]) }}" class="class-enrollment-action">
                    Đăng ký lớp học <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            @endif

            <a href="{{ route('classes') }}" class="class-enrollment-card__back">Xem các lớp Yoga khác</a>
        </aside>
    </div>
</div>
@endsection
