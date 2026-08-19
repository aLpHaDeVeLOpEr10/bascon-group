@extends('layouts.app')

{{-- Opens with the navigation collapsed to the icon rail. This screen is the
     densest in the app — five tab groups over eleven tables — so it starts
     with the full width available and the navigation out of the way. --}}
@section('collapse-sidebar', 'yes')

@section('breadcrumbs')
    <a href="{{ url('construction/show_site') }}">Construction</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Site details</span>
@endsection

@section('content')
<x-page-header :title="$name"
               subtitle="Materials, labour, miscellaneous costs and running totals for this site." />


            {{-- The tab strip meets the card edge, so the card clips it; the
                 panes carry the padding the body would otherwise have. --}}
            <div class="ui-card overflow-hidden">


                        <div>

                            <!-- Nav tabs -->
                            <ul class="ui-tabs" role="tablist">
                                <li role="presentation"><a href="#add_entry" aria-controls="add_entry" role="tab" data-toggle="tab">Add Entry</a></li>
                                <li role="presentation"><a href="#A_category" id="total_civil" aria-controls="home" role="tab" data-toggle="tab">Civil Materials</a></li>
                                <li role="presentation"><a href="#B_category" id="total_finish" aria-controls="profile" role="tab" data-toggle="tab">Finshing Materials</a></li>
                                <li role="presentation"><a href="#labour" id="total_labour" aria-controls="profile" role="tab" data-toggle="tab">Labour</a></li>
                                <li role="presentation"><a href="#Misc" id="total_misc" aria-controls="profile" role="tab" data-toggle="tab">Miscellaneous</a></li>

                                <li role="presentation"><a href="#payment" aria-controls="profile" id="total_pay" role="tab" data-toggle="tab">Sub Total</a></li>

                            </ul>
                        </div>

                        <!-- Tab panes -->
                        <div class="tab-content">


                            <!--Start add_entry -->
                            {{-- One form for all four ledgers.

                                 The four Add forms below each collect the same
                                 shape of thing — a category, sometimes a
                                 detail, sometimes a quantity, always an amount
                                 and a date — but under different field names
                                 and to different endpoints. Rather than a fifth
                                 endpoint, this posts to the existing four with
                                 their own names, so validation, the rollups and
                                 the approval status all behave exactly as they
                                 do on the individual tabs. The mapping lives in
                                 ENTRY_KINDS in the script below.

                                 The individual tabs are untouched and still
                                 work; this is a faster way in, not a
                                 replacement. --}}
                            <div role="tabpanel" class="tab-pane" id="add_entry">
                                <div class="mt-4">
                                    <form role="form" id="entry_form">
                                        <input type="hidden" name="proj_id" value="{{ $const_id }}">

                                        <div id="entry_message" class="ui-alert mb-5"
                                             role="status" aria-live="polite" hidden></div>

                                        <x-field label="Category"
                                                 hint="Which ledger this entry belongs to.">
    <select class="ui-select" id="entry_kind" name="entry_kind">
                                                <option value="civil">Civil Materials</option>
                                                <option value="finishing">Finishing Materials</option>
                                                <option value="labour">Labour</option>
                                                <option value="misc">Miscellaneous</option>
                                            </select>
</x-field>

                                        {{-- Everything below is shown or hidden
                                             by the chosen category. --}}
                                        <div data-entry-row="item">
                                            <x-field label="Material" id="entry_item_field">
    <select class="ui-select" id="entry_item"></select>
</x-field>
                                        </div>

                                        <div data-entry-row="detail">
                                            <x-field label="Detail">
    <input type="text" class="ui-input" id="entry_detail" placeholder="Add detail">
</x-field>
                                        </div>

                                        <div data-entry-row="quantity">
                                            <x-field label="Quantity">
    <input type="number" step="any" class="ui-input" id="entry_quantity" placeholder="Add quantity">
</x-field>
                                        </div>

                                        <x-field label="Price">
    <input type="number" step="any" class="ui-input" id="entry_amount" placeholder="Add price" required>
</x-field>

                                        <x-field label="Date">
    <input type="text" class="ui-input datepicker" id="entry_date"
                                                   placeholder="Select a date" autocomplete="off">
