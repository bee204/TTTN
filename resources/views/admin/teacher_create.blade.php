@extends('layouts.admin')
@section('title','Thêm giáo viên - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-teacher-form.css') }}">@endpush
@section('content')@php($isEdit=false) @php($teacher=null) @php($formAction=route('admin.teachers.store')) @php($cancelRoute=route('admin.teachers')) @include('admin.partials.teacher_form')@endsection
@push('scripts')<script src="{{ asset('js/admin-teacher-form.js') }}"></script>@endpush
