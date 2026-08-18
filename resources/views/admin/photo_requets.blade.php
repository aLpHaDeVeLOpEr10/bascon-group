@extends('layouts.admin')

@section('title', 'Photo Requests · BASCON GROUP')

@section('breadcrumbs')
    <span>Request</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Photo Request</span>
@endsection

@section('content')
<x-page-header title="Profile Picture Requests"
               subtitle="Pictures a worker has uploaded that are waiting on you. An approved picture replaces theirs; a rejected one is discarded and they keep what they had." />

@if ($pending->isEmpty())
    <x-card>
        <x-empty-state title="Nothing waiting"
                       message="Every profile picture has been reviewed." />
    </x-card>
@else
    {{-- Cards rather than a table: the picture is the thing being judged, so
         it has to be big enough to actually look at. --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($pending as $user)
            <x-card :id="'photo-' . $user->id">
                <div class="flex items-start gap-4">
                    {{-- A button, not a bare image: judging a photo from a 96px
                         thumbnail is guesswork, so it opens full size. Keyboard
                         reachable for the same reason every other control is. --}}
                    <button type="button" class="ui-avatar-lg is-pending is-zoomable"
                            data-photo="{{ $user->pending_avatar_url }}"
                            data-photo-name="{{ $user->name }}"
                            title="View full size"
                            aria-label="View {{ $user->name }}'s picture full size">
                        <img src="{{ $user->pending_avatar_url }}"
                             alt="Picture uploaded by {{ $user->name }}">
                        <span class="ui-avatar-zoom" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
                                <path d="M11 8v6" /><path d="M8 11h6" />
                            </svg>
                        </span>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[14px] font-semibold text-neutral-900">{{ $user->name }}</p>
                        <p class="truncate text-[12px] text-neutral-500">{{ $user->email }}</p>
                        <p class="mt-1 text-[11.5px] text-neutral-400">{{ $user->role ?: 'Worker' }}</p>

                        <div class="ui-table-actions mt-4">
                            <button type="button" class="ui-btn ui-btn-sm ui-btn-primary"
                                    onclick="decidePhoto({{ $user->id }}, true)">Approve</button>
                            <button type="button" class="ui-btn ui-btn-sm ui-btn-danger-soft"
                                    onclick="decidePhoto({{ $user->id }}, false)">Reject</button>
                        </div>
                    </div>
                </div>
            </x-card>
        @endforeach
    </div>
@endif
{{-- Full-size viewer. Built on the app's own .modal, so it inherits the
     backdrop, the Esc handling and the focus trap. --}}
<div class="modal" id="photoModal" tabindex="-1" role="dialog"
     aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="photoModalLabel">Profile picture</h2>
                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body text-center">
                <img id="photoModalImage" src="" alt="" class="ui-photo-full">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Delegated, so a card added or removed later still works.
        $(document).on('click', '[data-photo]', function () {
            $('#photoModalImage')
                .attr('src', $(this).data('photo'))
                .attr('alt', $(this).data('photo-name') + "'s profile picture");

            $('#photoModalLabel').text($(this).data('photo-name'));
            $('#photoModal').modal('show');
        });
    });

    function decidePhoto(userId, approve) {
        Swal.fire({
            title: approve ? 'Approve this picture?' : 'Reject this picture?',
            text: approve
                ? 'It will replace their current profile picture.'
                : 'It will be discarded and they keep their current picture.',
            icon: approve ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: approve ? 'Approve' : 'Reject'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: approve
                    ? "{{ url('admin_setting/accept_photo') }}"
                    : "{{ url('admin_setting/reject_photo') }}",
                type: 'POST',
                data: { userId: userId },
                dataType: 'json',
                success: function (response) {
                    if (!response.success) {
                        Swal.fire('Error', 'Could not update the picture.', 'error');
                        return;
                    }

                    // Drop the card rather than reloading, so reviewing a
                    // queue of them does not reload the page each time.
                    $('#photo-' + userId).fadeOut(180, function () {
                        $(this).remove();
                        if (!$('[id^="photo-"]').length) window.location.reload();
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Error in AJAX request. Please try again later.', 'error');
                }
            });
        });
    }
</script>
@endpush
