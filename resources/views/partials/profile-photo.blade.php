{{--
    Profile picture panel — shared by the worker, client and admin pages.

    The three differ in exactly two things: where the form posts, and whether
    the upload is reviewed. Both arrive as parameters, so there is one copy of
    the markup and the upload behaves identically everywhere.

        $endpoint  URL to post to
        $name      display name
        $initials  fallback when there is no picture
        $current   URL of the live picture, or null
        $pending   URL of one awaiting approval, or null
        $status    User::AVATAR_* — only meaningful when $reviewed
        $reviewed  true when an admin has to approve it
--}}
@php
    $pending = $pending ?? null;
    $status = $status ?? null;
    $reviewed = $reviewed ?? false;
@endphp

<x-card title="Profile picture"
        :subtitle="$reviewed
            ? 'A new picture is shown to everyone once an administrator approves it.'
            : 'Your picture is shown as soon as you upload it.'">

    {{-- Two columns on a wide screen, stacked below: the pictures are what you
         are looking at, the control is what you act on. Previously both sat in
         one flex row with the form squeezed beside them, which is what made it
         read as a jumble. --}}
    <div class="grid gap-8 lg:grid-cols-[auto_minmax(0,1fr)]">

        {{-- what it looks like now --}}
        <div class="flex items-start gap-5">
            <figure class="m-0 text-center">
                <span class="ui-avatar-lg" data-avatar-preview>
                    @if ($current)
                        <img src="{{ $current }}" alt="{{ $name }}">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <figcaption class="mt-2 text-[11.5px] text-neutral-400">Current</figcaption>
            </figure>

            @if ($reviewed && $pending)
                <figure class="m-0 text-center">
                    <span class="ui-avatar-lg is-pending">
                        <img src="{{ $pending }}" alt="Picture awaiting approval">
                    </span>
                    <figcaption class="mt-2 text-[11.5px] font-medium text-amber-700">
                        Awaiting approval
                    </figcaption>
                </figure>
            @endif
        </div>

        {{-- and how to change it --}}
        <form id="avatarForm" action="{{ $endpoint }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div id="avatarMessage" class="ui-alert mb-4" role="status" aria-live="polite" hidden></div>

            @if ($reviewed && $status === \App\Models\User::AVATAR_REJECTED)
                <div class="ui-alert ui-alert-danger mb-4">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 9v4" /><path d="M12 17h.01" />
                        <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                    </svg>
                    <span>Your last picture was not approved. You can upload a different one.</span>
                </div>
            @endif

            {{-- The app's own dropzone, so this file input drags and drops and
                 reports its filename like every other one. --}}
            <label class="ui-dropzone" for="avatarInput">
                <svg class="size-6 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <path d="M7 10l5-5 5 5" /><path d="M12 5v12" />
                </svg>

                <span class="text-[13px] font-medium text-neutral-700">
                    Drop a picture here, or <span class="text-brand-700">browse</span>
                </span>
                <span class="text-[11.5px] text-neutral-400">JPG, PNG or WebP · up to 4 MB</span>
                <span class="mt-1 text-[12px] font-medium text-brand-700" data-file-name></span>

                <input type="file" name="photo" id="avatarInput" accept="image/*" required>
            </label>

            <div class="ui-form-actions">
                <button type="submit" class="ui-btn ui-btn-primary">
                    {{ $reviewed ? 'Send for approval' : 'Update picture' }}
                </button>
            </div>
        </form>
    </div>
</x-card>

@push('scripts')
<script>
    $(document).ready(function () {
        var input = document.getElementById('avatarInput');
        var preview = document.querySelector('[data-avatar-preview]');

        // Show the chosen file before it is sent, so a mistake is obvious
        // while it can still be corrected. initDropzones fills in the name.
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;

            preview.innerHTML = '<img alt="Selected picture">';
            preview.querySelector('img').src = URL.createObjectURL(file);
        });

        function message(tone, text) {
            var icons = {
                success: '<path d="M20 6 9 17l-5-5"/>',
                info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
                danger: '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>'
            };

            $('#avatarMessage')
                .attr('class', 'ui-alert ui-alert-' + tone + ' mb-4')
                .html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" ' +
                      'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + icons[tone] + '</svg>' +
                      '<span></span>')
                .find('span').text(text).end()
                .prop('hidden', false);
        }

        $('#avatarForm').submit(function (event) {
            event.preventDefault();

            var $submit = $(this).find('button[type="submit"]');
            if ($submit.prop('disabled')) return;

            // FormData, not serialize(): this carries a file.
            var data = new FormData(this);

            $submit.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                dataType: 'json',
                complete: function () {
                    $submit.prop('disabled', false).removeClass('is-loading');
                },
                success: function (response) {
                    if (!response.success) {
                        message('danger', 'The picture could not be saved. Please try again.');
                        return;
                    }

                    message(response.live ? 'success' : 'info', response.message);

                    // A live picture is already on screen from the preview. A
                    // pending one must not replace the current picture, so the
                    // page reloads to show it in its own slot instead.
                    if (!response.live) {
                        window.setTimeout(function () { window.location.reload(); }, 1200);
                    }
                },
                error: function (xhr) {
                    var text = 'The picture could not be uploaded. Please try again.';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        text = Object.values(xhr.responseJSON.errors)[0][0];
                    }

                    message('danger', text);
                }
            });
        });
    });
</script>
@endpush
