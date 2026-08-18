@extends('layouts.admin')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Civil Materials</span>
@endsection

@section('content')
    {{-- Body shared with the construction side — see partials/category/civil. --}}
    @include('partials.category.civil', ['prefix' => 'admin_setting'])
@endsection
