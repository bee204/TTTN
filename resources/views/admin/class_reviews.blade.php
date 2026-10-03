@extends('layouts.admin')

@section('title', 'Đánh giá lớp học')

@section('content')
<div class="page-header">
    <a href="{{ Auth::user()->role === 'teacher' ? route('teacher.dashboard') : route('admin.classes.detail', $class->id) }}">← Quay lại lớp học</a>
    <h1>Đánh giá: {{ $class->name }}</h1>
    <div class="rating-summary">
        <span class="summary-stars">{{ str_repeat('★', (int) round($reviews->avg('rating') ?? 0)) }}{{ str_repeat('☆', 5 - (int) round($reviews->avg('rating') ?? 0)) }}</span>
        <strong>{{ number_format($reviews->avg('rating') ?? 0, 2) }}/5</strong>
        <span>({{ $reviews->count() }} đánh giá)</span>
    </div>
</div>

<div class="review-list">
@forelse($reviews as $review)
    <article class="review-row">
        <div><strong>{{ $review->customer->name }}</strong><small>{{ $review->created_at->format('d/m/Y H:i') }}</small></div>
        <div class="stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
        <p>{{ $review->comment ?: 'Không có nhận xét.' }}</p>
        @if(Auth::user()->role === 'admin')
            <form method="POST" action="{{ route('admin.classes.reviews.destroy', [$class->id, $review->id]) }}" data-confirm-title="Xác nhận xóa đánh giá" data-confirm-message="Bạn có chắc chắn muốn xóa đánh giá này? Hành động này không thể hoàn tác.">
                @csrf
                @method('DELETE')
                <button type="submit" class="review-delete-button">Xóa đánh giá</button>
            </form>
        @endif
    </article>
@empty
    <p>Chưa có đánh giá nào cho lớp học này.</p>
@endforelse
</div>
@endsection

@push('styles')
<style>
.page-header,.review-row{background:#fff;padding:20px;border-radius:10px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.08)}.page-header h1{margin:12px 0 4px}.rating-summary{display:flex;align-items:center;gap:10px;margin-top:12px;color:#555}.summary-stars,.stars{color:#f59f00;font-size:1.35rem;letter-spacing:2px}.review-row small{display:block;color:#777;margin-top:4px}.stars{margin-top:10px}.review-row p{margin-bottom:0;color:#444}.review-delete-button{margin-top:12px;padding:8px 12px;border:0;border-radius:5px;background:#b42318;color:#fff;cursor:pointer}.review-delete-button:hover{background:#912018}
</style>
@endpush
