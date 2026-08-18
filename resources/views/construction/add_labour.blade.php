@extends('layouts.app')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Labour</span>
@endsection

@section('content')
    {{-- Body shared with the admin side — see partials/category/labour. --}}
    @include('partials.category.labour', ['prefix' => 'construction'])
@endsection
