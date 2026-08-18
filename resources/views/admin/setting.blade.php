@extends('layouts.admin')

@section('breadcrumbs')
    <span>Settings</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Manage</span>
@endsection

@section('content')
<x-page-header title="Set Restriction"
               subtitle="Control what each role is allowed to reach." />


            <div class="ui-card" data-collapsed="0">

                            <!--Start A_category -->
                       
                                <div class="ui-card-body">

                                    <form role="form" id="material_add"
                                        action="{{ url('admin_setting/save_setting') }}">

                                        {{-- Saving a setting is not worth a modal: it interrupts,
                                             has to be dismissed, and covers the very controls you
                                             just changed. The result is reported in place instead.
                                             role="status" so a screen reader announces it without
                                             the focus being moved. --}}
                                        <div class="ui-alert mb-5" id="settings-message"
                                             role="status" aria-live="polite" hidden></div>

                                        <x-field label="Civil Setting">
    <select class="ui-select" id="civil" name="civil">
                                                    <option> Select Material</option>
                                                       
                                                        
                                                        <option value="1">Approve By admin</option>
                                                        <option value="0">Instant</option>
                                           
                                                    </select>
</x-field>
                                          <x-field label="Finish Setting">
    <select class="ui-select" id="finish" name="finish">
                                                    <option> Select Material</option>
                                                       
                                                        
                                                        <option value="1">Approve By admin</option>
                                                        <option value="0">Instant</option>
                                           
                                                    </select>
</x-field>

                                        {{-- Same two states as the material settings above: on
                                             "Approve By admin" a worker's payment is filed as
                                             pending and does not count towards a site's balance
                                             until an admin approves it on Payment Requests. --}}
                                        <x-field label="Payment Setting"
                                                 hint="Applies to payments a worker records against a site.">
    <select class="ui-select" id="payment" name="payment">
                                                    <option value="1">Approve By admin</option>
                                                    <option value="0">Instant</option>
                                                </select>
</x-field>


                                        <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-primary">Save settings</button>
</div>
                                    </form>

                                </div>

{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
</div>
@endsection

@push('scripts')
<script>

            $('#civil').val({{ $setting->civil_status }})
            $('#finish').val({{ $setting->finish_status }})
            $('#payment').val({{ $setting->payment_status }})


            /* One bar, restyled per outcome, so a success never sits under a
               stale error. It is not auto-dismissed: the page has no other
               feedback, and a message that vanishes on its own is one the
               reader can miss entirely. */
            function showSettingsMessage(tone, text) {
                var icons = {
                    success: '<path d="M20 6 9 17l-5-5"/>',
                    danger: '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>'
                };

                $('#settings-message')
                    .attr('class', 'ui-alert ui-alert-' + tone + ' mb-5')
                    .html(
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" ' +
                        'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + icons[tone] + '</svg>' +
                        '<span></span>'
                    )
                    .find('span').text(text).end()
                    .prop('hidden', false);
            }

            $(document).ready(function () {
               
                $('#material_add').submit(function (event) {
                    event.preventDefault();

                    var $submit = $(this).find('button[type="submit"]');
                    if ($submit.prop('disabled')) return;

                    // Your form data
                    var formData = $(this).serialize();

                    $submit.prop('disabled', true).addClass('is-loading');

                    // Ajax request
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        complete: function () {
                            $submit.prop('disabled', false).removeClass('is-loading');
                        },
                        success: function (response) {
                            if (response.success) {
                                showSettingsMessage('success', 'Settings saved. The approval settings have been updated.');
                            } else {
                                showSettingsMessage('danger', 'The settings could not be saved. Please try again.');
                            }
                        },
                        error: function () {
                            showSettingsMessage('danger', 'Could not reach the server. Please try again.');
                        }
                    });
                });
            });

           
        </script>
@endpush
