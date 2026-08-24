@extends('layouts.admin')

@section('title', 'Lớp Yoga - VITA Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-classes.css') }}">
@endpush

@section('content')
@php
    $currentStatus = strtolower((string) request('status'));
    $baseFilter = array_filter(['search' => request('search')]);
    $today = today();
@endphp

<div class="classes-admin-page">
    <header class="classes-admin-header">
        <div>
            <span class="classes-admin-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Quản lý lịch tập</span>
            <h1>Danh sách lớp Yoga</h1>
            <p>Theo dõi lịch học, giáo viên, sức chứa và học phí của từng lớp.</p>
        </div>
        <a href="{{ route('admin.classes.create') }}" class="classes-create-action"><i class="fa-solid fa-plus" aria-hidden="true"></i> Tạo lớp Yoga</a>
    </header>

    <section class="classes-summary-grid" aria-label="Thống kê lớp Yoga">
        <a href="{{ route('admin.classes', $baseFilter) }}" class="classes-summary-card {{ $currentStatus === '' ? 'is-active' : '' }}">
            <span><i class="fa-solid fa-spa" aria-hidden="true"></i></span><div><small>Tất cả lớp</small><strong>{{ number_format($stats['total']) }}</strong></div>
        </a>
        <a href="{{ route('admin.classes', array_merge($baseFilter, ['status' => 'ongoing'])) }}" class="classes-summary-card classes-summary-card--ongoing {{ $currentStatus === 'ongoing' ? 'is-active' : '' }}">
            <span><i class="fa-solid fa-circle-play" aria-hidden="true"></i></span><div><small>Đang diễn ra</small><strong>{{ number_format($stats['ongoing']) }}</strong></div>
        </a>
        <a href="{{ route('admin.classes', array_merge($baseFilter, ['status' => 'upcoming'])) }}" class="classes-summary-card classes-summary-card--upcoming {{ $currentStatus === 'upcoming' ? 'is-active' : '' }}">
            <span><i class="fa-regular fa-calendar-plus" aria-hidden="true"></i></span><div><small>Sắp diễn ra</small><strong>{{ number_format($stats['upcoming']) }}</strong></div>
        </a>
        <a href="{{ route('admin.classes', array_merge($baseFilter, ['status' => 'ended'])) }}" class="classes-summary-card classes-summary-card--ended {{ $currentStatus === 'ended' ? 'is-active' : '' }}">
            <span><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i></span><div><small>Đã kết thúc</small><strong>{{ number_format($stats['ended']) }}</strong></div>
        </a>
    </section>

    <section class="classes-toolbar" aria-label="Tìm kiếm lớp Yoga">
        <form method="GET" action="{{ route('admin.classes') }}" class="classes-search-form">
            @if($currentStatus !== '')<input type="hidden" name="status" value="{{ $currentStatus }}">@endif
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <label for="classSearch" class="classes-sr-only">Tìm kiếm lớp Yoga</label>
            <input type="search" id="classSearch" name="search" value="{{ request('search') }}" placeholder="Tên lớp, giáo viên, địa điểm hoặc mô tả...">
            @if(request()->filled('search'))
                <a href="{{ route('admin.classes', array_filter(['status' => $currentStatus])) }}" aria-label="Xóa từ khóa tìm kiếm"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
            @endif
            <button type="submit">Tìm kiếm</button>
        </form>
        <div class="classes-toolbar__result"><span>{{ number_format($classes->total()) }} lớp</span><small>Trang {{ $classes->currentPage() }} / {{ max(1, $classes->lastPage()) }}</small></div>
    </section>

    <section class="classes-admin-grid" aria-label="Danh sách lớp Yoga">
        @forelse($classes as $class)
            @php
                $confirmedCount = (int) $class->registrations_count;
                $availableSlots = max(0, $class->quantity - $confirmedCount);
                $occupancy = $class->quantity > 0 ? min(100, round($confirmedCount / $class->quantity * 100)) : 0;
                $isFull = $availableSlots === 0;
                if ($today->lt($class->start_date)) {
                    $phase = ['Sắp diễn ra', 'upcoming', 'fa-calendar-plus'];
                } elseif ($today->gt($class->end_date)) {
                    $phase = ['Đã kết thúc', 'ended', 'fa-flag-checkered'];
                } else {
                    $phase = ['Đang diễn ra', 'ongoing', 'fa-circle-play'];
                }
            @endphp

            <article class="class-admin-card {{ $isFull ? 'is-full' : '' }}">
                <header class="class-admin-card__header">
                    <div class="class-admin-card__identity">
                        <span><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                        <div><small>Lớp #{{ str_pad((string) $class->id, 3, '0', STR_PAD_LEFT) }}</small><h2>{{ $class->name }}</h2></div>
                    </div>
                    <span class="class-phase class-phase--{{ $phase[1] }}"><i class="fa-solid {{ $phase[2] }}" aria-hidden="true"></i> {{ $phase[0] }}</span>
                </header>

                <div class="class-admin-card__schedule">
                    <div class="class-schedule-primary"><small>Lịch học</small><strong>{{ $class->lich_hoc }}</strong></div>
                    <div class="class-time-range"><i class="fa-regular fa-clock" aria-hidden="true"></i><strong>{{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}</strong></div>
                </div>

                <div class="class-admin-facts">
                    <div><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i><span><small>Giáo viên</small><strong>{{ $class->teacher?->name ?? 'Chưa cập nhật' }}</strong></span></div>
                    <div><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><small>Địa điểm</small><strong>{{ $class->location }}</strong></span></div>
                    <div><i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span><small>Thời gian khóa</small><strong>{{ $class->start_date->format('d/m/Y') }} – {{ $class->end_date->format('d/m/Y') }}</strong></span></div>
                </div>

                <div class="class-capacity">
                    <div class="class-capacity__heading">
                        <span><strong>{{ $confirmedCount }}/{{ $class->quantity }}</strong> học viên</span>
                        <span class="{{ $isFull ? 'is-full' : '' }}">{{ $isFull ? 'Đã đủ chỗ' : 'Còn '.$availableSlots.' chỗ' }}</span>
                    </div>
                    <div class="class-capacity__track" role="progressbar" aria-valuenow="{{ $occupancy }}" aria-valuemin="0" aria-valuemax="100" aria-label="Sức chứa lớp {{ $class->name }}"><span class="{{ $isFull ? 'is-full' : '' }}" style="width: {{ $occupancy }}%"></span></div>
                </div>

                <div class="class-admin-card__footer">
                    <div class="class-price"><small>Học phí</small><strong>{{ number_format($class->price, 0, ',', '.') }}<span>đ/tháng</span></strong></div>
                    <div class="class-card-actions">
                        <a href="{{ route('admin.classes.detail', $class->id) }}" class="class-action class-action--primary"><i class="fa-regular fa-eye" aria-hidden="true"></i> Chi tiết</a>
                        <a href="{{ route('admin.classes.edit', $class->id) }}" class="class-action" title="Chỉnh sửa lớp" aria-label="Chỉnh sửa lớp {{ $class->name }}"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></a>
                        <form method="POST" action="{{ route('admin.classes.delete', $class->id) }}" data-confirm-title="Xóa lớp Yoga?" data-confirm-message="Lớp #{{ $class->id }} – {{ $class->name }} sẽ bị xóa nếu chưa phát sinh đăng ký.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="class-action class-action--delete" title="Xóa lớp" aria-label="Xóa lớp {{ $class->name }}"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="classes-empty-state">
                <span><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                <h2>{{ request()->hasAny(['search', 'status']) ? 'Không tìm thấy lớp phù hợp' : 'Chưa có lớp Yoga nào' }}</h2>
                <p>{{ request()->hasAny(['search', 'status']) ? 'Thử thay đổi từ khóa hoặc trạng thái đang lọc.' : 'Tạo lớp Yoga đầu tiên để bắt đầu nhận đăng ký.' }}</p>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.classes') }}">Xóa toàn bộ bộ lọc</a>
                @else
                    <a href="{{ route('admin.classes.create') }}">Tạo lớp Yoga đầu tiên</a>
                @endif
            </div>
        @endforelse
    </section>

    @if($classes->hasPages())
        <nav class="classes-pagination" aria-label="Phân trang lớp Yoga">
            @if($classes->onFirstPage())<span class="is-disabled"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</span>@else<a href="{{ $classes->previousPageUrl() }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</a>@endif
            <span class="classes-pagination__current">{{ $classes->currentPage() }} / {{ $classes->lastPage() }}</span>
            @if($classes->hasMorePages())<a href="{{ $classes->nextPageUrl() }}">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>@else<span class="is-disabled">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>@endif
        </nav>
    @endif
</div>
@endsection
