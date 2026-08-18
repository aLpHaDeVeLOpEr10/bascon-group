@extends('layouts.admin')

@section('title', 'Profile · BASCON GROUP')

@section('breadcrumbs')
    <span data-crumb-current>Profile</span>
@endsection

@php
    $initials = collect(preg_split('/\s+/', trim((string) ($admin->name ?? ''))))
        ->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') ?: '?';
@endphp
@section('content')
<x-page-header title="Profile" subtitle="Your account and profile picture." />

@include('partials.profile-photo', [
    'endpoint' => url('admin_setting/profile_photo'),
    'name'     => $admin->name,
    'initials' => $initials,
    'current'  => $admin->avatar_url,
    'reviewed' => false,
])
@endsection
