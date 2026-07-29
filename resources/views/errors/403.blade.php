@extends('errors.layout', [
    'code' => 403,
    'tone' => 'bg-amber-50 text-amber-600',
    'icon' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
])

@section('heading', 'Access denied')

@section('message')
    Your account doesn't have permission to view this page. If you think that's
    wrong, ask an administrator to check your role.
@endsection
