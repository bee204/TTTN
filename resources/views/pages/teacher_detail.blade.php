@extends('layouts.app')

@section('title', $teacher->name . ' - Giáo viên Yoga tại VITA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teachers.css') }}">
@endpush

@section('content')
@php
    $hasAvatar = $teacher->avatar && file_exists(public_path('storage/' . $teacher->avatar));
    $initial = mb_strtoupper(mb_substr($teacher->name, 0, 1));
    $activeClassesCount = $teacher->classes->filter(fn ($class) => $class->end_date->gte(today()))->count();
@endphp

<div class="teacher-profile-page">
    <nav class="teacher-breadcrumb" aria-label="Điều hướng">
        <a href="{{ route('teachers') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Danh sách giáo viên</a>
        <span aria-hidden="true">/</span>
        <span>{{ $teacher->name }}</span>
    </nav>

    <section class="teacher-profile-hero">
        <div class="teacher-profile-hero__avatar">
            @if($hasAvatar)
                <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Ảnh giáo viên {{ $teacher->name }}">
            @else
                <span>{{ $initial }}</span>
            @endif
        </div>

        <div class="teacher-profile-hero__content">
            <span class="teachers-eyebrow"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Giáo viên Yoga tại VITA</span>
            <h1>{{ $teacher->name }}</h1>
            <p>{{ $teacher->description ?: 'Thông tin giới thiệu của giáo viên đang được cập nhật.' }}</p>
            <div class="teacher-profile-hero__badges">
                <span><i class="fa-solid fa-award" aria-hidden="true"></i> {{ $teacher->exp_year }} năm kinh nghiệm</span>
                <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> {{ $activeClassesCount }} lớp đang phụ trách</span>
            </div>
        </div>

        <div class="teacher-profile-hero__mark" aria-hidden="true">
            <i class="fa-solid fa-spa"></i>
            <small>Teach with purpose</small>
        </div>
    </section>

    <div class="teacher-profile-layout">
        <div class="teacher-profile-main">
            <section class="teacher-profile-panel">
                <div class="teacher-profile-heading">
                    <span>01</span>
                    <div><small>Hồ sơ chuyên môn</small><h2>Giới thiệu giáo viên</h2></div>
                </div>
                <p class="teacher-profile-description">{{ $teacher->description ?: 'Giáo viên chưa cập nhật nội dung giới thiệu.' }}</p>
            </section>

            <section class="teacher-profile-panel">
                <div class="teacher-profile-heading teacher-profile-heading--classes">
                    <span>02</span>
                    <div><small>Lịch giảng dạy</small><h2>Các lớp đang phụ trách</h2></div>
                    <strong>{{ $teacher->classes->count() }} lớp</strong>
                </div>

                <div class="teacher-class-list">
                    @forelse($teacher->classes as $class)
                        @php
                            $isEnded = $class->end_date->lt(today());
                            $isStarted = ! $isEnded && $class->start_date->lte(today());
                        @endphp
                        <article class="teacher-class-row {{ $isEnded ? 'teacher-class-row--ended' : '' }}">
                            <span class="teacher-class-row__icon"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                            <div class="teacher-class-row__content">
                                <div class="teacher-class-row__title">
                                    <h3>{{ $class->name }}</h3>
                                    @if($isEnded)
                                        <span class="is-muted">Đã kết thúc</span>
                                    @elseif($isStarted)
                                        <span class="is-active">Đang diễn ra</span>
                                    @else
                                        <span class="is-open">Sắp khai giảng</span>
                                    @endif
                                </div>
                                <div class="teacher-class-row__facts">
                                    <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $class->lich_hoc }}</span>
                                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}</span>
                                    <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $class->location }}</span>
                                </div>
                            </div>
                            <div class="teacher-class-row__action">
                                <strong>{{ number_format($class->price, 0, ',', '.') }}<small>đ/tháng</small></strong>
                                <a href="{{ route('class.detail', $class->id) }}" aria-label="Xem lớp {{ $class->name }}"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    @empty
                        <div class="teacher-classes-empty">
                            <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                            <p>Giáo viên chưa có lớp học nào trong hệ thống.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="teacher-contact-card">
            <span class="teacher-contact-card__eyebrow">Thông tin giáo viên</span>
            <h2>Liên hệ và kinh nghiệm</h2>

            <div class="teacher-contact-card__stats">
                <div><strong>{{ $teacher->exp_year }}</strong><small>Năm kinh nghiệm</small></div>
                <div><strong>{{ $teacher->classes->count() }}</strong><small>Tổng số lớp</small></div>
            </div>

            <dl class="teacher-contact-list">
                <div>
                    <dt><i class="fa-regular fa-envelope" aria-hidden="true"></i> Email</dt>
                    <dd><a href="mailto:{{ $teacher->email }}">{{ $teacher->email }}</a></dd>
                </div>
                <div>
                    <dt><i class="fa-solid fa-phone" aria-hidden="true"></i> Số điện thoại</dt>
                    <dd><a href="tel:{{ preg_replace('/\s+/', '', $teacher->phone) }}">{{ $teacher->phone }}</a></dd>
                </div>
                <div>
                    <dt><i class="fa-solid fa-award" aria-hidden="true"></i> Kinh nghiệm</dt>
                    <dd>{{ $teacher->exp_year }} năm giảng dạy</dd>
                </div>
            </dl>

            <a href="{{ route('classes') }}" class="teacher-contact-card__cta">Khám phá lớp Yoga <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('teachers') }}" class="teacher-contact-card__back">Xem các giáo viên khác</a>
        </aside>
    </div>
</div>
@endsection
