@extends('layouts.admin')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Labour</span>
@endsection

@section('content')
    {{-- Body shared with the construction side — see partials/category/labour. --}}
    @include('partials.category.labour', ['prefix' => 'admin_setting'])
@endsection
