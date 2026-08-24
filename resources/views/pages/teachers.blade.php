@extends('layouts.app')

@section('title', 'Giáo viên Yoga - VITA Yoga Center')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teachers.css') }}">
@endpush

@section('content')
<div class="teachers-page">
    <section class="teachers-hero">
        <div>
            <span class="teachers-eyebrow"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Đội ngũ tại VITA</span>
            <h1>Gặp gỡ những người<br><span>đồng hành cùng bạn.</span></h1>
            <p>Tìm hiểu kinh nghiệm và các lớp đang phụ trách trước khi chọn giáo viên Yoga phù hợp.</p>
        </div>
        <div class="teachers-hero__count">
            <strong>{{ number_format($teachers->total()) }}</strong>
            <span>Giáo viên Yoga</span>
            <small>Dữ liệu hiện có tại trung tâm</small>
        </div>
    </section>

    @if($teachers->isNotEmpty())
        <section class="teachers-toolbar" aria-label="Tìm kiếm giáo viên">
            <div class="teachers-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <label for="teacherSearch" class="sr-only">Tìm giáo viên trong trang hiện tại</label>
                <input type="search" id="teacherSearch" placeholder="Tìm theo tên hoặc số năm kinh nghiệm..." autocomplete="off">
                <kbd>Ctrl K</kbd>
            </div>
            <div class="teachers-toolbar__meta">
                <span id="teacherResultCount">Đang hiển thị {{ $teachers->count() }} giáo viên</span>
                <small>Tìm trong trang hiện tại</small>
            </div>
        </section>
    @endif

    <section class="teachers-grid" id="teachersGrid" aria-live="polite">
        @forelse($teachers as $teacher)
            @php
                $hasAvatar = $teacher->avatar && file_exists(public_path('storage/' . $teacher->avatar));
                $initial = mb_strtoupper(mb_substr($teacher->name, 0, 1));
            @endphp
            <article class="teacher-vita-card teacher-vita-card--tone-{{ ($teacher->id % 3) + 1 }}" data-teacher-card data-search="{{ $teacher->name }} {{ $teacher->exp_year }} năm kinh nghiệm">
                <div class="teacher-vita-card__visual">
                    <span class="teacher-vita-card__number">GV {{ str_pad((string) $teacher->id, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="teacher-vita-card__avatar">
                        @if($hasAvatar)
                            <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Ảnh giáo viên {{ $teacher->name }}">
                        @else
                            <span>{{ $initial }}</span>
                        @endif
                    </div>
                    <span class="teacher-vita-card__role">Giáo viên Yoga</span>
                </div>

                <div class="teacher-vita-card__body">
                    <h2>{{ $teacher->name }}</h2>
                    <p>{{ $teacher->description ?: 'Thông tin giới thiệu của giáo viên đang được cập nhật.' }}</p>

                    <div class="teacher-vita-card__stats">
                        <div><i class="fa-solid fa-award" aria-hidden="true"></i><span><strong>{{ $teacher->exp_year }}</strong><small>Năm kinh nghiệm</small></span></div>
                        <div><i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span><strong>{{ $teacher->classes_count }}</strong><small>Lớp phụ trách</small></span></div>
                    </div>
                </div>

                <footer class="teacher-vita-card__footer">
                    <span>Xem kinh nghiệm và lịch dạy</span>
                    <a href="{{ route('teacher.detail', $teacher->id) }}" aria-label="Xem hồ sơ giáo viên {{ $teacher->name }}">
                        Xem hồ sơ <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </footer>
            </article>
        @empty
            <div class="teachers-empty-state">
                <span><i class="fa-solid fa-seedling" aria-hidden="true"></i></span>
                <h2>Chưa có giáo viên nào</h2>
                <p>Thông tin đội ngũ giáo viên đang được cập nhật.</p>
            </div>
        @endforelse
    </section>

    <div class="teachers-no-results" id="teacherNoResults" hidden>
        <span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
        <h2>Không tìm thấy giáo viên phù hợp</h2>
        <p>Hãy thử tìm bằng một tên hoặc số năm kinh nghiệm khác.</p>
        <button type="button" id="clearTeacherSearch">Xóa tìm kiếm</button>
    </div>

    @if($teachers->hasPages())
        <nav class="teachers-pagination" id="teachersPagination" aria-label="Phân trang giáo viên">
            @if($teachers->onFirstPage())
                <span class="is-disabled"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</span>
            @else
                <a href="{{ $teachers->previousPageUrl() }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Trang trước</a>
            @endif
            <span class="teachers-pagination__current">Trang {{ $teachers->currentPage() }} / {{ $teachers->lastPage() }}</span>
            @if($teachers->hasMorePages())
                <a href="{{ $teachers->nextPageUrl() }}">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            @else
                <span class="is-disabled">Trang sau <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            @endif
        </nav>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/teachers.js') }}"></script>
@endpush
