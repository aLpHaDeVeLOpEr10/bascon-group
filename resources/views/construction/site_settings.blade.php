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
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Civil Materials</a></li>
                                <li role="presentation"><a href="#B_category" aria-controls="profile" role="tab" data-toggle="tab">Finshing Materials</a></li>
                                <li role="presentation"><a href="#labour" aria-controls="profile" role="tab" data-toggle="tab">Labour</a></li>
                                <li role="presentation"><a href="#Misc" id="total_misc" aria-controls="profile" role="tab" data-toggle="tab">Miscellaneous</a></li>

                                <li role="presentation"><a href="#payment" aria-controls="profile" id="total_pay" role="tab" data-toggle="tab">Sub Total</a></li>

                            </ul>
                        </div>

                        <!-- Tab panes -->
                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="A_category">
                                <ul class="ui-tabs" role="tablist">
                                    <li role="presentation"><a href="#add_material" aria-controls="home" role="tab" data-toggle="tab">Add Meterial</a>
                                    </li>
                                    <li role="presentation"><a href="#show_material" aria-controls="profile" role="tab" data-toggle="tab">Show Meterial</a></li>
                                </ul>


                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="add_material">


                                        <form role="form" id="brick_addition_form" action="{{ url('construction/Add_brick') }}">

                                            <x-field label="Select List">
    <select class="ui-select" name="catgory" id="catgory">
                                                        <option> Select Material</option>
                                                        @foreach ($civilCategories as $row)

                                                            <option> {{ $row->material_name }}</option>
                                                        @endforeach
                                                    </select>
</x-field>

                                            <x-field label="Quantity">
    <input type="number" class="ui-input" name="brick_quantity" id="field-1" placeholder="Add Quantity" required>
</x-field>

                                            <x-field label="Price">
    <input type="number" class="ui-input" name="brick_price" id="field-1" placeholder="Add Price" required>
</x-field>
                                            <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date1" id="datepicker1" placeholder="Select a date" required>
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

                                </div>


                            </div>
                            <!-- End A_category -->

                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="B_category">

                                <ul class="ui-tabs" role="tablist">
                                    <li role="presentation"><a href="#tab_3" aria-controls="home" role="tab" data-toggle="tab">Add Detail</a></li>
                                    <li role="presentation"><a href="#tab_4" aria-controls="profile" role="tab" data-toggle="tab">Show details</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="tab_3">
                                        <form role="form" id="Bcategory_form" action="{{ url('construction/Add_b_category') }}">


                                            <x-field label="Select List">
    <select class="ui-select" name="catgory" id="b_catecory">
                                                        <option> Select Material</option>
                                                        @foreach ($finishCategories as $row)

                                                            <option> {{ $row->material_name }}</option>
                                                        @endforeach
                                                    </select>
</x-field>
                                            <x-field label="Detail">
    <input type="text" class="ui-input" name="Detail" id="field-1" placeholder="Add Detail" required>
</x-field>

                                            <x-field label="Quantity">
    <input type="number" class="ui-input" name="brick_quantity" id="field-1" placeholder="Add Quantity" required>
</x-field>

                                            <x-field label="Price">
    <input type="number" class="ui-input" name="brick_price" id="field-1" placeholder="Add Price" required>
</x-field>
                                            <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date2" id="datepicker2" placeholder="Select a date" required>
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
                                </div>
                            </div>


                            <!-- End B_category -->


                            <!-- Start labour -->

                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="labour">

                                <ul class="ui-tabs" role="tablist">

                                    <li role="presentation"><a href="#instalment" aria-controls="home" role="tab" data-toggle="tab">Add Instalment</a></li>
                                    <li role="presentation"><a href="#show_instalment" aria-controls="profile" role="tab" data-toggle="tab">Show Instalment</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="instalment">
                                        <form role="form" id="labour_form" action="{{ url('construction/labour_instalment') }}">

                                            
                                            <x-field label="Select List">
    <select class="ui-select" name="labour_type" id="c_category">
                                                        <option> Select </option>
                                                        @foreach ($labourTypes as $row)

                                                            <option> {{ $row->type }}</option>
                                                        @endforeach
                                                    </select>
</x-field>
                                            <x-field label="Detail">
    <input type="text" class="ui-input" name="Detail" id="field-1" placeholder="Add Detail" required>
</x-field>

                                            <x-field label="Paid">
    <input type="number" class="ui-input" name="bill_labour" id="field-1" placeholder="Add ammount" required>
