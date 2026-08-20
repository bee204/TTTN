@extends('layouts.admin')

@section('title', 'Chi tiết Lớp học - Admin')

@section('content')
<div class="page-header">
    <div class="header-navigation">
        <a href="{{ route('admin.classes') }}" class="back-btn">
            ← Quay lại danh sách
        </a>
    </div>
    <div class="header-content">
        <h1>🧘‍♀️ Chi tiết Lớp học: {{ $class->name }}</h1>
    </div>
</div>

<div class="detail-container">
    <div class="detail-grid">
        <!-- Class Information -->
        <div class="detail-section">
            <div class="section-header">
                <h2>📋 Thông tin lớp học</h2>
            </div>
            <div class="section-content">
                <div class="info-row">
                    <span class="info-label">🧘‍♀️ Tên lớp:</span>
                    <span class="info-value">{{ $class->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">👨‍🏫 Giảng viên:</span>
                    <span class="info-value">{{ $class->teacher->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕒 Lịch học:</span>
                    <span class="info-value">{{ $class->lich_hoc ?? 'Chưa xác định' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">⏰ Thời gian:</span>
                    <span class="info-value">{{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Thời gian học:</span>
                    <span class="info-value">{{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">👥 Sức chứa:</span>
                    <span class="info-value">{{ $class->quantity }} học viên</span>
                </div>
                <div class="info-row">
                    <span class="info-label">💰 Giá:</span>
                    <span class="info-value">{{ number_format($class->price) }} VNĐ</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📍 Địa điểm:</span>
                    <span class="info-value">{{ $class->location }}</span>
                </div>
                @if($class->description)
                    <div class="info-row">
                        <span class="info-label">📝 Mô tả:</span>
                        <span class="info-value">{{ $class->description }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Student List -->
        <div class="detail-section">
            <div class="section-header">
                <h2>👥 Danh sách học viên ({{ $registrations->count() }}/{{ $class->quantity }})</h2>
            </div>
            <div class="section-content">
                @forelse($registrations as $registration)
                    <div class="student-item">
                        <div class="student-avatar">
                            {{ substr($registration->customer->name, 0, 1) }}
                        </div>
                        <div class="student-info">
                            <div class="student-name">
                                {{ $registration->customer->name }}
                                <span class="status-badge confirmed">✅ Đã xác nhận</span>
                            </div>
                            <div class="student-contact">
                                📧 {{ $registration->customer->email }} | 
                                📱 {{ $registration->customer->phone }}
                            </div>
                            <div class="registration-date">
                                📅 Đăng ký: {{ $registration->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>📭 Chưa có học viên nào được xác nhận tham gia lớp học này</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('admin.classes.attendance', $class->id) }}" class="action-btn edit-btn">
            ✅ Điểm danh
        </a>
        <a href="{{ route('admin.classes.reviews', $class->id) }}" class="action-btn secondary-btn">
            ⭐ Xem đánh giá
        </a>
        <a href="{{ route('admin.classes.edit', $class) }}" class="action-btn edit-btn">
            ✏️ Chỉnh sửa lớp học
        </a>
        <a href="{{ route('admin.classes') }}" class="action-btn secondary-btn">
            📋 Quay lại danh sách
        </a>
    </div>
</div>

@push('styles')
<style>
.page-header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.header-navigation {
    margin-bottom: 15px;
}

.back-btn {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 6px;
    background: #f8f9fa;
    transition: all 0.2s;
}

.back-btn:hover {
    background: #e9ecef;
}

.header-content h1 {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.detail-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.detail-section {
    padding: 30px;
    border-right: 1px solid #f8f9fa;
}

.detail-section:last-child {
    border-right: none;
}

.section-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.section-header h2 {
    color: #333;
    font-size: 1.3rem;
    margin: 0;
}

.info-row {
    display: flex;
    margin-bottom: 15px;
    align-items: flex-start;
}

.info-label {
    min-width: 140px;
    font-weight: 600;
    color: #666;
    flex-shrink: 0;
}

.info-value {
    color: #333;
    flex: 1;
}

.student-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f8f9fa;
}

.student-item:last-child {
    border-bottom: none;
}

.student-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.student-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.status-badge.confirmed {
    background: #d4edda;
    color: #155724;
}

.student-contact {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 3px;
}

.registration-date {
    color: #999;
    font-size: 0.8rem;
}

.action-buttons {
    padding: 25px 30px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 15px;
}

.action-btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.edit-btn {
    background: #ffc107;
    color: #212529;
}

.edit-btn:hover {
    background: #e0a800;
    color: #212529;
}

.secondary-btn {
    background: #6c757d;
    color: white;
}

.secondary-btn:hover {
    background: #5a6268;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-section {
        border-right: none;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .detail-section:last-child {
        border-bottom: none;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
@endpush
@endsection