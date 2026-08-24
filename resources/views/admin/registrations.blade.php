@extends('layouts.admin')

@section('title', 'Đơn đăng ký - VITA Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-registrations.css') }}">
@endpush

@section('content')
@php
    $currentStatus = strtolower((string) request('status'));
    $baseFilter = array_filter(['search' => request('search')]);
    $totalRegistrations = $stats['pending'] + $stats['confirmed'] + $stats['cancelled'];
@endphp

<div class="registration-admin-page">
    <header class="registration-admin-header">
        <div>
            <span class="registration-admin-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Quản lý vận hành</span>
            <h1>Đơn đăng ký lớp Yoga</h1>
            <p>Theo dõi, xét duyệt và cập nhật các yêu cầu đăng ký của học viên.</p>
        </div>
        <a href="{{ route('admin.registrations.create') }}" class="registration-create-action">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Tạo đơn đăng ký
        </a>
    </header>

    <section class="registration-summary-grid" aria-label="Thống kê trạng thái đăng ký">
        <a href="{{ route('admin.registrations', $baseFilter) }}" class="registration-summary-card registration-summary-card--all {{ $currentStatus === '' ? 'is-active' : '' }}">
            <span><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
            <div><small>Tất cả đơn</small><strong>{{ number_format($totalRegistrations) }}</strong></div>
        </a>
        <a href="{{ route('admin.registrations', array_merge($baseFilter, ['status' => 'pending'])) }}" class="registration-summary-card registration-summary-card--pending {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
            <span><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
            <div><small>Chờ duyệt</small><strong>{{ number_format($stats['pending']) }}</strong></div>
        </a>
        <a href="{{ route('admin.registrations', array_merge($baseFilter, ['status' => 'confirmed'])) }}" class="registration-summary-card registration-summary-card--confirmed {{ $currentStatus === 'confirmed' ? 'is-active' : '' }}">
            <span><i class="fa-regular fa-circle-check" aria-hidden="true"></i></span>
            <div><small>Đã duyệt</small><strong>{{ number_format($stats['confirmed']) }}</strong></div>
        </a>
        <a href="{{ route('admin.registrations', array_merge($baseFilter, ['status' => 'cancelled'])) }}" class="registration-summary-card registration-summary-card--cancelled {{ $currentStatus === 'cancelled' ? 'is-active' : '' }}">
            <span><i class="fa-regular fa-circle-xmark" aria-hidden="true"></i></span>
            <div><small>Đã hủy</small><strong>{{ number_format($stats['cancelled']) }}</strong></div>
        </a>
    </section>

    <section class="registration-toolbar" aria-label="Tìm kiếm và lọc đơn đăng ký">
        <form method="GET" action="{{ route('admin.registrations') }}" class="registration-search-form">
            @if($currentStatus !== '')<input type="hidden" name="status" value="{{ $currentStatus }}">@endif
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <label for="registrationSearch" class="registration-sr-only">Tìm kiếm đơn đăng ký</label>
            <input type="search" id="registrationSearch" name="search" value="{{ request('search') }}" placeholder="Tên, email, số điện thoại hoặc lớp Yoga...">
            @if(request()->filled('search'))
                <a href="{{ route('admin.registrations', array_filter(['status' => $currentStatus])) }}" aria-label="Xóa từ khóa tìm kiếm"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
            @endif
            <button type="submit">Tìm kiếm</button>
        </form>
        <div class="registration-toolbar__result">
            <span>{{ number_format($registrations->total()) }} kết quả</span>
            <small>Trang {{ $registrations->currentPage() }} / {{ max(1, $registrations->lastPage()) }}</small>
        </div>
    </section>

    <section class="registration-table-panel">
        <div class="registration-table-wrap">
            <table class="registration-table">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Lớp Yoga</th>
                        <th>Gói học</th>
                        <th>Thành tiền</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th><span class="registration-sr-only">Thao tác</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                        @php
                            $status = $registration->status->value;
                            $statusMeta = match($status) {
                                'CONFIRMED' => ['Đã duyệt', 'confirmed', 'fa-circle-check'],
                                'CANCELLED' => ['Đã hủy', 'cancelled', 'fa-circle-xmark'],
                                default => ['Chờ duyệt', 'pending', 'fa-clock'],
                            };
                        @endphp
                        <tr class="{{ $status === 'PENDING' ? 'is-pending' : '' }}">
                            <td data-label="Học viên">
                                <div class="registration-customer">
                                    <span>{{ mb_strtoupper(mb_substr($registration->customer?->name ?? 'H', 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $registration->customer?->name ?? 'Không có thông tin' }}</strong>
                                        <small>{{ $registration->customer?->email ?? 'Không có email' }}</small>
                                        <small>{{ $registration->customer?->phone ?? 'Không có SĐT' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Lớp Yoga">
                                <div class="registration-class">
                                    <strong>{{ $registration->class?->name ?? 'Lớp không còn tồn tại' }}</strong>
                                    <small><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $registration->class?->lich_hoc ?? 'Chưa cập nhật lịch' }}</small>
                                    <small><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i> {{ $registration->class?->teacher?->name ?? 'Chưa có giáo viên' }}</small>
                                </div>
                            </td>
                            <td data-label="Gói học">
                                <div class="registration-package"><strong>{{ $registration->package_months }} tháng</strong><small>Giảm {{ number_format($registration->discount, 0, ',', '.') }}đ</small></div>
                            </td>
                            <td data-label="Thành tiền"><strong class="registration-price">{{ number_format($registration->final_price, 0, ',', '.') }}<small>đ</small></strong></td>
                            <td data-label="Ngày đăng ký">
                                <div class="registration-created"><strong>{{ $registration->created_at->format('d/m/Y') }}</strong><small>{{ $registration->created_at->format('H:i') }} · #{{ str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT) }}</small></div>
                            </td>
                            <td data-label="Trạng thái"><span class="registration-status registration-status--{{ $statusMeta[1] }}"><i class="fa-regular {{ $statusMeta[2] }}" aria-hidden="true"></i> {{ $statusMeta[0] }}</span></td>
                            <td data-label="Thao tác">
                                <div class="registration-actions">
                                    @if($status === 'PENDING')
                                        <form method="POST" action="{{ route('admin.registrations.approve', $registration->id) }}">
                                            @csrf
                                            <button type="submit" class="registration-action registration-action--approve" title="Duyệt đơn" aria-label="Duyệt đơn số {{ $registration->id }}" onclick="return confirm('Duyệt đơn đăng ký #{{ $registration->id }}?')"><i class="fa-solid fa-check" aria-hidden="true"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.registrations.reject', $registration->id) }}">
                                            @csrf
                                            <button type="submit" class="registration-action registration-action--reject" title="Từ chối đơn" aria-label="Từ chối đơn số {{ $registration->id }}" onclick="return confirm('Từ chối đơn đăng ký #{{ $registration->id }}?')"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.registrations.detail', $registration->id) }}" class="registration-action" title="Xem chi tiết" aria-label="Xem chi tiết đơn số {{ $registration->id }}"><i class="fa-regular fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.registrations.edit', $registration->id) }}" class="registration-action" title="Chỉnh sửa" aria-label="Chỉnh sửa đơn số {{ $registration->id }}"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.registrations.destroy', $registration->id) }}" data-confirm-title="Xóa đơn đăng ký?" data-confirm-message="Đơn đăng ký #{{ $registration->id }} sẽ bị xóa vĩnh viễn khỏi hệ thống.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="registration-action registration-action--delete" title="Xóa đơn" aria-label="Xóa đơn số {{ $registration->id }}"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="registration-empty-state">
                                    <span><i class="fa-regular fa-folder-open" aria-hidden="true"></i></span>
                                    <h2>Không tìm thấy đơn đăng ký</h2>
                                    <p>Thử thay đổi từ khóa hoặc trạng thái đang lọc.</p>
                                    @if(request()->hasAny(['search', 'status']))<a href="{{ route('admin.registrations') }}">Xóa toàn bộ bộ lọc</a>@endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <nav class="registration-pagination" aria-label="Phân trang đơn đăng ký">
                @if($registrations->onFirstPage())
                    <span class="is-disabled"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</span>
                @else
                    <a href="{{ $registrations->previousPageUrl() }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</a>
                @endif
                <span class="registration-pagination__current">{{ $registrations->currentPage() }} / {{ $registrations->lastPage() }}</span>
                @if($registrations->hasMorePages())
                    <a href="{{ $registrations->nextPageUrl() }}">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @else
                    <span class="is-disabled">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                @endif
            </nav>
        @endif
    </section>
</div>
@endsection