</x-field>

                                            <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date" id="datepicker" placeholder="Select a date" required>
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
                                </div>
                            </div>

                            <!-- End labour -->

                            <!-- Start Miscellaneous -->
                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="Misc">

                                <ul class="ui-tabs" role="tablist">

                                    <li role="presentation"><a href="#micsc_detail" aria-controls="home" role="tab" data-toggle="tab">Add Detail</a></li>
                                    <li role="presentation"><a href="#show_misc" aria-controls="profile" role="tab" data-toggle="tab">Show Miscellaneous</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="micsc_detail">
                                        <form role="form" id="misc_form" action="{{ url('construction/misc_add') }}">

                                            <x-field label="Detail">
    <input type="text" class="ui-input" name="Detail_misc" id="field-1" placeholder="Add Detail" required>
</x-field>

                                            <x-field label="Ammount">
    <input type="number" class="ui-input" name="ammoun_misc" id="field-1" placeholder="Add Price" required>
</x-field>

                                            <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date3" id="datepicke6" placeholder="Select a date" required>
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
                                </div>
                            </div>


                            <!-- End Miscellaneous -->

                            <!-- Start Payment -->

                            <div role="tabpanel" class="tab-pane ui-tab-total-host" id="payment">

                                <ul class="ui-tabs" role="tablist">

                                    <li role="presentation"><a href="#civil_total" aria-controls="home" role="tab" data-toggle="tab">Civil Total</a></li>
                                    <li role="presentation"><a href="#finish_total" aria-controls="profile" role="tab" data-toggle="tab">Finishing Total</a></li>

                                    <li role="presentation"><a href="#Miscellaneous_total" aria-controls="profile" role="tab" data-toggle="tab"> Miscellaneous Total</a></li>

                                    <li role="presentation"><a href="#Labour_total" aria-controls="profile" role="tab" data-toggle="tab"> Labour Total</a></li>
                                    <li role="presentation"><a href="#Total_entries" aria-controls="profile" role="tab" data-toggle="tab">All Entries</a></li>

                                    <li role="presentation"><a href="#return" aria-controls="profile" role="tab" data-toggle="tab"> Return </a></li>
                                    <li role="presentation"><a href="#grand_total" aria-controls="profile" role="tab" data-toggle="tab"> Grand Total</a></li>

                                </ul>

                                <div class="tab-content">
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


                                            <div class="ui-total mb-4" id="total_price_managments">
                                                {{-- Two figures, so two pills — the run of &nbsp; that used to
                                                     separate them stretched the row off the side of the card. --}}
                                                <h3>Payments Recieved: @money($payment_recieved)</h3>
                                                <h3 @class(['is-negative' => $Remainung_Balace < 0])>Remaining Balace: @money($Remainung_Balace)</h3>
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
<script>
    /** Sits at the head of every printed sheet, above the table. */
    var PRINT_MASTHEAD = 'BASCON GROUP';

    /**
     * Heading for a print button.
     *
     * Three of the tables on this page show one material at a time, chosen
     * from a <select> above them, so the table name alone does not say what
     * was printed — every category prints as "Civil Materials". `selectId`
     * names the dropdown to fold into the heading.
     *
     * DataTables evaluates a `title` function at export time rather than at
     * init, which is what makes this work: the heading follows whatever is
     * selected when the button is pressed.
     *
     * Those <option>s carry no value attribute, so val() hands back the label
     * text — including the leading space the option markup opens with, hence
     * the trim. The first option is a placeholder rather than a material, and
     * is left out of the heading entirely.
     */
    function printHeading(tableName, selectId, placeholder) {
        var site = @json($name);

        if (!selectId) return tableName + ' — ' + site;

        var picked = ($('#' + selectId).val() || '').trim();
        var suffix = picked && picked !== placeholder ? ' (' + picked + ')' : '';

        return tableName + suffix + ' — ' + site;
    }

    /**
     * Puts that heading in the printed table's own first row instead of in an
     * <h1> above it, so the sheet reads as one block and the heading travels
     * with the table if the rows spill onto a second page.
     *
     * Every print button used to build this row by hand with a hardcoded
     * colspan — "5" on one table, "6" on nine others, several with a stray
     * empty <th> tacked on to cover the miscount. The count is taken from the
     * header row itself here, before the heading is prepended to it, so it
     * cannot drift when a column is added or moved.
     *
     * The heading carries a site name from the database, so it goes in as
     * text rather than markup.
     */
    function printHeadingRow(win, heading) {
        var body = $(win.document.body);

        // DataTables writes its `title` into the print window twice: once as
        // the document <title> and again as an <h1> above the table. Only the
        // first is wanted — the browser draws the document title along the top
        // of every printed page, so the <h1> was a second BASCON GROUP sitting
        // directly on the table's own heading.
        //
        // Dropped here rather than by blanking `title`, which would take the
        // document <title> with it and leave the top of the page empty.
        body.find('h1').remove();

        var thead = body.find('table thead');
        if (!thead.length) return;

        var columns = thead.find('tr').first().children().length || 1;

        thead.prepend(
            '<tr class="print-heading">' +
            '<th colspan="' + columns + '" ' +
            'style="font-size:14px;font-weight:700;text-align:center;padding:8px 6px;' +
            'text-transform:none;letter-spacing:normal;color:#18181b;">' +
            $('<div/>').text(heading).html() +
            '</th></tr>'
        );
    }
