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
                    <h3>{{ $class->name }}</h3>
                    <p>📅 {{ $class->lich_hoc }} | ⏰ {{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}</p>
                    <p>📍 {{ $class->location }}</p>
                </div>
                <div class="class-stats">
                    <div class="stat-number">{{ $class->registrations_count }}</div>
                    <div class="stat-label">Học viên đã duyệt</div>
                </div>
            </div>
            <div class="class-actions">
                <a href="{{ route('teacher.classes.attendance', $class->id) }}" class="action-btn view-btn">✅ Điểm danh</a>
            </div>
        </div>
    @empty
        <div class="empty-state"><h3>Chưa có lớp được phân công</h3></div>
    @endforelse
</div>
@endsection