@extends('layouts.admin')

@section('title', 'Chỉnh sửa đơn #'.$registration->id.' - VITA Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-registration-create.css') }}">
@endpush

@section('content')
@php
    $status = $registration->status->value;
    $statusMeta = match($status) {
        'CONFIRMED' => ['Đã duyệt', 'confirmed', 'fa-circle-check'],
        'CANCELLED' => ['Đã hủy', 'cancelled', 'fa-circle-xmark'],
        default => ['Chờ duyệt', 'pending', 'fa-clock'],
    };
    $selectedClassId = (string) old('class_id', $registration->class_id);
    $selectedPackage = (int) old('package_months', $registration->package_months);
@endphp

<div class="registration-create-page registration-edit-page">
    <header class="registration-create-header">
        <div>
            <a href="{{ route('admin.registrations.detail', $registration->id) }}" class="registration-create-back">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Chi tiết đơn đăng ký
            </a>
            <span class="registration-create-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Cập nhật hồ sơ</span>
            <div class="registration-edit-title-row">
                <h1>Chỉnh sửa đơn #{{ str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT) }}</h1>
                <span class="registration-edit-status registration-edit-status--{{ $statusMeta[1] }}"><i class="fa-regular {{ $statusMeta[2] }}" aria-hidden="true"></i> {{ $statusMeta[0] }}</span>
            </div>
            <p>Thay đổi thông tin học viên, lớp Yoga hoặc thời hạn gói học.</p>
        </div>
        <div class="registration-create-status">
            <span><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
            <div><strong>Cập nhật gần nhất</strong><small>{{ $registration->updated_at->format('H:i · d/m/Y') }}</small></div>
        </div>
    </header>

    <form id="adminRegistrationForm" class="registration-create-form" method="POST" action="{{ route('admin.registrations.update', $registration->id) }}" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="registration-create-main">
            <section class="registration-form-card" aria-labelledby="editCustomerTitle">
                <div class="registration-card-heading">
                    <span>01</span>
                    <div><h2 id="editCustomerTitle">Thông tin học viên</h2><p>Các thay đổi tại đây cũng cập nhật hồ sơ học viên trong hệ thống.</p></div>
                </div>

                <div class="registration-edit-customer">
                    <span>{{ mb_strtoupper(mb_substr($registration->customer?->name ?? 'H', 0, 1)) }}</span>
                    <p><strong>{{ $registration->customer?->name ?? 'Không có thông tin' }}</strong><small>Mã học viên #{{ $registration->customer_id ? str_pad((string) $registration->customer_id, 4, '0', STR_PAD_LEFT) : '—' }}</small></p>
                </div>

                <div class="registration-field-grid">
                    <div class="registration-field registration-field--full">
                        <label for="fullname">Họ và tên <span>*</span></label>
                        <div class="registration-input-wrap">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input type="text" id="fullname" name="name" value="{{ old('name', $registration->customer?->name) }}" placeholder="Ví dụ: Nguyễn Minh Anh" maxlength="255" required autofocus>
                        </div>
                        @error('name')<small class="registration-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="registration-field">
                        <label for="email">Email <span>*</span></label>
                        <div class="registration-input-wrap">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <input type="email" id="email" name="email" value="{{ $registration->customer?->email }}" maxlength="255" aria-describedby="registrationEmailLocked" readonly required>
                        </div>
                        <small id="registrationEmailLocked" class="registration-field-hint"><i class="fa-solid fa-lock" aria-hidden="true"></i> Email dùng để đăng nhập nên không thể thay đổi.</small>
                        @error('email')<small class="registration-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="registration-field">
                        <label for="phone">Số điện thoại <span>*</span></label>
                        <div class="registration-input-wrap">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $registration->customer?->phone) }}" placeholder="090 123 4567" maxlength="20" required>
                        </div>
                        @error('phone')<small class="registration-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
            </section>

            <section class="registration-form-card" aria-labelledby="editClassTitle">
                <div class="registration-card-heading">
                    <span>02</span>
                    <div><h2 id="editClassTitle">Lớp và gói học</h2><p>Đổi lớp hoặc thời hạn sẽ tính lại toàn bộ chi phí của đơn.</p></div>
                </div>

                <div class="registration-field">
                    <label for="class_id">Lớp Yoga <span>*</span></label>
                    <div class="registration-select-wrap">
                        <i class="fa-solid fa-spa" aria-hidden="true"></i>
                        <select id="class_id" name="class_id" required>
                            <option value="">Chọn một lớp Yoga</option>
                            @foreach($classes as $class)
                                @php($availableSlots = max(0, $class->quantity - $class->confirmed_registrations_count))
                                <option value="{{ $class->id }}"
                                        data-price="{{ $class->price }}"
                                        data-schedule="{{ $class->lich_hoc }}"
                                        data-time="{{ $class->start_time?->format('H:i') }} – {{ $class->end_time?->format('H:i') }}"
                                        data-location="{{ $class->location }}"
                                        data-teacher="{{ $class->teacher?->name ?? 'Chưa có giáo viên' }}"
                                        data-slots="{{ $availableSlots }}"
                                        {{ $selectedClassId === (string) $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} · {{ number_format($class->price, 0, ',', '.') }}đ/tháng{{ $class->id === $registration->class_id ? ' · Lớp hiện tại' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id')<small class="registration-field-error">{{ $message }}</small>@enderror
                </div>

                <div id="selectedClassDetails" class="registration-class-preview" hidden>
                    <div><i class="fa-regular fa-calendar" aria-hidden="true"></i><span><small>Lịch học</small><strong id="classSchedule">—</strong></span></div>
                    <div><i class="fa-regular fa-clock" aria-hidden="true"></i><span><small>Khung giờ</small><strong id="classTime">—</strong></span></div>
                    <div><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><small>Địa điểm</small><strong id="classLocation">—</strong></span></div>
                    <div><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i><span><small>Giáo viên</small><strong id="classTeacher">—</strong></span></div>
                    <div><i class="fa-solid fa-user-group" aria-hidden="true"></i><span><small>Còn trống</small><strong id="classSlots">—</strong></span></div>
                </div>

                <fieldset class="registration-package-fieldset">
                    <legend>Thời hạn gói học <span>*</span></legend>
                    <div class="registration-package-grid">
                        @foreach([1 => 0, 3 => 5, 6 => 10, 12 => 15] as $months => $discount)
                            <label class="registration-package-option">
                                <input type="radio" name="package_months" value="{{ $months }}" {{ $selectedPackage === $months ? 'checked' : '' }} required>
                                <span class="registration-package-option__check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                <strong>{{ $months }} tháng</strong>
                                <small>{{ $discount > 0 ? 'Tiết kiệm '.$discount.'%' : 'Gói linh hoạt' }}</small>
                            </label>
                        @endforeach
                    </div>
                    @error('package_months')<small class="registration-field-error">{{ $message }}</small>@enderror
                </fieldset>

                <div class="registration-field">
                    <label for="notes">Ghi chú <small>Không bắt buộc</small></label>
                    <textarea id="notes" name="notes" rows="4" maxlength="255" placeholder="Nhu cầu tập luyện hoặc thông tin cần lưu ý...">{{ old('notes', $registration->note) }}</textarea>
                    <div class="registration-note-meta"><span>Chỉ quản trị viên nhìn thấy ghi chú này</span><span id="noteCounter">0/255</span></div>
                    @error('notes')<small class="registration-field-error">{{ $message }}</small>@enderror
                </div>
            </section>
        </div>

        <aside class="registration-price-card" aria-labelledby="editSummaryTitle">
            <div class="registration-price-card__heading">
                <span><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
                <div><h2 id="editSummaryTitle">Giá trị sau cập nhật</h2><p>Hệ thống tính lại theo lựa chọn hiện tại.</p></div>
            </div>

            <div class="registration-price-placeholder" id="pricePlaceholder"><span><i class="fa-solid fa-arrow-pointer" aria-hidden="true"></i></span><p>Chọn lớp Yoga để xem chi phí.</p></div>
            <div class="registration-price-content" id="priceContent" hidden>
                <div class="registration-price-selected-class"><small>Lớp đã chọn</small><strong id="summaryClassName">—</strong></div>
                <dl>
                    <div><dt>Học phí tháng</dt><dd id="monthlyPrice">0đ</dd></div>
                    <div><dt>Thời hạn</dt><dd id="summaryPackage">1 tháng</dd></div>
                    <div><dt>Giá gốc</dt><dd id="originalPrice">0đ</dd></div>
                    <div class="registration-price-discount"><dt>Ưu đãi</dt><dd id="discountAmount">− 0đ</dd></div>
                </dl>
                <div class="registration-price-total"><span>Tổng thanh toán<small>Giá trị mới của đơn</small></span><strong id="finalPrice">0đ</strong></div>
            </div>

            <div class="registration-edit-impact"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p><strong>Trạng thái không thay đổi</strong><span>Việc lưu thông tin không tự động duyệt hoặc hủy đơn.</span></p></div>

            <button type="submit" class="registration-submit"><span>Lưu thay đổi</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            <a href="{{ route('admin.registrations.detail', $registration->id) }}" class="registration-cancel">Hủy và quay lại chi tiết đơn</a>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-registration-create.js') }}"></script>
@endpush
