@extends('layouts.admin')

@section('title', 'Đánh giá lớp học')

@section('content')
<div class="page-header">
    <a href="{{ route('admin.classes.detail', $class->id) }}">← Quay lại lớp học</a>
    <h1>Đánh giá: {{ $class->name }}</h1>
    <p>Điểm trung bình: <strong>{{ number_format($reviews->avg('rating') ?? 0, 2) }}/5</strong> ({{ $reviews->count() }} đánh giá)</p>
</div>

<div class="review-list">
@forelse($reviews as $review)
    <article class="review-row">
        <div><strong>{{ $review->customer->name }}</strong><small>{{ $review->created_at->format('d/m/Y H:i') }}</small></div>
        <div class="stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
        <p>{{ $review->comment ?: 'Không có nhận xét.' }}</p>
    </article>
@empty
    <p>Chưa có đánh giá nào cho lớp học này.</p>
@endforelse
</div>
@endsection

@push('styles')
<style>
.page-header,.review-row{background:#fff;padding:20px;border-radius:10px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.08)}.page-header h1{margin:12px 0 4px}.review-row small{display:block;color:#777;margin-top:4px}.stars{color:#f59f00;font-size:1.25rem;margin-top:10px}.review-row p{margin-bottom:0;color:#444}
</style>
@endpush
