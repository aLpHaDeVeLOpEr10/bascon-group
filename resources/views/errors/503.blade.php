@extends('errors.layout', [
    'code' => 503,
    'tone' => 'bg-neutral-100 text-neutral-500',
    'icon' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/>',
])

@section('heading', 'Down for maintenance')

@section('message')
    The system is briefly offline while we ship an update. It'll be back
    shortly — no data is affected.
@endsection
