@extends('layouts.admin')

@section('title', 'Điểm danh lớp học')

@section('content')
<div class="page-header">
    <a href="{{ Auth::user()->role === 'teacher' ? route('teacher.dashboard') : route('admin.classes.detail', $class->id) }}">← Quay lại lớp học</a>
    <h1>Điểm danh: {{ $class->name }}</h1>
    <p>{{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="attendance-list">
@forelse($registrations as $registration)
    <form method="POST" action="{{ Auth::user()->role === 'teacher' ? route('teacher.registrations.attendance.store', $registration->id) : route('admin.registrations.attendance.store', $registration->id) }}" class="attendance-row">
        @csrf
        <div>
            <strong>{{ $registration->customer->name }}</strong>
            <small>{{ $registration->customer->email }}</small>
        </div>
        <label>Ngày học
            <input type="date" name="attendance_date" value="{{ old('attendance_date', now()->format('Y-m-d')) }}" min="{{ $class->start_date->format('Y-m-d') }}" max="{{ min($class->end_date->format('Y-m-d'), now()->format('Y-m-d')) }}" required>
        </label>
        <label>Trạng thái
            <select name="status">
                @foreach(['PRESENT' => 'Có mặt', 'LATE' => 'Đi muộn', 'ABSENT' => 'Vắng', 'EXCUSED' => 'Có phép'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">Lưu</button>
        <div class="history">
            @foreach($registration->attendances->take(5) as $attendance)
                <span>{{ $attendance->attendance_date->format('d/m') }}: {{ $attendance->status }}</span>
            @endforeach
        </div>
    </form>
@empty
    <p>Chưa có học viên được duyệt trong lớp.</p>
@endforelse
</div>
@endsection

@push('styles')
<style>
.page-header,.attendance-row{background:#fff;padding:20px;border-radius:10px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.page-header h1{margin:12px 0 4px}.attendance-row{display:grid;grid-template-columns:1.2fr 1fr 1fr 1.2fr auto;gap:12px;align-items:end}.attendance-row small{display:block;color:#777;margin-top:4px}.attendance-row label{display:grid;gap:5px;font-size:.85rem}.attendance-row input,.attendance-row select{padding:9px;border:1px solid #ddd;border-radius:5px}.attendance-row button{padding:10px 16px;border:0;border-radius:5px;background:#198754;color:#fff}.history{grid-column:1/-1;display:flex;gap:8px;flex-wrap:wrap;color:#666;font-size:.85rem}.history span{background:#f1f3f5;padding:4px 8px;border-radius:4px}.alert{padding:12px;margin-bottom:16px;border-radius:6px}.alert-success{background:#d1e7dd}.alert-error{background:#f8d7da}@media(max-width:800px){.attendance-row{grid-template-columns:1fr 1fr}.history{grid-column:1/-1}}
</style>
@endpush
