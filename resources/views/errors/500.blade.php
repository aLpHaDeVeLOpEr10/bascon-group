@extends('errors.layout', [
    'code' => 500,
    'tone' => 'bg-rose-50 text-rose-600',
    'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
])

@section('heading', 'Something went wrong')

@section('message')
    An unexpected error occurred on our side. The team has been notified — please
    try again in a moment.
@endsection
