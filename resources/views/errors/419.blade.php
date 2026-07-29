@extends('errors.layout', [
    'code' => 419,
    'tone' => 'bg-amber-50 text-amber-600',
    'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
])

@section('heading', 'Your session expired')

@section('message')
    You were signed out after a period of inactivity. Sign in again and your work
    will pick up where it left off.
@endsection
