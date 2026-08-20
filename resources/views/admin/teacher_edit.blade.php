@extends('layouts.admin')

@section('title', 'Chỉnh sửa Giảng viên - Admin')

@section('content')
<div class="page-header">
    <div class="header-navigation">
        <a href="{{ route('admin.teachers') }}" class="back-btn">
            ← Quay lại danh sách
        </a>
        <a href="{{ route('admin.teachers.detail', $teacher->id) }}" class="view-btn">
            👁️ Xem chi tiết
        </a>
    </div>
    <div class="header-content">
        <h1>✏️ Chỉnh sửa Giảng viên</h1>
        <p>Cập nhật thông tin: <strong>{{ $teacher->name }}</strong></p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        ✅ {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        ❌ Vui lòng kiểm tra lại thông tin:
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-container">
    <form method="POST" action="{{ route('admin.teachers.update', $teacher->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-grid">
            <div class="form-group">
                <label for="name">👤 Tên giảng viên <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $teacher->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">📧 Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $teacher->email) }}" required>
            </div>

            <div class="form-group">
                <label for="phone">📱 Số điện thoại <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}" required>
            </div>

            <div class="form-group">
                <label for="birthday">🎂 Ngày sinh</label>
                <input type="date" id="birthday" name="birthday" value="{{ old('birthday', $teacher->birthday ? $teacher->birthday->format('Y-m-d') : '') }}">
            </div>

            <div class="form-group">
                <label for="exp_year">📈 Số năm kinh nghiệm <span class="required">*</span></label>
                <input type="number" id="exp_year" name="exp_year" value="{{ old('exp_year', $teacher->exp_year) }}" min="0" required>
            </div>

            <div class="form-group">
                <label for="level">🏆 Trình độ</label>
                <select id="level" name="level">
                    <option value="">-- Chọn trình độ --</option>
                    <option value="Beginner" {{ old('level', $teacher->level) == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="Intermediate" {{ old('level', $teacher->level) == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="Advanced" {{ old('level', $teacher->level) == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    <option value="Expert" {{ old('level', $teacher->level) == 'Expert' ? 'selected' : '' }}>Expert</option>
                </select>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="description">📝 Mô tả về giảng viên</label>
            <textarea id="description" name="description" rows="4" placeholder="Mô tả về kinh nghiệm, chuyên môn của giảng viên...">{{ old('description', $teacher->description) }}</textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="account_password">🔐 Mật khẩu mới</label>
                <input type="password" id="account_password" name="account_password" minlength="6">
            </div>
            <div class="form-group">
                <label for="account_password_confirmation">🔐 Nhập lại mật khẩu</label>
                <input type="password" id="account_password_confirmation" name="account_password_confirmation" minlength="6">
            </div>
        </div>
        <p class="account-hint">Nếu tài khoản teacher chưa tồn tại, nhập mật khẩu để tạo mới. Để trống để giữ nguyên mật khẩu hiện tại.</p>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                💾 Cập nhật giảng viên
            </button>
            <a href="{{ route('admin.teachers.detail', $teacher->id) }}" class="btn btn-secondary">
                ❌ Hủy bỏ
            </a>
        </div>
    </form>
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
    display: flex;
    gap: 10px;
}

.back-btn, .view-btn {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 6px;
    background: #f8f9fa;
    transition: all 0.2s;
}

.back-btn:hover, .view-btn:hover {
    background: #e9ecef;
}

.header-content h1 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 5px;
}

.header-content p {
    color: #666;
    margin: 0;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert ul {
    margin: 10px 0 0 20px;
}

.form-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.required {
    color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #f8f9fa;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    color: white;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.full-width {
        grid-column: span 1;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .header-navigation {
        flex-direction: column;
    }
}
</style>
@endpush
@endsection