</x-field>

                                        <div class="ui-form-actions">
                                            <button type="submit" class="ui-btn ui-btn-primary">Add entry</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="A_category">
                                <ul class="ui-tabs" role="tablist">
                                    <li role="presentation"><a href="#show_material" aria-controls="profile" role="tab" data-toggle="tab">Show Meterial</a></li>
                                    <li role="presentation"><a href="#civil_total" aria-controls="civil_total" role="tab" data-toggle="tab">Civil Total</a></li>
                                </ul>


                                <div class="tab-content">


                                    <div role="tabpanel" class="tab-pane" id="show_material">

                                        <div class="ui-row">
                                            <label class="ui-label" >Select List</label>

                                            <div class="shrink-0">
                                                <select class="ui-select get_category" id="category" name="catgory">
                                                    <option> Select Material</option>
                                                    @foreach ($civilCategories as $row)

                                                        <option> {{ $row->material_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- Refetches the list without a page reload, for entries
                                                 added from another tab or by someone else. It replays
                                                 the select's own change handler, so there is one
                                                 loading path rather than two that can drift. --}}
                                            <button type="button" class="ui-icon-btn ui-tip ui-reload"
                                                    data-reload="#category" data-tip="Reload data"
                                                    aria-label="Reload data">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                     aria-hidden="true">
                                                    <path d="M3 12a9 9 0 0 1 15.5-6.2L21 8" />
                                                    <path d="M21 3v5h-5" />
                                                    <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16" />
                                                    <path d="M3 21v-5h5" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="ui-total my-5" id="total_price">

                                        </div>
                                        <div class="mt-4">


                                            <table id="show_site" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Materials</th>
                                                        <th>Unit</th>
                                                        <th>Ammount</th>
                                                        <th class="w-px whitespace-nowrap text-right">Action</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="civil_total">

                                        <div class="mt-4">


                                            <div class="ui-total mb-4" id="civil_total324"></div>
                                            <table id="civil_account" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <!-- End A_category -->

                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="B_category">

                                <ul class="ui-tabs" role="tablist">

                                    <li role="presentation"><a href="#tab_4" aria-controls="profile" role="tab" data-toggle="tab">Show details</a></li>
                                    <li role="presentation"><a href="#finish_total" aria-controls="finish_total" role="tab" data-toggle="tab">Finishing Total</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="tab_4">
                                        <div class="ui-row">
                                            <label class="ui-label" >Select List</label>

                                            <div class="shrink-0">
                                                <select class="ui-select get__b_category" id="b_category" name="catgory">
                                                    <option> Select Material</option>
                                                    @foreach ($finishCategories as $row)

                                                        <option> {{ $row->material_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- Refetches the list without a page reload, for entries
                                                 added from another tab or by someone else. It replays
                                                 the select's own change handler, so there is one
                                                 loading path rather than two that can drift. --}}
                                            <button type="button" class="ui-icon-btn ui-tip ui-reload"
                                                    data-reload="#b_category" data-tip="Reload data"
                                                    aria-label="Reload data">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                     aria-hidden="true">
                                                    <path d="M3 12a9 9 0 0 1 15.5-6.2L21 8" />
                                                    <path d="M21 3v5h-5" />
                                                    <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16" />
                                                    <path d="M3 21v-5h5" />
                                                </svg>
                                            </button>

                                        </div>

                                        <div class="ui-total my-5" id="total_price_b">

                                        </div>
                                        <div class="mt-4">


                                            <table id="b_categoer_table" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Materials</th>
                                                        <th>Detail</th>
                                                        <th>Unit</th>
                                                        <th>Ammount</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="finish_total">


                                        <div class="mt-4">


                                            <div class="ui-total mb-4" id="finish_total324"></div>
                                            <table id="finish_account" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Detail</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <!-- End B_category -->


                            <!-- Start labour -->

                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="labour">

                                <ul class="ui-tabs" role="tablist">


                                    <li role="presentation"><a href="#show_instalment" aria-controls="profile" role="tab" data-toggle="tab">Show Instalment</a></li>
                                    <li role="presentation"><a href="#Labour_total" aria-controls="Labour_total" role="tab" data-toggle="tab">Labour Total</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="show_instalment">
                                        <div class="ui-row">
                                            <label class="ui-label" >Select List</label>

                                            <div class="shrink-0">
                                                <select class="ui-select get__labour" id="labour_value" name="labour_value">
                                                    <option> Select </option>
                                                    @foreach ($labourTypes as $row)

                                                        <option> {{ $row->type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- Refetches the list without a page reload, for entries
                                                 added from another tab or by someone else. It replays
                                                 the select's own change handler, so there is one
                                                 loading path rather than two that can drift. --}}
                                            <button type="button" class="ui-icon-btn ui-tip ui-reload"
                                                    data-reload="#labour_value" data-tip="Reload data"
                                                    aria-label="Reload data">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                     aria-hidden="true">
                                                    <path d="M3 12a9 9 0 0 1 15.5-6.2L21 8" />
                                                    <path d="M21 3v5h-5" />
                                                    <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16" />
                                                    <path d="M3 21v-5h5" />
                                                </svg>
                                            </button>

                                        </div>

                                        <div class="mt-4">


                                            <div class="ui-total mb-4" id="total_price_labour"></div>
                                            <table id="labour_table" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>labour</th>
                                                        <th>Description</th>
                                                        <th>installment</th>
                                                        <th>Action</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="Labour_total">


                                        <div class="mt-4">


                                            <div class="ui-total mb-4" id="Labour_account123"></div>
                                            <table id="Labour_account" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Labour</th>
                                                        <th>Detail</th>
                                                        <th>Installment</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- End labour -->

                            <!-- Start Miscellaneous -->
                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="Misc">

                                <ul class="ui-tabs" role="tablist">


                                    <li role="presentation"><a href="#show_misc" aria-controls="profile" role="tab" data-toggle="tab">Show Miscellaneous</a></li>
                                    <li role="presentation"><a href="#Miscellaneous_total" aria-controls="Miscellaneous_total" role="tab" data-toggle="tab">Miscellaneous Total</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="show_misc">
                                        <div class="mt-4">
                                            <div class="ui-total mb-4" id="misclanious_total"></div>
                                            <table id="misc_table" width="100%" class="ui-table">
                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Detail</th>
                                                        <th>Price</th>
                                                        <th>Action</th>


                                                    </tr>
                                                </thead>
                                            </table>

                                        </div>

                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="Miscellaneous_total">


                                        <div class="mt-4">


                                            <div class="ui-total mb-4" id="miscle_account123"></div>
                                            <table id="miscle_account" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Detail</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <!-- End Miscellaneous -->

                            <!-- Start Payment -->

                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="payment">

                                <ul class="ui-tabs" role="tablist">



                                    <li role="presentation"><a href="#Total_entries" aria-controls="profile" role="tab" data-toggle="tab">All Entries</a></li>
                                    <li role="presentation"><a href="#return" aria-controls="profile" role="tab" data-toggle="tab"> Return </a></li>
                                    <li role="presentation"><a href="#grand_total" aria-controls="profile" role="tab" data-toggle="tab"> Grand Total</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="Total_entries">


                                        <div class="mt-4">


                                            <table id="Total_entries_account" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Source</th>
                                                        <th>Type</th>
                                                        <th>Detail</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                            {{-- No total card here: this pane used to carry a second
                                                 <div id="finish_total324">, the same id as the one in
                                                 the Finishing Total pane above. Only the first of a
                                                 duplicated id is ever written to, so it sat empty
                                                 (and hidden by .ui-total:empty) on every visit. --}}
                                        </div>

                                    </div>


                                    <div role="tabpanel" class="tab-pane" id="return">

                                        <ul class="ui-tabs" role="tablist">

                                            <li role="presentation"><a href="#return_add" aria-controls="home" role="tab" data-toggle="tab">Return Add</a></li>
                                            <li role="presentation"><a href="#return_show" id="return_tab" aria-controls="profile" role="tab" data-toggle="tab">Return Show</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div role="tabpanel" class="tab-pane" id="return_add">
                                                <form role="form" id="return_form" action="{{ url('construction/return_payment') }}">

                                                    <x-field label="Detail">
    <input type="text" class="ui-input" name="Detail_misc" id="field-1" placeholder="Add Detail" required>
