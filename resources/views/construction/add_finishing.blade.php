@extends('layouts.app')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Finishing Materials</span>
@endsection

@section('content')
    {{-- Body shared with the admin side — see partials/category/finishing. --}}
    @include('partials.category.finishing', ['prefix' => 'construction'])
@endsection
