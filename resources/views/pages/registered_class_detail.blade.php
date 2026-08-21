@extends('layouts.app')

@section('title', 'Chi tiết lớp học đã đăng ký')

@section('content')
<h1 class="page-title">🧘‍♀️ {{ $class->name }}</h1>
<div class="class-detail">
    <div class="teacher-info">
        <h3>👨‍🏫 Giảng viên</h3>
        <div class="card">
            <div class="card-body">
                <h5>{{ $class->teacher->name }}</h5>
                <p>{{ $class->teacher->description }}</p>
                <p>Email: {{ $class->teacher->email }}</p>
                <p>Phone: {{ $class->teacher->phone }}</p>
            </div>
        </div>
    </div>
    @if($review)
        <div class="review-form review-readonly" style="margin-top: 30px;">
            <h3>⭐ Đánh giá của bạn</h3>
            <div class="selected-stars" aria-label="{{ $review->rating }} trên 5 sao">
                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
            </div>
            <p>{{ $review->comment ?: 'Không có nhận xét.' }}</p>
            <small>Bạn đã gửi đánh giá cho lớp học này.</small>
        </div>
    @else
    <div class="review-form" style="margin-top: 30px;">
        <h3>⭐ Đánh giá lớp học</h3>
        <form method="POST" action="{{ route('registered.class.review', $class->id) }}">
            @csrf
            <fieldset class="star-rating">
                <legend>Chọn mức đánh giá</legend>
                @for($rating = 5; $rating >= 1; $rating--)
                    <input type="radio" id="rating-{{ $rating }}" name="rating" value="{{ $rating }}" {{ optional($review)->rating === $rating ? 'checked' : '' }} required>
                    <label for="rating-{{ $rating }}" title="{{ $rating }} sao">★</label>
                @endfor
            </fieldset>
            <label for="comment">Nhận xét của bạn</label>
            <textarea id="comment" name="comment" rows="4" maxlength="2000">{{ old('comment', optional($review)->comment) }}</textarea>
            <button type="submit" class="btn btn-primary">{{ $review ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}</button>
        </form>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.review-form { max-width: 640px; padding: 24px; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.review-form form { display: grid; gap: 10px; }
.review-form label { font-weight: 600; color: #444; }
.review-form textarea { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; resize: vertical; }
.star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 3px; border: 0; padding: 0; margin: 0 0 8px; }
.star-rating legend { width: 100%; margin-bottom: 5px; font-weight: 600; color: #444; }
.star-rating input { position: absolute; opacity: 0; pointer-events: none; }
.star-rating label { color: #ced4da; font-size: 2.2rem; line-height: 1; cursor: pointer; transition: color .15s ease; }
.star-rating label:hover, .star-rating label:hover ~ label, .star-rating input:checked ~ label { color: #f59f00; }
.star-rating input:focus-visible + label { outline: 2px solid #667eea; outline-offset: 3px; }
.selected-stars { color: #f59f00; font-size: 2.2rem; letter-spacing: 2px; margin: 8px 0; }
.review-readonly p { margin: 8px 0; color: #444; }
.review-readonly small { color: #6c757d; }
</style>
@endpush
