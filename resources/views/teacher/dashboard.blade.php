@extends('layouts.admin')

@section('title', 'Khu vực giảng viên')

@section('content')
<div class="page-header">
    <h1>👨‍🏫 Khu vực giảng viên</h1>
    <p>Xin chào {{ $teacher->name }}. Đây là các lớp bạn đang phụ trách.</p>
</div>

<div class="classes-container">
    @forelse($classes as $class)
        <div class="class-card">
            <div class="class-main">
                <div class="class-info">
                    <h3><a href="{{ route('teacher.classes.detail', $class->id) }}">{{ $class->name }}</a></h3>
                    <p>📅 {{ $class->lich_hoc }} | ⏰ {{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}</p>
                    <p>📍 {{ $class->location }}</p>
                </div>
                <div class="class-stats">
                    <div class="stat-number">{{ $class->registrations_count }}</div>
                    <div class="stat-label">Học viên đã duyệt</div>
                </div>
            </div>
            <div class="class-actions">
                @if($class->start_date->isFuture())
                    <button type="button" class="action-btn view-btn" disabled title="Chưa đến ngày bắt đầu lớp học">⏳ Chưa bắt đầu</button>
                @else
                    <a href="{{ route('teacher.classes.attendance', $class->id) }}" class="action-btn view-btn">✅ Điểm danh</a>
                @endif
                <a href="{{ route('teacher.classes.reviews', $class->id) }}" class="action-btn review-btn">⭐ Xem đánh giá</a>
            </div>
        </div>
    @empty
        <div class="empty-state"><h3>Chưa có lớp được phân công</h3></div>
    @endforelse
</div>
@endsection

@push('styles')
<style>
.teacher-dashboard-header {
    margin-bottom: 24px;
}

.teacher-dashboard-header h1 {
    margin-bottom: 8px;
}

.classes-container {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 18px;
    max-width: 1100px;
}

.classes-container .class-card {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 24px;
    padding: 24px 28px;
    background: #fff;
    border: 1px solid #e2e6ea;
    border-left: 5px solid #667eea;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(31, 41, 55, .08);
}

.class-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex: 1;
    min-width: 0;
}

.class-info {
    min-width: 0;
}

.class-info h3 {
    margin: 0 0 12px;
    color: #253047;
    font-size: 1.3rem;
}

.class-info p {
    margin: 7px 0;
    color: #657083;
}

.class-stats {
    flex: 0 0 150px;
    padding: 12px 18px;
    border-left: 1px solid #e9ecef;
    text-align: center;
}

.stat-number {
    color: #667eea;
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    margin-top: 7px;
    color: #6c757d;
    font-size: .85rem;
}

.class-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex: 0 0 150px;
    padding-left: 24px;
    border-left: 1px solid #e9ecef;
}

.class-actions .action-btn {
    width: 100%;
    padding: 11px 14px;
    border: 0;
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
}

.class-actions .view-btn {
    background: #667eea;
    color: #fff;
}

.class-actions .view-btn:hover {
    background: #5568d8;
}

.class-actions .review-btn {
    background: #f59f00;
    color: #fff;
}

.class-actions .review-btn:hover {
    background: #d98700;
}

.class-actions .view-btn:disabled {
    background: #adb5bd;
    cursor: not-allowed;
}

.empty-state {
    padding: 48px 24px;
    background: #fff;
    border: 1px dashed #cbd3dc;
    border-radius: 10px;
    color: #6c757d;
    text-align: center;
}

@media (max-width: 700px) {
    .classes-container .class-card,
    .class-main {
        flex-direction: column;
        align-items: stretch;
    }

    .classes-container .class-card {
        gap: 16px;
        padding: 20px;
    }

    .class-stats,
    .class-actions {
        flex-basis: auto;
        padding: 14px 0 0;
        border-top: 1px solid #e9ecef;
        border-left: 0;
    }

    .class-actions {
        gap: 10px;
    }
}
</style>
@endpush