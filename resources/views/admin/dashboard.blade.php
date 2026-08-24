@extends('layouts.admin')

@section('title', 'Tổng quan vận hành - VITA Control')
@section('body-class', 'admin-dashboard-body')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endpush

@section('content')
@php
    $totalRegistrations = max(1, $stats['registrations']);
    $pendingPercent = round($stats['pending_registrations'] / $totalRegistrations * 100);
    $confirmedPercent = round($stats['approved_registrations'] / $totalRegistrations * 100);
    $cancelledPercent = round($stats['cancelled_registrations'] / $totalRegistrations * 100);
@endphp

<div class="control-dashboard">
    <header class="control-dashboard__header">
        <div>
            <span class="control-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> Trung tâm vận hành</span>
            <h1>Chào mừng trở lại, {{ Auth::user()->name ?: Auth::user()->user_name }}.</h1>
            <p>Tổng quan hoạt động của VITA Yoga Center tại thời điểm hiện tại.</p>
        </div>
        <div class="control-header-actions">
            <span class="control-current-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ now()->format('d/m/Y') }}</span>
            <a href="{{ route('admin.registrations', ['status' => 'pending']) }}" class="control-primary-action">
                <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
                Xử lý đơn chờ duyệt
                @if($stats['pending_registrations'] > 0)<span>{{ $stats['pending_registrations'] }}</span>@endif
            </a>
        </div>
    </header>

    <section class="control-kpis" aria-label="Chỉ số tổng quan">
        <article class="control-kpi control-kpi--classes">
            <div class="control-kpi__top"><span><i class="fa-solid fa-spa" aria-hidden="true"></i></span><small>Lớp Yoga</small></div>
            <strong>{{ number_format($stats['classes']) }}</strong>
            <footer><span>Đang có trong hệ thống</span><a href="{{ route('admin.classes') }}" aria-label="Quản lý lớp Yoga"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></footer>
        </article>
        <article class="control-kpi control-kpi--customers">
            <div class="control-kpi__top"><span><i class="fa-solid fa-users" aria-hidden="true"></i></span><small>Học viên</small></div>
            <strong>{{ number_format($stats['customers']) }}</strong>
            <footer><span>Hồ sơ học viên</span><a href="{{ route('admin.customers') }}" aria-label="Quản lý học viên"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></footer>
        </article>
        <article class="control-kpi control-kpi--teachers">
            <div class="control-kpi__top"><span><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span><small>Giáo viên Yoga</small></div>
            <strong>{{ number_format($stats['teachers']) }}</strong>
            <footer><span>Đội ngũ giảng dạy</span><a href="{{ route('admin.teachers') }}" aria-label="Quản lý giáo viên"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></footer>
        </article>
        <article class="control-kpi control-kpi--registrations">
            <div class="control-kpi__top"><span><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span><small>Tổng đăng ký</small></div>
            <strong>{{ number_format($stats['registrations']) }}</strong>
            <footer><span>{{ $stats['pending_registrations'] }} đơn cần xử lý</span><a href="{{ route('admin.registrations') }}" aria-label="Quản lý đơn đăng ký"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></footer>
        </article>
    </section>

    <section class="control-analytics" id="dashboardAnalytics" data-endpoint="{{ route('admin.dashboard.analytics') }}" aria-labelledby="analyticsTitle">
        <header class="control-analytics__header">
            <div>
                <span class="control-eyebrow"><i class="fa-solid fa-chart-simple" aria-hidden="true"></i> Phân tích vận hành</span>
                <h2 id="analyticsTitle">Sức khỏe trung tâm qua dữ liệu</h2>
                <p>Theo dõi tăng trưởng học viên, lớp học, phân bổ giảng dạy và doanh số đã xác nhận.</p>
            </div>
            <label class="control-chart-filter">
                <span>Khoảng phân tích</span>
                <select id="analyticsPeriod" aria-label="Chọn khoảng thời gian phân tích">
                    <option value="30">30 ngày qua</option>
                    <option value="90" selected>90 ngày qua</option>
                    <option value="180">6 tháng qua</option>
                    <option value="365">12 tháng qua</option>
                </select>
            </label>
        </header>

        <div class="control-analytics__notice" id="analyticsNotice" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Đang tổng hợp dữ liệu...
        </div>

        <div class="control-analytics__grid" aria-busy="true">
            <article class="control-forecast-card" id="revenueForecastCard">
                <div class="control-forecast-card__icon"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></div>
                <div class="control-forecast-card__main">
                    <span>Doanh số dự tính tháng hiện tại</span>
                    <strong id="forecastRevenue">0 ₫</strong>
                    <small id="forecastMethod">Đang tính từ tiến độ doanh số trong tháng...</small>
                </div>
                <div class="control-forecast-card__actual">
                    <small>Đã ghi nhận</small>
                    <strong id="currentMonthRevenue">0 ₫</strong>
                    <span id="forecastProgress">0/0 ngày</span>
                </div>
                <div class="control-forecast-card__comparison">
                    <small id="previousMonthLabel">So với tháng trước</small>
                    <strong id="previousMonthRevenue">0 ₫</strong>
                    <span class="is-flat" id="forecastChange"><i class="fa-solid fa-minus" aria-hidden="true"></i> 0%</span>
                </div>
            </article>

            <article class="control-chart-card control-chart-card--growth">
                <div class="control-chart-card__heading">
                    <div><span>Tốc độ mở rộng</span><h3>Tăng trưởng mới</h3></div>
                    <small id="growthPeriodLabel">90 ngày qua</small>
                </div>
                <div class="control-growth-charts">
                    <div class="control-growth-metric" id="customerGrowthChart"></div>
                    <div class="control-growth-metric" id="classGrowthChart"></div>
                </div>
            </article>

            <article class="control-chart-card control-chart-card--lifecycle">
                <div class="control-chart-card__heading">
                    <div><span>Trạng thái hiện tại</span><h3>Vòng đời lớp Yoga</h3></div>
                    <a href="{{ route('admin.classes') }}">Quản lý lớp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="control-lifecycle-chart" id="classLifecycleChart"></div>
            </article>

            <article class="control-chart-card control-chart-card--teachers">
                <div class="control-chart-card__heading">
                    <div><span>Phân bổ nguồn lực</span><h3>Tỷ lệ đứng lớp của giáo viên</h3></div>
                    <small id="teacherAssignmentTotal">0 lượt phân công</small>
                </div>
                <div class="control-column-chart" id="teacherChart" aria-label="Biểu đồ tỷ lệ đứng lớp của giáo viên"></div>
            </article>

            <article class="control-chart-card control-chart-card--revenue">
                <div class="control-chart-card__heading">
                    <div><span>Đơn đã xác nhận</span><h3>Doanh số theo tháng</h3></div>
                    <label class="control-chart-filter control-chart-filter--compact">
                        <span class="sr-only">Khoảng thời gian doanh số</span>
                        <select id="revenuePeriod" aria-label="Chọn khoảng thời gian doanh số">
                            <option value="6" selected>6 tháng</option>
                            <option value="12">12 tháng</option>
                        </select>
                    </label>
                </div>
                <div class="control-revenue-summary">
                    <div><small>Tổng trong kỳ</small><strong id="revenueTotal">0 ₫</strong></div>
                    <div><small>Trung bình/tháng</small><strong id="revenueAverage">0 ₫</strong></div>
                </div>
                <div class="control-column-chart control-column-chart--revenue" id="revenueChart" aria-label="Biểu đồ doanh số theo tháng"></div>
            </article>
        </div>
    </section>

    <div class="control-dashboard__grid">
        <section class="control-panel control-recent">
            <div class="control-panel__heading">
                <div><span>Hoạt động mới</span><h2>Đăng ký gần đây</h2></div>
                <a href="{{ route('admin.registrations') }}">Xem tất cả <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>

            <div class="control-registration-list">
                @forelse($recentRegistrations as $registration)
                    @php
                        $status = $registration->status->value;
                        $statusMeta = match($status) {
                            'CONFIRMED' => ['Đã duyệt', 'confirmed', 'fa-circle-check'],
                            'CANCELLED' => ['Đã hủy', 'cancelled', 'fa-circle-xmark'],
                            default => ['Chờ duyệt', 'pending', 'fa-clock'],
                        };
                    @endphp
                    <article class="control-registration-row">
                        <span class="control-registration-row__avatar">{{ mb_strtoupper(mb_substr($registration->customer?->name ?? 'H', 0, 1)) }}</span>
                        <div class="control-registration-row__main">
                            <strong>{{ $registration->customer?->name ?? 'Học viên không xác định' }}</strong>
                            <span><i class="fa-solid fa-spa" aria-hidden="true"></i> {{ $registration->class?->name ?? 'Lớp không còn tồn tại' }}</span>
                        </div>
                        <div class="control-registration-row__time">
                            <strong>{{ $registration->created_at->format('d/m/Y') }}</strong>
                            <span>{{ $registration->created_at->format('H:i') }}</span>
                        </div>
                        <span class="control-status control-status--{{ $statusMeta[1] }}"><i class="fa-solid {{ $statusMeta[2] }}" aria-hidden="true"></i> {{ $statusMeta[0] }}</span>
                        <a href="{{ route('admin.registrations.detail', $registration->id) }}" class="control-registration-row__link" aria-label="Xem đơn đăng ký số {{ $registration->id }}"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                    </article>
                @empty
                    <div class="control-empty-state">
                        <span><i class="fa-regular fa-folder-open" aria-hidden="true"></i></span>
                        <strong>Chưa có đơn đăng ký</strong>
                        <p>Các đơn mới sẽ xuất hiện tại đây.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="control-dashboard__side">
            <section class="control-panel control-registration-overview">
                <div class="control-panel__heading">
                    <div><span>Phân bổ trạng thái</span><h2>Đơn đăng ký</h2></div>
                    <strong>{{ number_format($stats['registrations']) }}</strong>
                </div>

                <div class="control-status-list">
                    <div>
                        <div><span><i class="fa-regular fa-clock" aria-hidden="true"></i> Chờ duyệt</span><strong>{{ $stats['pending_registrations'] }}</strong></div>
                        <div class="control-progress"><span class="is-pending" style="width: {{ $pendingPercent }}%"></span></div>
                        <small>{{ $stats['registrations'] ? $pendingPercent : 0 }}% tổng số đơn</small>
                    </div>
                    <div>
                        <div><span><i class="fa-regular fa-circle-check" aria-hidden="true"></i> Đã duyệt</span><strong>{{ $stats['approved_registrations'] }}</strong></div>
                        <div class="control-progress"><span class="is-confirmed" style="width: {{ $confirmedPercent }}%"></span></div>
                        <small>{{ $stats['registrations'] ? $confirmedPercent : 0 }}% tổng số đơn</small>
                    </div>
                    <div>
                        <div><span><i class="fa-regular fa-circle-xmark" aria-hidden="true"></i> Đã hủy</span><strong>{{ $stats['cancelled_registrations'] }}</strong></div>
                        <div class="control-progress"><span class="is-cancelled" style="width: {{ $cancelledPercent }}%"></span></div>
                        <small>{{ $stats['registrations'] ? $cancelledPercent : 0 }}% tổng số đơn</small>
                    </div>
                </div>
            </section>

            <section class="control-quick-actions">
                <div><span>Thao tác nhanh</span><h2>Tạo dữ liệu mới</h2></div>
                <a href="{{ route('admin.classes.create') }}"><span><i class="fa-solid fa-spa" aria-hidden="true"></i></span><p><strong>Thêm lớp Yoga</strong><small>Tạo lịch học mới</small></p><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <a href="{{ route('admin.registrations.create') }}"><span><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span><p><strong>Tạo đơn đăng ký</strong><small>Nhập đơn tại quầy</small></p><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <a href="{{ route('admin.teachers') }}"><span><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span><p><strong>Quản lý giáo viên</strong><small>Cập nhật đội ngũ</small></p><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </section>
        </aside>
    </div>
</div>
@endsection
