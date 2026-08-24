@extends('layouts.admin')
@section('title','Thêm học viên - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-customer-form.css') }}">@endpush
@section('content')
@php($isEdit=false) @php($customer=null) @php($formAction=route('admin.customers.store')) @php($cancelRoute=route('admin.customers'))
@include('admin.partials.customer_form')
@endsection
@push('scripts')<script src="{{ asset('js/admin-customer-form.js') }}"></script>@endpush
