@extends('errors.layout', [
    'code' => 404,
    'tone' => 'bg-sky-50 text-sky-600',
    'icon' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
])

@section('heading', 'Page not found')

@section('message')
    The page you were looking for doesn't exist, or the record it pointed at has
    since been removed.
@endsection