</script>
{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
<script>
    $(function() {
        $("#datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $("#datepicker1").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $("#datepicker2").datepicker({
            dateFormat: 'dd/mm/yy'
        });

        $("#datepicke3").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $("#datepicke6").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#brick_addition_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="brick_quantity"]').val('');
                        $('input[name="brick_price"]').val('');
                        $('#catgory').val('');
                        $('#datepicker1').val('');

                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });
</script>
<script>
    $('.get_category').on('change', function() {
        var Category = $('#category').val();

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#show_site')) {
            $('#show_site').DataTable().destroy();
        }

        $.ajax({
            url: "{{ url('construction/show_bricks/' . $const_id ) }}/" + Category,
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var quantity = response.quantity;


                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });
                $('#total_price').html('<h4>Total Amount: ' + money(total_price) + '</h4>');

                var oAllLinksTable = $('#show_site').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        }, {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },
                        {
                            /* Actions belong in a column of their own. They
                               used to be rendered into the Materials cell
                               beneath the material name, which made that
                               column two things at once and left the row with
                               no action column at all. */
                            "data": null,
                            "orderable": false,
                            "className": "whitespace-nowrap text-right",
                            "render": function(data, type, row) {
                                return '<div class="ui-row-actions">' +
                                    '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deleteCivil(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>' +
                                    '</div>';
                            }
                        },

                    ],

                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Every print button on this page used to head its
                            // output "BASCON GROUP" — DataTables falls back to
                            // the document title when none is given, so eleven
                            // different tables printed under the same heading
                            // and a printout could not be identified once it
                            // left the screen. Each now names its own table,
                            // the material it is filtered to where there is
                            // one, and the site it belongs to.
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {


                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert "Civil Material" heading in the table header (centered with colspan="5")
                                printHeadingRow(win, printHeading('Civil Materials', 'category', 'Select Material'));

                                // Append total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' +
                                    '<td>Total Quantity: ' + quantity + '</td>' +
                                    '<td>Total Amount: ' + money(total_price) + '</td>' +
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);

                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );
                            }


                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#Bcategory_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="brick_quantity"]').val('');
                        $('input[name="brick_price"]').val('');
                        $('#b_catecory').val('');
                        $('#datepicker2').val('');

                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });
</script>
<script>
    // 'change', not 'click': on a <select>, click fires when the list is merely
    // opened, so every click reloaded the table before a choice had been made.
    $('.get__b_category').on('change', function() {
        var Category = $('#b_category').val();

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#b_categoer_table')) {
            $('#b_categoer_table').DataTable().destroy();
        }

        $.ajax({
            url: "{{ url('construction/show_b_category/' . $const_id ) }}/" + Category,
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var quantity = response.quantity;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#total_price_b').html('<h4>Total Amount: ' + money(total_price) + '</h4>');

                var oAllLinksTable = $('#b_categoer_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        }, {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },
                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openfinishModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deletefinish(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Finishing Materials', 'b_category', 'Select Material'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' +
                                    '<td>Total Quantity: ' + quantity + '</td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }

                        },

                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });

    });
