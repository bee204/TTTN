@extends('layouts.admin')
@section('title', 'Chỉnh sửa '.$class->name.' - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-class-form.css') }}">@endpush
@section('content')
@php($isEdit = true)
@php($formAction = route('admin.classes.update', $class->id))
@php($cancelRoute = route('admin.classes.detail', $class->id))
@php($minimumQuantity = max(1, (int) $class->confirmed_registrations_count))
@include('admin.partials.class_form')
@endsection
@push('scripts')<script src="{{ asset('js/admin-class-form.js') }}"></script>@endpush
