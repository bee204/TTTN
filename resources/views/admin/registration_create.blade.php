@extends('layouts.admin')

@section('title', 'Tạo đơn đăng ký - VITA Control')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-registration-create.css') }}">
@endpush

@section('content')
<div class="registration-create-page">
    <header class="registration-create-header">
        <div>
            <a href="{{ route('admin.registrations') }}" class="registration-create-back">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Danh sách đơn đăng ký
            </a>
            <span class="registration-create-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Đăng ký tại quầy</span>
            <h1>Tạo đơn đăng ký mới</h1>
            <p>Nhập thông tin học viên, chọn lớp Yoga và gói học phù hợp.</p>
        </div>
        <div class="registration-create-status">
            <span><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
            <div><strong>Duyệt tự động</strong><small>Đơn do quản trị viên tạo có hiệu lực ngay</small></div>
        </div>
    </header>

    <form id="adminRegistrationForm"
          class="registration-create-form"
          method="POST"
          action="{{ route('admin.registrations.create') }}"
          data-customer-search-url="{{ route('admin.customers.search') }}"
          autocomplete="off">
        @csrf

        <div class="registration-create-main">
            <section class="registration-form-card" aria-labelledby="customerInformationTitle">
                <div class="registration-card-heading">
                    <span>01</span>
                    <div><h2 id="customerInformationTitle">Thông tin học viên</h2><p>Hệ thống sẽ nhận diện học viên cũ qua email hoặc số điện thoại.</p></div>
                </div>

                <div id="existingCustomerNotice" class="registration-customer-notice" role="status" aria-live="polite" hidden>
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span>Đã tìm thấy học viên trong hệ thống và điền thông tin tương ứng.</span>
                </div>

                <div class="registration-field-grid">
                    <div class="registration-field registration-field--full">
                        <label for="fullname">Họ và tên <span>*</span></label>
                        <div class="registration-input-wrap">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input type="text" id="fullname" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Nguyễn Minh Anh" maxlength="255" required autofocus>
                        </div>
                        @error('name')<small class="registration-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="registration-field">
                        <label for="email">Email <span>*</span></label>
                        <div class="registration-input-wrap">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="minhanh@email.com" maxlength="255" required>
                        </div>
                        @error('email')<small class="registration-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="registration-field">
                        <label for="phone">Số điện thoại <span>*</span></label>
                        <div class="registration-input-wrap">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="090 123 4567" maxlength="20" required>
                        </div>
                        @error('phone')<small class="registration-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
            </section>

            <section class="registration-form-card" aria-labelledby="classInformationTitle">
                <div class="registration-card-heading">
                    <span>02</span>
                    <div><h2 id="classInformationTitle">Lớp và gói học</h2><p>Chỉ các lớp còn chỗ mới có thể được đăng ký.</p></div>
                </div>

                <div class="registration-field">
                    <label for="class_id">Lớp Yoga <span>*</span></label>
                    <div class="registration-select-wrap">
                        <i class="fa-solid fa-spa" aria-hidden="true"></i>
                        <select id="class_id" name="class_id" required>
                            <option value="">Chọn một lớp Yoga</option>
                            @foreach($classes as $class)
                                @php($availableSlots = max(0, $class->quantity - $class->confirmed_registrations_count))
                                @if($availableSlots > 0)
                                    <option value="{{ $class->id }}"
                                            data-price="{{ $class->price }}"
                                            data-schedule="{{ $class->lich_hoc }}"
                                            data-time="{{ $class->start_time?->format('H:i') }} – {{ $class->end_time?->format('H:i') }}"
                                            data-location="{{ $class->location }}"
                                            data-teacher="{{ $class->teacher?->name ?? 'Chưa có giáo viên' }}"
                                            data-slots="{{ $availableSlots }}"
                                            {{ (string) old('class_id') === (string) $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} · {{ number_format($class->price, 0, ',', '.') }}đ/tháng
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @error('class_id')<small class="registration-field-error">{{ $message }}</small>@enderror
                    @if($classes->isEmpty())<small class="registration-field-hint">Hiện chưa có lớp Yoga nào để đăng ký.</small>@endif
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
                                <input type="radio" name="package_months" value="{{ $months }}" {{ (int) old('package_months', 1) === $months ? 'checked' : '' }} required>
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
                    <textarea id="notes" name="notes" rows="4" maxlength="255" placeholder="Nhu cầu tập luyện hoặc thông tin cần lưu ý...">{{ old('notes') }}</textarea>
                    <div class="registration-note-meta"><span>Chỉ quản trị viên nhìn thấy ghi chú này</span><span id="noteCounter">0/255</span></div>
                    @error('notes')<small class="registration-field-error">{{ $message }}</small>@enderror
                </div>
            </section>
        </div>

        <aside class="registration-price-card" aria-labelledby="orderSummaryTitle">
            <div class="registration-price-card__heading">
                <span><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
                <div><h2 id="orderSummaryTitle">Tạm tính đơn đăng ký</h2><p>Chi phí được tính theo lớp và thời hạn đã chọn.</p></div>
            </div>

            <div class="registration-price-placeholder" id="pricePlaceholder">
                <span><i class="fa-solid fa-arrow-pointer" aria-hidden="true"></i></span>
                <p>Chọn lớp Yoga để xem chi phí.</p>
            </div>

            <div class="registration-price-content" id="priceContent" hidden>
                <div class="registration-price-selected-class"><small>Lớp đã chọn</small><strong id="summaryClassName">—</strong></div>
                <dl>
                    <div><dt>Học phí tháng</dt><dd id="monthlyPrice">0đ</dd></div>
                    <div><dt>Thời hạn</dt><dd id="summaryPackage">1 tháng</dd></div>
                    <div><dt>Giá gốc</dt><dd id="originalPrice">0đ</dd></div>
                    <div class="registration-price-discount"><dt>Ưu đãi</dt><dd id="discountAmount">− 0đ</dd></div>
                </dl>
                <div class="registration-price-total"><span>Tổng thanh toán<small>Đã bao gồm ưu đãi gói</small></span><strong id="finalPrice">0đ</strong></div>
            </div>

            <div class="registration-auto-approval">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <p><strong>Đơn sẽ được xác nhận ngay</strong><span>Học viên được ghi nhận vào lớp sau khi tạo.</span></p>
            </div>

            <button type="submit" class="registration-submit">
                <span>Tạo và duyệt đơn</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
            <a href="{{ route('admin.registrations') }}" class="registration-cancel">Hủy và quay lại danh sách</a>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-registration-create.js') }}"></script>
@endpush
