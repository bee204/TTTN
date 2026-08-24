@extends('layouts.admin')
@section('title','Chỉnh sửa '.$customer->name.' - VITA Control')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-customer-form.css') }}">@endpush
@section('content')
@php($isEdit=true) @php($formAction=route('admin.customers.update',$customer->id)) @php($cancelRoute=route('admin.customers.detail',$customer->id))
@include('admin.partials.customer_form')
@endsection
@push('scripts')<script src="{{ asset('js/admin-customer-form.js') }}"></script>@endpush