</x-field>

                                                    <x-field label="Ammount">
    <input type="number" class="ui-input" name="ammoun_misc" id="field-1" placeholder="Add Price" required>
</x-field>

                                                    <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date3" id="datepicke3" placeholder="Select a date" required>
</x-field>
                                                    <div class="ui-row">


                                                        <div class="min-w-0 flex-1">
                                                            <input type="hidden" class="ui-input" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-primary submit-form">Add</button>
</div>
                                                </form>
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="return_show">
                                                <div class="mt-4">
                                                    <div class="ui-total mb-4" id="return_total"></div>
                                                    <table id="return_table" width="100%" class="ui-table">
                                                        <thead>
                                                            <tr>
                                                                <!-- Add your table headers here -->
                                                                <th>Sr No</th>
                                                                <th>Date</th>
                                                                <th>Detail</th>
                                                                <th>Price</th>
                                                                <th>Action</th>


                                                            </tr>
                                                        </thead>
                                                    </table>

                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="grand_total">


                                        <div class="mt-4">


                                            {{-- Stat cards in flow rather than the pinned pills the other
                                                 Sub Total tabs use. Two long money figures side by side ran
                                                 past the card's right edge once pinned to the tab line, and
                                                 this is the same pair the client sees on their Grand Total —
                                                 so it is presented the same way, with the same labels, icons
                                                 and the danger tone when the balance is overdrawn. --}}
                                            <div class="mb-4 grid gap-4 sm:grid-cols-2">
                                                <x-stat-card label="Payments received" :value="money($payment_recieved)" wash
                                                             icon="check" tone="success" />
                                                <x-stat-card label="Remaining balance" :value="money($Remainung_Balace)"
                                                             icon="clock"
                                                             :tone="$Remainung_Balace < 0 ? 'danger' : 'success'" wash />
                                            </div>
                                            <table id="grand_account" width="100%" class="ui-table">

                                                <thead>
                                                    <tr>
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Type</th>
                                                        <th>Total</th>

                                                    </tr>
                                                </thead>
                                            </table>

                                        </div>
                                    </div>
                                </div>


                                <!-- End Payment -->

                            </div>

                        </div>
                    </div>
                

                <div class="modal" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="updateModalLabel">Update User</h2>
                                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="updateForm">

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Material</label>

                                        <input type="text" class="ui-input" name="type" id="field-1" placeholder="Plot No" required>

                                        <input type="hidden" class="ui-input" name="id" id="civil_id" placeholder="Material" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Quantity</label>


                                        <input type="text" class="ui-input" name="quantity" id="sector" placeholder="Quantity" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Price</label>


                                        <input type="text" class="ui-input" name="price" id="field-1" placeholder="Price" required>