</script>
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#labour_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="brick_quantity"]').val('');
                        $('input[name="brick_price"]').val('');
                        $('#c_category').val('');
                        $('#datepicker').val('');
                        v
                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#misc_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="Detail_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('#datepicke3').val('');

                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });

    $(document).ready(function() {
        // Handle form submission
        $('#return_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="Detail_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('#datepicke3').val('');

                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });
</script>
<script>
    // 'change' for the same reason as .get__b_category above.
    $('.get__labour').on('change', function() {
        var Category = $('#labour_value').val();

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#labour_table')) {
            $('#labour_table').DataTable().destroy();
        }

        $.ajax({
            url: "{{ url('construction/show_labour/' . $const_id ) }}/" + Category,
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var total_priceaaa = response.project_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#total_price_labour').html('<h4>Projcet Done: ' + money(total_priceaaa) + '</h4><h4>Total payed: ' + money(total_price) + '</h4>');

                var oAllLinksTable = $('#labour_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "description"
                        },
                        {
                            "data": "instalmet"
                        },

                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openmiscrModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deletelabour(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Labour Instalments', 'labour_value', 'Select'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });

    });
</script>
<script>
    var civilAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (civilAccountTable) {
            civilAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: "{{ url('construction/show_civil_total/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#civil_total324').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                civilAccountTable = $('#civil_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Civil Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    var miscTable = null; // Declare a variable to store DataTable instance

    $('#total_misc').click(function() {

        if (miscTable) {
            miscTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: "{{ url('construction/misc_total/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#misclanious_total').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                miscTable = $('#misc_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "price"
                        },

                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openmiscrModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deletemisc(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Miscellaneous'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    var returnTable = null;

    $('#return_tab').click(function() {

        if (returnTable) {
            returnTable.destroy();
        }

        $.ajax({
            url: "{{ url('construction/return_total/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#return_total').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                returnTable = $('#return_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "price"
                        },

                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openreturnrModal22(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="return_delete(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Returned Payments'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });



    });

    function openreturnrModal22(userId) {

        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('construction/get_return_details') }}",
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#return_id').val(response.data.id);
                            $('#returnform input[name="detail"]').val(response.data.detail);
                            $('#returnform input[name="price"]').val(response.data.price);

                            // Show the modal
                            $('#openreturnrModal').modal('show');
                        } else {
                            Swal.fire('Error', 'Failed to fetch user details', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request', 'error');
                    }
                });
            }
        });
    }
</script>
<script>
    var finishAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (finishAccountTable) {
            finishAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: "{{ url('construction/show_finish_total/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                $('#finish_total324').html('<h3>Total Amount: ' + money(total_price) + '</h3>');
                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                // Initialize DataTable and store the instance in the variable
                finishAccountTable = $('#finish_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible' // Export only visible columns
                            },
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Finishing Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="5"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    var AllAccountTable = null;

    $('#total_pay').click(function() {
        if (AllAccountTable) {
            AllAccountTable.destroy();
        }

        $.ajax({
            url: "{{ url('construction/total_entries/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var sites_data = response.data;

                // Add serial number
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                AllAccountTable = $('#Total_entries_account').DataTable({
                    data: sites_data,
                    // Source sits right after the date: this table pools Civil,
                    // Finishing, Labour and Miscellaneous rows together, so
                    // which ledger a row came from belongs up front rather than
                    // stranded in the last column.
                    columns: [{
                            data: 'serial_number',
                            title: '#'
                        },
                        {
                            data: 'date',
                            title: 'Date'
                        },
                        {
                            data: 'source',
                            title: 'Source'
                        },
                        {
                            data: 'type',
                            title: 'Type'
                        },
                        {
                            data: 'detail',
                            title: 'Detail'
                        },
                        {
                            data: 'quantity',
                            title: 'Quantity'
                        },
                        {
                            data: 'price',
                            title: 'Price'
                        },
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible'
                            },
                            customize: function(win) {
                                printHeadingRow(win, printHeading('All Entries'));

                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'total_entries'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    var miscleAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (miscleAccountTable) {
            miscleAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: "{{ url('construction/show_misc_total/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#miscle_account123').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                miscleAccountTable = $('#miscle_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "price"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible' // Export only visible columns
                            },
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Miscellaneous Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    var labourAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (labourAccountTable) {
            labourAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: "{{ url('construction/show_labour_total/' . $const_id) }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#Labour_account123').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                labourAccountTable = $('#Labour_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "description"
                        },
                        {
                            "data": "instalmet"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible' // Export only visible columns
                            },
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Labour Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });
</script>
<script>
    // Assuming jQuery is included
    function openUpdateModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('construction/get_civil_details') }}",
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#civil_id').val(response.data.id);
                            $('#updateForm input[name="type"]').val(response.data.type);
                            $('#updateForm input[name="quantity"]').val(response.data.quantity);
                            $('#updateForm input[name="price"]').val(response.data.price);


                            // Show the modal
                            $('#updateModal').modal('show');
                        } else {
                            Swal.fire('Error', 'Failed to fetch user details', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request', 'error');
                    }
                });
            }
        });
    }



    $(document).ready(function() {
        $('#updateForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('construction/update_civil') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#updateModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            // Reload the DataTable upon success
                            var oAllLinksTable = $('#show_site').DataTable();
                            oAllLinksTable.destroy();
                            // Set to false to use the same page
                        });
                    } else {
                        // Show error message with SweetAlert
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update user. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },

            });
        });
    });

    function deleteCivil(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/delete_civil') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#show_site').DataTable();
                            var row = table.row('#tr_' + userId);

                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>
