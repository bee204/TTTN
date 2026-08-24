@extends('layouts.admin')

@section('title', 'Đơn đăng ký #'.$registration->id.' - VITA Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-registration-detail.css') }}">
@endpush

@section('content')
@php
    $status = $registration->status->value;
    $statusMeta = match($status) {
        'CONFIRMED' => ['Đã duyệt', 'confirmed', 'fa-circle-check', 'Đơn đã được xác nhận và học viên đã được ghi nhận vào lớp.'],
        'CANCELLED' => ['Đã hủy', 'cancelled', 'fa-circle-xmark', 'Đơn đã bị hủy và không còn hiệu lực đăng ký lớp.'],
        default => ['Chờ duyệt', 'pending', 'fa-clock', 'Đơn đang chờ quản trị viên kiểm tra và xác nhận.'],
    };
    $customer = $registration->customer;
    $class = $registration->class;
    $originalPrice = (float) $registration->final_price + (float) $registration->discount;
@endphp

<div class="registration-detail-page">
    <header class="registration-detail-header">
        <div>
            <a href="{{ route('admin.registrations') }}" class="registration-detail-back">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Danh sách đơn đăng ký
            </a>
            <span class="registration-detail-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Hồ sơ đăng ký</span>
            <div class="registration-detail-title-row">
                <h1>Đơn đăng ký #{{ str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT) }}</h1>
                <span class="registration-detail-status registration-detail-status--{{ $statusMeta[1] }}">
                    <i class="fa-regular {{ $statusMeta[2] }}" aria-hidden="true"></i> {{ $statusMeta[0] }}
                </span>
            </div>
            <p>Tạo lúc {{ $registration->created_at->format('H:i, d/m/Y') }} · Cập nhật lúc {{ $registration->updated_at->format('H:i, d/m/Y') }}</p>
        </div>

        <a href="{{ route('admin.registrations.edit', $registration->id) }}" class="registration-detail-edit">
            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Chỉnh sửa đơn
        </a>
    </header>

    <div class="registration-detail-layout">
        <main class="registration-detail-main">
            <section class="registration-detail-card" aria-labelledby="studentSectionTitle">
                <div class="registration-detail-card__header">
                    <span><i class="fa-regular fa-user" aria-hidden="true"></i></span>
                    <div><h2 id="studentSectionTitle">Thông tin học viên</h2><p>Thông tin liên hệ gắn với đơn đăng ký này.</p></div>
                </div>

                <div class="registration-student-profile">
                    <div class="registration-student-avatar">{{ mb_strtoupper(mb_substr($customer?->name ?? 'H', 0, 1)) }}</div>
                    <div>
                        <h3>{{ $customer?->name ?? 'Không có thông tin' }}</h3>
                        <span>Mã học viên #{{ $customer?->id ? str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT) : '—' }}</span>
                    </div>
                </div>

                <dl class="registration-information-grid">
                    <div>
                        <dt><i class="fa-regular fa-envelope" aria-hidden="true"></i> Email</dt>
                        <dd>@if($customer?->email)<a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>@else Chưa cập nhật @endif</dd>
                    </div>
                    <div>
                        <dt><i class="fa-solid fa-phone" aria-hidden="true"></i> Số điện thoại</dt>
                        <dd>@if($customer?->phone)<a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>@else Chưa cập nhật @endif</dd>
                    </div>
                    @if($customer?->address)
                        <div class="registration-information-grid__full">
                            <dt><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Địa chỉ</dt>
                            <dd>{{ $customer->address }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="registration-detail-card" aria-labelledby="classSectionTitle">
                <div class="registration-detail-card__header">
                    <span><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                    <div><h2 id="classSectionTitle">Thông tin lớp Yoga</h2><p>Lịch tập và thông tin vận hành của lớp đã chọn.</p></div>
                </div>

                @if($class)
                    <div class="registration-class-heading">
                        <div><small>Lớp đã đăng ký</small><h3>{{ $class->name }}</h3></div>
                        <strong>{{ number_format($class->price, 0, ',', '.') }}<small>đ/tháng</small></strong>
                    </div>

                    <div class="registration-class-facts">
                        <div><span><i class="fa-regular fa-calendar" aria-hidden="true"></i></span><p><small>Lịch học</small><strong>{{ $class->lich_hoc }}</strong></p></div>
                        <div><span><i class="fa-regular fa-clock" aria-hidden="true"></i></span><p><small>Khung giờ</small><strong>{{ $class->start_time?->format('H:i') }} – {{ $class->end_time?->format('H:i') }}</strong></p></div>
                        <div><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span><p><small>Địa điểm</small><strong>{{ $class->location }}</strong></p></div>
                        <div><span><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span><p><small>Giáo viên</small><strong>{{ $class->teacher?->name ?? 'Chưa cập nhật' }}</strong></p></div>
                        <div><span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span><p><small>Thời gian khóa</small><strong>{{ $class->start_date?->format('d/m/Y') }} – {{ $class->end_date?->format('d/m/Y') }}</strong></p></div>
                        <div><span><i class="fa-solid fa-user-group" aria-hidden="true"></i></span><p><small>Sức chứa</small><strong>{{ $class->quantity }} học viên</strong></p></div>
                    </div>

                    @if($class->description)
                        <div class="registration-class-description"><small>Mô tả lớp</small><p>{{ $class->description }}</p></div>
                    @endif
                @else
                    <div class="registration-detail-missing"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Lớp Yoga của đơn này không còn tồn tại.</div>
                @endif
            </section>

            <section class="registration-detail-card" aria-labelledby="noteSectionTitle">
                <div class="registration-detail-card__header">
                    <span><i class="fa-regular fa-note-sticky" aria-hidden="true"></i></span>
                    <div><h2 id="noteSectionTitle">Ghi chú và lịch sử</h2><p>Thông tin bổ sung và thời điểm cập nhật đơn.</p></div>
                </div>

                <div class="registration-note-box {{ $registration->note ? '' : 'is-empty' }}">
                    <small>Ghi chú đăng ký</small>
                    <p>{{ $registration->note ?: 'Không có ghi chú cho đơn đăng ký này.' }}</p>
                </div>

                <div class="registration-timeline">
                    <div><span><i class="fa-solid fa-plus" aria-hidden="true"></i></span><p><strong>Đơn được tạo</strong><small>{{ $registration->created_at->format('H:i:s · d/m/Y') }}</small></p></div>
                    <div><span><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></span><p><strong>Cập nhật gần nhất</strong><small>{{ $registration->updated_at->format('H:i:s · d/m/Y') }}</small></p></div>
                </div>

                @if($registration->idempotency_key)
                    <div class="registration-reference"><span>Mã đối soát</span><code>{{ $registration->idempotency_key }}</code></div>
                @endif
            </section>
        </main>

        <aside class="registration-detail-sidebar">
            <section class="registration-payment-card" aria-labelledby="paymentSectionTitle">
                <div class="registration-payment-card__header"><span><i class="fa-solid fa-receipt" aria-hidden="true"></i></span><div><h2 id="paymentSectionTitle">Chi tiết thanh toán</h2><p>Giá trị được lưu tại thời điểm đăng ký.</p></div></div>
                <div class="registration-payment-package"><small>Gói học</small><strong>{{ $registration->package_months }} tháng</strong></div>
                <dl>
                    <div><dt>Giá gốc</dt><dd>{{ number_format($originalPrice, 0, ',', '.') }}đ</dd></div>
                    <div class="registration-payment-discount"><dt>Ưu đãi</dt><dd>− {{ number_format($registration->discount, 0, ',', '.') }}đ</dd></div>
                </dl>
                <div class="registration-payment-total"><span>Tổng thanh toán<small>Giá trị cuối cùng của đơn</small></span><strong>{{ number_format($registration->final_price, 0, ',', '.') }}đ</strong></div>
            </section>

            <section class="registration-state-card registration-state-card--{{ $statusMeta[1] }}">
                <div class="registration-state-icon"><i class="fa-regular {{ $statusMeta[2] }}" aria-hidden="true"></i></div>
                <div><small>Trạng thái hiện tại</small><h2>{{ $statusMeta[0] }}</h2><p>{{ $statusMeta[3] }}</p></div>
            </section>

            @if($status === 'PENDING')
                <section class="registration-quick-actions">
                    <h2>Xử lý đơn đăng ký</h2>
                    <p>Kiểm tra thông tin học viên và lớp trước khi xác nhận.</p>
                    <form method="POST" action="{{ route('admin.registrations.approve', $registration->id) }}">
                        @csrf
                        <button type="submit" class="registration-action-button registration-action-button--approve" onclick="return confirm('Duyệt đơn đăng ký #{{ $registration->id }}?')">
                            <i class="fa-solid fa-check" aria-hidden="true"></i><span>Duyệt đơn đăng ký</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.registrations.reject', $registration->id) }}">
                        @csrf
                        <button type="submit" class="registration-action-button registration-action-button--reject" onclick="return confirm('Hủy đơn đăng ký #{{ $registration->id }}?')">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i><span>Hủy đơn đăng ký</span>
                        </button>
                    </form>
                </section>
            @endif

            <section class="registration-secondary-actions">
                <a href="{{ route('admin.registrations.edit', $registration->id) }}"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Chỉnh sửa thông tin</a>
                <form method="POST" action="{{ route('admin.registrations.destroy', $registration->id) }}" data-confirm-title="Xóa đơn đăng ký?" data-confirm-message="Đơn đăng ký #{{ $registration->id }} sẽ bị xóa vĩnh viễn khỏi hệ thống.">
                    @csrf
                    @method('DELETE')
                    <button type="submit"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Xóa đơn đăng ký</button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
