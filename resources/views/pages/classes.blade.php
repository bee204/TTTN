@extends('layouts.app')

@section('title', 'Lớp học Yoga - VITA Yoga Center')

@section('content')
<div class="classes-page">
    <header class="classes-hero">
        <div class="classes-hero__content">
            <span class="classes-eyebrow"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Luyện tập theo cách của bạn</span>
            <h1>Tìm lớp học phù hợp<br><span>với nhịp sống của bạn.</span></h1>
            <p>Khám phá lịch học, giáo viên Yoga và số chỗ còn lại trước khi bắt đầu hành trình.</p>
        </div>
        <div class="classes-hero__summary">
            <strong>{{ number_format($classes->total()) }}</strong>
            <span>Lớp học trong hệ thống</span>
            <small>Cập nhật theo dữ liệu hiện tại</small>
        </div>
    </header>

    <section class="classes-toolbar" aria-label="Tìm kiếm lớp học">
        <div class="classes-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <label for="searchClass" class="sr-only">Tìm kiếm lớp học trong trang hiện tại</label>
            <input type="search" id="searchClass" placeholder="Tên lớp, giáo viên Yoga, địa điểm..." autocomplete="off">
            <kbd>Ctrl K</kbd>
        </div>
        <div class="classes-toolbar__meta">
            <span id="classResultCount">Đang hiển thị {{ $classes->count() }} lớp</span>
            <small>Tìm trong trang hiện tại</small>
        </div>
    </section>

    <section class="classes-grid" id="classGrid" aria-live="polite">
        @forelse($classes as $class)
            @php
                $availableSlots = $class->available_slots;
                $registeredCount = max(0, $class->quantity - $availableSlots);
                $occupancyPercent = $class->quantity > 0
                    ? min(100, (int) round($registeredCount / $class->quantity * 100))
                    : 100;
                $isEnded = $class->end_date->lt(today());
                $isStarted = ! $isEnded && $class->start_date->lte(today());
                $searchText = strtolower(implode(' ', [
                    $class->name,
                    $class->description,
                    $class->teacher?->name,
                    $class->location,
                    $class->lich_hoc,
                ]));
            @endphp

            <article class="class-card-vita class-card-vita--tone-{{ ($class->id % 3) + 1 }}" data-class-card data-search="{{ $searchText }}">
                <div class="class-card-vita__visual">
                    <span class="class-card-vita__number">{{ str_pad((string) $class->id, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="class-card-vita__symbol"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                    @if($isEnded)
                        <span class="class-state class-state--muted">Đã kết thúc</span>
                    @elseif($isStarted)
                        <span class="class-state class-state--active">Đang diễn ra</span>
                    @elseif($availableSlots <= 0)
                        <span class="class-state class-state--full">Đã đủ chỗ</span>
                    @else
                        <span class="class-state class-state--open">Đang nhận đăng ký</span>
                    @endif
                </div>

                <div class="class-card-vita__body">
                    <div class="class-card-vita__title">
                        <div>
                            <span>{{ $class->teacher?->name ?? 'Đang cập nhật giáo viên' }}</span>
                            <h2>{{ $class->name }}</h2>
                        </div>
                        <a href="{{ route('class.detail', $class->id) }}" aria-label="Xem chi tiết lớp {{ $class->name }}">
                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </a>
                    </div>

                    <p class="class-card-vita__description">{{ $class->description ?: 'Thông tin mô tả lớp học đang được cập nhật.' }}</p>

                    <p class="class-card-vita__essentials-label">Thông tin quan trọng</p>
                    <dl class="class-card-vita__schedule">
                        <div class="class-fact class-fact--schedule">
                            <dt><span><i class="fa-regular fa-calendar" aria-hidden="true"></i></span> Lịch học</dt>
                            <dd>{{ $class->lich_hoc }}</dd>
                        </div>
                        <div class="class-fact class-fact--time">
                            <dt><span><i class="fa-regular fa-clock" aria-hidden="true"></i></span> Khung giờ</dt>
                            <dd>{{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}</dd>
                        </div>
                        <div class="class-fact class-fact--location">
                            <dt><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span> Địa điểm</dt>
                            <dd>{{ $class->location }}</dd>
                        </div>
                        <div class="class-fact class-fact--duration">
                            <dt><span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span> Khóa học</dt>
                            <dd>{{ $class->start_date->format('d/m') }} – {{ $class->end_date->format('d/m/Y') }}</dd>
                        </div>
                    </dl>

                    <div class="class-capacity">
                        <div class="class-capacity__head">
                            <span>Sĩ số lớp</span>
                            <strong>{{ $registeredCount }}/{{ $class->quantity }} học viên</strong>
                        </div>
                        <div class="class-capacity__track"><span style="width: {{ $occupancyPercent }}%"></span></div>
                        <small>{{ $availableSlots > 0 ? 'Còn '.$availableSlots.' chỗ trống' : 'Lớp hiện không còn chỗ trống' }}</small>
                    </div>
                </div>

                <footer class="class-card-vita__footer">
                    <div class="class-price-vita">
                        <span class="class-price-vita__icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span>
                        <div><small>Học phí</small><strong>{{ number_format($class->price, 0, ',', '.') }}<span>đ/tháng</span></strong></div>
                    </div>
                    <div class="class-card-vita__actions">
                        <a href="{{ route('class.detail', $class->id) }}" class="class-action class-action--secondary">Chi tiết</a>
                        @if(isset($registrationStatuses[$class->id]))
                            <span class="class-action class-action--disabled">
                                @if($registrationStatuses[$class->id]['has_attendance'])
                                    Đang học
                                @elseif($registrationStatuses[$class->id]['status'] === 'CONFIRMED')
                                    Đã được duyệt
                                @else
                                    Chờ duyệt
                                @endif
                            </span>
                        @elseif($isEnded)
                            <span class="class-action class-action--disabled">Đã kết thúc</span>
                        @elseif($isStarted)
                            <span class="class-action class-action--disabled">Đã bắt đầu</span>
                        @elseif($availableSlots <= 0)
                            <span class="class-action class-action--disabled">Hết chỗ</span>
                        @else
                            <a href="{{ route('register', ['class_id' => $class->id]) }}" class="class-action class-action--primary">Đăng ký</a>
                        @endif
                    </div>
                </footer>
            </article>
        @empty
            <div class="classes-empty-state">
                <span><i class="fa-solid fa-seedling" aria-hidden="true"></i></span>
                <h2>Chưa có lớp học nào</h2>
                <p>Các lớp học mới đang được chuẩn bị. Vui lòng quay lại sau.</p>
            </div>
        @endforelse
    </section>

    <div class="classes-no-results" id="noResultsMessage" hidden>
        <span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
        <h2>Không tìm thấy lớp phù hợp</h2>
        <p>Thử tìm bằng tên lớp, giáo viên hoặc địa điểm khác.</p>
        <button type="button" id="clearClassSearch">Xóa tìm kiếm</button>
    </div>

    @if($classes->hasPages())
        <nav class="classes-pagination" id="classesPagination" aria-label="Phân trang lớp học">
            @if($classes->onFirstPage())
                <span class="is-disabled"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</span>
            @else
                <a href="{{ $classes->previousPageUrl() }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</a>
            @endif
            <span class="classes-pagination__current">Trang {{ $classes->currentPage() }} / {{ $classes->lastPage() }}</span>
            @if($classes->hasMorePages())
                <a href="{{ $classes->nextPageUrl() }}">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            @else
                <span class="is-disabled">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            @endif
        </nav>
    @endif
</div>
@endsection
