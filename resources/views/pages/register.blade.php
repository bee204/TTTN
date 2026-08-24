@extends('layouts.app')

@section('title', 'Đăng ký lớp Yoga - VITA Yoga Center')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/class-register.css') }}">
@endpush

@section('content')
@php
    $currentClassId = (string) old('class_id', $selectedClassId ?? '');
    $currentPackage = (string) old('package_months', '1');
@endphp

<div class="class-register-page">
    <nav class="register-breadcrumb" aria-label="Điều hướng">
        <a href="{{ route('classes') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Danh sách lớp Yoga</a>
        <span aria-hidden="true">/</span>
        <span>Đăng ký lớp học</span>
    </nav>

    <section class="register-heading">
        <div>
            <span class="register-eyebrow"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Bắt đầu hành trình cùng VITA</span>
            <h1>Hoàn tất đăng ký<br><span>lớp Yoga của bạn.</span></h1>
        </div>
        <p>Chọn lớp và gói học phù hợp. Trung tâm sẽ kiểm tra thông tin và xác nhận đơn đăng ký sau khi bạn gửi.</p>
    </section>

    @if($errors->any())
        <div class="register-alert register-alert--error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <div>
                <strong>Vui lòng kiểm tra lại thông tin</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="registerForm" class="register-layout" method="POST" action="{{ route('register.submit') }}" autocomplete="on">
        @csrf

        <div class="register-form-card">
            <section class="register-form-section">
                <div class="register-section-heading">
                    <span>01</span>
                    <div><small>Lớp học</small><h2>Chọn lớp Yoga</h2></div>
                </div>

                <div class="register-field">
                    <label for="className">Lớp bạn muốn tham gia <em>*</em></label>
                    <div class="register-control register-control--select">
                        <i class="fa-solid fa-spa" aria-hidden="true"></i>
                        <select id="className" name="class_id" required>
                            <option value="">Chọn một lớp Yoga</option>
                            @foreach($classes as $class)
                                @if(!$class->is_full && $class->start_date->isFuture())
                                    <option
                                        value="{{ $class->id }}"
                                        data-price="{{ $class->price }}"
                                        data-teacher="{{ $class->teacher?->name ?? 'Đang cập nhật' }}"
                                        data-schedule="{{ $class->lich_hoc }}"
                                        data-time="{{ $class->start_time->format('H:i') }} – {{ $class->end_time->format('H:i') }}"
                                        data-location="{{ $class->location }}"
                                        data-dates="{{ $class->start_date->format('d/m/Y') }} – {{ $class->end_date->format('d/m/Y') }}"
                                        data-slots="{{ $class->available_slots }}"
                                        @selected($currentClassId === (string) $class->id)
                                    >{{ $class->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down register-select-arrow" aria-hidden="true"></i>
                    </div>
                    <small>Chỉ hiển thị các lớp còn chỗ và chưa bắt đầu.</small>
                </div>
            </section>

            <section class="register-form-section">
                <div class="register-section-heading">
                    <span>02</span>
                    <div><small>Gói học</small><h2>Chọn thời hạn phù hợp</h2></div>
                </div>

                <div class="package-options" role="radiogroup" aria-label="Thời hạn gói học">
                    @foreach([
                        1 => ['1 tháng', 'Không giảm'],
                        3 => ['3 tháng', 'Giảm 5%'],
                        6 => ['6 tháng', 'Giảm 10%'],
                        12 => ['12 tháng', 'Giảm 15%'],
                    ] as $months => [$label, $discountLabel])
                        <label class="package-option">
                            <input type="radio" name="package_months" value="{{ $months }}" @checked($currentPackage === (string) $months) required>
                            <span><strong>{{ $label }}</strong><small>{{ $discountLabel }}</small></span>
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="register-form-section">
                <div class="register-section-heading">
                    <span>03</span>
                    <div><small>Học viên</small><h2>Thông tin liên hệ</h2></div>
                </div>

                <div class="register-field-grid">
                    <div class="register-field">
                        <label for="fullname">Họ và tên <em>*</em></label>
                        <div class="register-control">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input type="text" id="fullname" name="name" required maxlength="255" autocomplete="name" placeholder="Họ và tên của bạn" value="{{ old('name', $user->name) }}">
                        </div>
                        @error('name')<small class="register-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="register-field">
                        <label for="phone">Số điện thoại <em>*</em></label>
                        <div class="register-control">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <input type="tel" id="phone" name="phone" required maxlength="20" autocomplete="tel" inputmode="tel" placeholder="Ví dụ: 0909 123 456" value="{{ old('phone', $user->customer?->phone) }}">
                        </div>
                        @error('phone')<small class="register-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="register-field">
                    <label for="email">Email tài khoản</label>
                    <div class="register-control register-control--locked">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        <input type="email" id="email" value="{{ $user->email }}" disabled>
                        <span><i class="fa-solid fa-lock" aria-hidden="true"></i> Cố định</span>
                    </div>
                    <small>Email dùng để đăng nhập nên không thể thay đổi tại đây.</small>
                </div>

                <div class="register-field">
                    <label for="notes">Ghi chú cho trung tâm</label>
                    <textarea id="notes" name="notes" rows="4" maxlength="1000" placeholder="Tình trạng sức khỏe hoặc điều bạn muốn giáo viên lưu ý...">{{ old('notes') }}</textarea>
                </div>
            </section>

            <label class="register-consent">
                <input type="checkbox" id="terms" name="terms" value="1" required @checked(old('terms'))>
                <span><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                <p>Tôi xác nhận thông tin trên là chính xác và đồng ý để VITA liên hệ xác nhận đăng ký. <em>*</em></p>
            </label>

            <button type="submit" class="register-submit" id="registerSubmit">
                Gửi đăng ký <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
            <p class="register-submit-note"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i> Bạn chưa cần thanh toán ở bước này.</p>
        </div>

        <aside class="register-summary" aria-live="polite">
            <div class="register-summary__top">
                <span class="register-summary__eyebrow">Tóm tắt đăng ký</span>
                <span class="register-summary__icon"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                <h2 id="summaryClassName">Chưa chọn lớp</h2>
                <p id="summaryTeacher">Chọn một lớp Yoga để xem thông tin.</p>
            </div>

            <div class="register-summary__facts" id="summaryFacts" hidden>
                <div><i class="fa-regular fa-calendar" aria-hidden="true"></i><span><small>Lịch học</small><strong id="summarySchedule">—</strong></span></div>
                <div><i class="fa-regular fa-clock" aria-hidden="true"></i><span><small>Khung giờ</small><strong id="summaryTime">—</strong></span></div>
                <div><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><small>Địa điểm</small><strong id="summaryLocation">—</strong></span></div>
                <div><i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span><small>Khóa học</small><strong id="summaryDates">—</strong></span></div>
            </div>

            <div class="register-summary__pricing">
                <div><span>Học phí gốc</span><strong id="originalPrice">—</strong></div>
                <div><span>Ưu đãi gói học</span><strong id="discountAmount">—</strong></div>
                <div class="register-summary__total"><span>Tổng dự kiến</span><strong id="finalPrice">—</strong></div>
            </div>

            <p class="register-summary__status" id="summaryStatus"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Vui lòng chọn lớp học.</p>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/register.js') }}"></script>
@endpush
