@extends('layouts.admin')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Finishing Materials</span>
@endsection

@section('content')
    {{-- Body shared with the construction side — see partials/category/finishing. --}}
    @include('partials.category.finishing', ['prefix' => 'admin_setting'])
@endsection
