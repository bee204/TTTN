@extends('layouts.app')

@section('title', 'Danh sách lớp học - Yoga/Gym Center')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/classes.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/search.js') }}"></script>
@endpush

@section('content')
<h1 class="page-title">🏃‍♀️ Danh sách lớp học</h1>

<div class="search-box">
    <input type="text" id="searchClass" placeholder="Tìm kiếm lớp học..." onkeyup="searchClassFunc()">
</div>

<div class="class-grid" id="classGrid">
    @foreach($classes as $class)
    <div class="class-card" style="position: relative;">
        <!-- Full Tag - Top Right Corner -->
        @if($class->is_full)
            <div style="position: absolute; top: 10px; right: 10px; background: #dc3545; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; z-index: 10;">
                🚫 FULL
            </div>
        @endif
        
        <h3>{{ $class->name }}</h3>
        <p>{{ $class->description }}</p>
        
        <!-- Schedule Information -->
        <div class="schedule-info" style="background: #667eea; color: white; padding: 10px; border-radius: 8px; margin: 10px 0;">
            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                <span style="margin-right: 8px;">📅</span>
                <strong>Lịch học:</strong> {{ $class->lich_hoc }}
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                <span style="margin-right: 8px;">⏰</span>
                <strong>Giờ học:</strong> {{ $class->start_time }} - {{ $class->end_time }}
            </div>
            <div style="display: flex; align-items: center;">
                <span style="margin-right: 8px;">📆</span>
                <strong>Thời gian:</strong> {{ \Carbon\Carbon::parse($class->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($class->end_date)->format('d/m/Y') }}
            </div>
        </div>
        <div class="price">💰 {{ number_format($class->price ?? 500000, 0, ',', '.') }}đ</div>
        <div style="margin-top: 15px;">
            <span style="background: #51cf66; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                �‍🏫 {{ $class->teacher->name ?? 'Chưa có giảng viên' }}
            </span>
        </div>
        <div style="margin-top: 10px; display: flex; gap: 10px;">
            <a href="{{ route('class.detail', $class->id) }}" class="btn" style="flex: 1; background: #f8f9fa; color: #495057; text-align: center; white-space: nowrap; padding: 8px 12px;">👁️ Xem chi tiết</a>
            @if(isset($registrationStatuses[$class->id]))
                <button class="btn" style="flex: 1; text-align: center; white-space: nowrap; padding: 8px 12px; background: #6c757d; color: white; cursor: not-allowed;" disabled>
                    @if($registrationStatuses[$class->id]['has_attendance'])
                        🔵 Đang học
                    @elseif($registrationStatuses[$class->id]['status'] === 'CONFIRMED')
                        ✅ Đã duyệt
                    @else
                        ⏳ Đang chờ duyệt
                    @endif
                </button>
            @elseif($class->is_full)
                <button class="btn" style="flex: 1; text-align: center; white-space: nowrap; padding: 8px 12px; background: #6c757d; color: white; cursor: not-allowed;" disabled>📝 Đã hết chỗ</button>
            @else
                <a href="{{ route('register', ['class_id' => $class->id]) }}" class="btn btn-primary" style="flex: 1; text-align: center; white-space: nowrap; padding: 8px 12px;">📝 Đăng ký</a>
            @endif
        </div>
    </div>
    @endforeach
    
    @if($classes->isEmpty())
    <!-- Static classes when no database classes -->
    <div class="class-card">
        <h3>🌅 Yoga Sáng</h3>
        <p>Bắt đầu ngày mới với năng lượng tích cực qua các bài tập yoga nhẹ nhàng</p>
        <span class="time">⏰ 6:00 - 7:00</span>
        <div class="price">💰 500.000đ/tháng</div>
        <div style="margin-top: 15px;">
            <span style="background: #51cf66; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                👥 15/20 học viên
            </span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 15px; width: 100%; white-space: nowrap; padding: 10px 15px;">📝 Đăng ký ngay</a>
    </div>
    
    <div class="class-card">
        <h3>🌙 Yoga Tối</h3>
        <p>Thư giãn sau ngày làm việc căng thẳng với yoga thư giãn sâu</p>
        <span class="time">⏰ 18:00 - 19:00</span>
        <div class="price">💰 500.000đ/tháng</div>
        <div style="margin-top: 15px;">
            <span style="background: #ff6b6b; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                👥 18/20 học viên
            </span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 15px; width: 100%; white-space: nowrap; padding: 10px 15px;">📝 Đăng ký ngay</a>
    </div>
    
    <div class="class-card">
        <h3>💪 Gym</h3>
        <p>Tập luyện sức mạnh và thể lực với đầy đủ thiết bị hiện đại</p>
        <span class="time">⏰ 7:00 - 21:00</span>
        <div class="price">💰 400.000đ/tháng</div>
        <div style="margin-top: 15px;">
            <span style="background: #667eea; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                👥 Không giới hạn
            </span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 15px; width: 100%; white-space: nowrap; padding: 10px 15px;">📝 Đăng ký ngay</a>
    </div>
    
    <div class="class-card">
        <h3>🧘‍♀️ Yoga Cơ bản</h3>
        <p>Dành cho người mới bắt đầu, học các tư thế yoga cơ bản</p>
        <span class="time">⏰ 9:00 - 10:00</span>
        <div class="price">💰 450.000đ/tháng</div>
        <div style="margin-top: 15px;">
            <span style="background: #51cf66; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                👥 10/15 học viên
            </span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 15px; width: 100%; white-space: nowrap; padding: 10px 15px;">📝 Đăng ký ngay</a>
    </div>
    
    <div class="class-card">
        <h3>🧘‍♂️ Yoga Nâng cao</h3>
        <p>Dành cho học viên có kinh nghiệm, thực hành các tư thế phức tạp</p>
        <span class="time">⏰ 19:00 - 20:30</span>
        <div class="price">💰 600.000đ/tháng</div>
        <div style="margin-top: 15px;">
            <span style="background: #ffc107; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                👥 8/12 học viên
            </span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 15px; width: 100%; white-space: nowrap; padding: 10px 15px;">📝 Đăng ký ngay</a>
    </div>
    
    <div class="class-card">
        <h3>🤸‍♀️ Yoga Flow</h3>
        <p>Kết hợp nhiều tư thế trong dòng chảy liền mạch, tăng sức bền</p>
        <span class="time">⏰ 17:00 - 18:00</span>
        <div class="price">💰 550.000đ/tháng</div>
        <div style="margin-top: 15px;">
            <span style="background: #51cf66; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px;">
                👥 12/18 học viên
            </span>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 15px; width: 100%; white-space: nowrap; padding: 10px 15px;">📝 Đăng ký ngay</a>
    </div>
    @endif
</div>
@endsection