</div>

                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal" id="finishModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="updateModalLabel">Update User</h2>
                                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="finishForm">

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Material</label>

                                        <input type="text" class="ui-input" name="type" id="field-1" placeholder="Plot No" required>

                                        <input type="hidden" class="ui-input" name="id" id="finish_id" placeholder="Material" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Detail</label>


                                        <input type="text" class="ui-input" name="detail" id="detail" placeholder="detail" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Quantity</label>


                                        <input type="text" class="ui-input" name="quantity" id="sector" placeholder="Quantity" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Price</label>


                                        <input type="text" class="ui-input" name="price" id="field-1" placeholder="Price" required>
</div>

                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal" id="openlabourModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="updateModalLabel">Update Labour</h2>
                                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="labourForm">

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Labour</label>

                                        <input type="text" class="ui-input" name="type" id="field-1" placeholder="Labour" required>

                                        <input type="hidden" class="ui-input" name="id" id="labour_id" placeholder="Material" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Description</label>


                                        <input type="text" class="ui-input" name="description" id="detail" placeholder="Description" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Paid</label>


                                        <input type="text" class="ui-input" name="instalmet" id="sector" placeholder="Instalmet" required>
</div>

                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal" id="openmiscrModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="updateModalLabel">Update Miscellaneous</h2>
                                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="miscForm">

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Detail</label>

                                        <input type="text" class="ui-input" name="detail" id="field-1" placeholder="Detail" required>

                                        <input type="hidden" class="ui-input" name="id" id="misc_id" placeholder="Material" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Ammount</label>


                                        <input type="text" class="ui-input" name="price" id="detail" placeholder="Ammount" required>
</div>

                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal" id="openreturnrModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="updateModalLabel">Update Miscellaneous</h2>
                                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="returnform">

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Detail</label>

                                        <input type="text" class="ui-input" name="detail" id="field-1" placeholder="Detail" required>

                                        <input type="hidden" class="ui-input" name="id" id="return_id" placeholder="Material" required>
</div>

                                    <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Ammount</label>


                                        <input type="text" class="ui-input" name="price" id="detail" placeholder="Ammount" required>
</div>

                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
{{-- Every server value this page's JavaScript needs, in one object. The script
     itself is resources/js/site-settings.js — see the note at the top of it.

     The array is built in a php block first, then encoded: the json directive
     takes its argument as one expression, and Blade's directive parser cannot
     follow a multi-line array literal containing its own brackets and calls.
     (Directive names are spelled out here rather than written with their @
     prefix — Blade compiles those even inside a comment.) --}}
@php
    $siteSettings = [
        'siteId' => $const_id,
        'siteName' => $name,
        'base' => url('/'),
        'watermark' => asset('assets/images/water_mak.jpeg'),
        'totals' => [
            'civil' => money($civil_price),
            'finishing' => money($finish_price),
            'labour' => money($labour_price),
            'misc' => money($misc_price),
            'grand' => money($Grand_total),
        ],
        'options' => [
            'civil' => $civilCategories->pluck('material_name')->values(),
            'finishing' => $finishCategories->pluck('material_name')->values(),
            'labour' => $labourTypes->pluck('type')->values(),
        ],
    ];
@endphp

<script>
    window.SITE_SETTINGS = {!! json_encode($siteSettings) !!};
</script>

@vite('resources/js/site-settings.js')
@endpush