<script>
    // Assuming jQuery is included
    function openfinishModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('construction/get_finish_details') }}",
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#finish_id').val(response.data.id);
                            $('#finishForm input[name="type"]').val(response.data.type);
                            $('#finishForm input[name="quantity"]').val(response.data.quantity);
                            $('#finishForm input[name="price"]').val(response.data.price);
                            $('#finishForm input[name="detail"]').val(response.data.detail);



                            // Show the modal
                            $('#finishModal').modal('show');
                        } else {
                            Swal.fire('Error', 'Failed to fetch user details', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request', 'error');
                    }
                });
            }
        });
    }



    $(document).ready(function() {
        $('#finishForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('construction/update_finish') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#finishModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

                        });
                    } else {
                        // Show error message with SweetAlert
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update user. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },

            });
        });
    });

    function deleteCivil(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/delete_civil') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#show_site').DataTable();
                            var row = table.row('#tr_' + userId);

                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function deletefinish(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/delete_finish') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#finishModal').DataTable();
                            var row = table.row('#tr_' + userId);

                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function deletelabour(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/delete_labour') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#labour_table').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }


    function deletelabour(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/delete_labour') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#labour_table').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function deletemisc(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/delete_misc') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#misc_table').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function return_delete(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('construction/return_delete') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#returnTable').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }


    function openlabourModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('construction/get_labour_details') }}",
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#labour_id').val(response.data.id);
                            $('#labourForm input[name="type"]').val(response.data.type);
                            $('#labourForm input[name="description"]').val(response.data.description);
                            $('#labourForm input[name="instalmet"]').val(response.data.instalmet);



                            // Show the modal
                            $('#openlabourModal').modal('show');
                        } else {
                            Swal.fire('Error', 'Failed to fetch user details', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request', 'error');
                    }
                });
            }
        });
    }



    $(document).ready(function() {
        $('#labourForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('construction/update_labour') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#openlabourModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

                        });
                    } else {
                        // Show error message with SweetAlert
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update user. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },

            });
        });
    });



    function openmiscrModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('construction/get_misc_details') }}",
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#misc_id').val(response.data.id);
                            $('#miscForm input[name="detail"]').val(response.data.detail);
                            $('#miscForm input[name="price"]').val(response.data.price);

                            // Show the modal
                            $('#openmiscrModal').modal('show');
                        } else {
                            Swal.fire('Error', 'Failed to fetch user details', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request', 'error');
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        $('#miscForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('construction/update_misc') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#openmiscrModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

                        });
                    } else {
                        // Show error message with SweetAlert
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update user. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },

            });
        });
    });

    $(document).ready(function() {
        $('#returnform').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('construction/update_rerturn') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#openreturnrModal').modal('hide');

                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

                        });
                    } else {
                        // Show error message with SweetAlert
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update user. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },

            });
        });
    });

    $(document).ready(function() {
        var dataTable = $('#grand_account').DataTable({
            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
            buttons: [{
                    extend: 'print',
                    // Masthead only. Which table this is, which material it is
                    // filtered to and which site it belongs to all go in the
                    // table's own first row instead — printHeadingRow, below.
                    title: PRINT_MASTHEAD,
                    text: 'Print Record',
                    className: 'dt-button',
                    exportOptions: {
                        columns: ':visible' // Export only visible columns
                    },
                    customize: function(win) {
                        // Add watermark
                        $(win.document.body).prepend(
                            '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                            '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" />' +
                            '</div>'
                        );
                        // Insert the heading row for the table (centered with colspan="6")
                        printHeadingRow(win, printHeading('Grand Total'));

                    }
                },
                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'dt-button',
                    filename: 'data_export'
                }
            ]
        });

        // Add a new row with values
        dataTable.row.add([
            '1',
            'Civil Total',
            '@money($civil_price)',
        ]).draw();

        dataTable.row.add([
            '2',
            'Finish Total',
            '@money($finish_price)',
        ]).draw();

        dataTable.row.add([
            '3',
            'Labour total',
            '@money($labour_price)',
        ]).draw();

        dataTable.row.add([
            '4',
            'Miscellaneous Total',
            '@money($misc_price)',
        ]).draw();

        dataTable.row.add([
            '<h3>5</h3>',
            '<h3>Grand Total</h3>',
            '<h3>@money($Grand_total)</h3>',
        ]).draw();
    });
</script>
@endpush
