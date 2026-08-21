@extends('layouts.app')
@section('title', 'Thông tin tài khoản')
@section('content')
<div class="auth-form-container">
    <h2>Thông tin tài khoản</h2>
    <div class="form-group">
        <strong>Họ và tên</strong>
        <p>{{ $user->name }}</p>
    </div>
    <div class="form-group">
        <strong>Email</strong>
        <p>{{ $user->email }}</p>
    </div>
    <div class="form-group">
        <strong>Số điện thoại</strong>
        <p>{{ $user->customer?->phone ?? 'Chưa cập nhật' }}</p>
    </div>
</div>
@endsection
@push('styles')
<style>
.auth-form-container { max-width: 460px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.auth-form-container h2 { margin-bottom: 24px; }
.auth-form-container p { margin: 6px 0 18px; color: #555; }
</style>
@endpush
