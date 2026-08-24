@extends('layouts.admin')
@section('title','Chỉnh sửa '.$teacher->name.' - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-teacher-form.css') }}">@endpush
@section('content')@php($isEdit=true) @php($formAction=route('admin.teachers.update',$teacher->id)) @php($cancelRoute=route('admin.teachers.detail',$teacher->id)) @include('admin.partials.teacher_form')@endsection
@push('scripts')<script src="{{ asset('js/admin-teacher-form.js') }}"></script>@endpush
