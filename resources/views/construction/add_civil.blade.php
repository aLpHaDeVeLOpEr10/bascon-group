@extends('layouts.app')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Civil Materials</span>
@endsection

@section('content')
    {{-- Body shared with the admin side — see partials/category/civil. --}}
    @include('partials.category.civil', ['prefix' => 'construction'])
@endsection
