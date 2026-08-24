@extends('layouts.admin')
@section('title', 'Tạo lớp Yoga - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-class-form.css') }}">@endpush
@section('content')
@php($isEdit = false)
@php($class = null)
@php($formAction = route('admin.classes.store'))
@php($cancelRoute = route('admin.classes'))
@php($minimumQuantity = 1)
@include('admin.partials.class_form')
@endsection
@push('scripts')<script src="{{ asset('js/admin-class-form.js') }}"></script>@endpush
