@extends('layouts.app')

@push('styles')
<style>
    .label_class {
        font-size: 12px;
        display: flex;
        text-align: end;
        align-items: end;
        justify-content: end;
    }
</style>
@endpush

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Site Details</strong>
                </li>
            </ol>

            <h2>
                {{ $name }}
            </h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary " style="padding-bottom: 30px;">


                        <div>

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
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
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation"><a href="#add_material" aria-controls="home" role="tab" data-toggle="tab">Add Meterial</a>
                                    </li>
                                    <li role="presentation"><a href="#show_material" aria-controls="profile" role="tab" data-toggle="tab">Show Meterial</a></li>
                                </ul>


                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="add_material">


                                        <form role="form" class="form-horizontal" id="brick_addition_form" action="{{ url('construction/Add_brick') }}">

                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">Select List</label>

                                                <div class="col-sm-5">
                                                    <select class="form-control" name="catgory" id="catgory">
                                                        <option> Select Material</option>
                                                        @foreach ($civilCategories as $row)

                                                            <option> {{ $row->material_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Quantity</label>

                                                <div class="col-sm-5">
                                                    <input type="number" class="form-control" name="brick_quantity" id="field-1" placeholder="Add Quantity" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Price</label>

                                                <div class="col-sm-5">
                                                    <input type="number" class="form-control" name="brick_price" id="field-1" placeholder="Add Price" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Date</label>
                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="selected_date1" id="datepicker1" placeholder="Select a date" required>
                                                </div>
                                            </div>
                                            <div class="form-group">

                                                
                                                <div class="col-sm-5">
                                                    <input type="hidden" class="form-control" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-3 col-sm-5">
                                                    <button type="submit" class="btn btn-default submit-form">Add</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>


                                    <div role="tabpanel" class="tab-pane" id="show_material">

                                        <div style="display: flex;">
                                            <label class="col-sm-3 control-label label_class">Select List</label>

                                            <div class="col-sm-2">
                                                <select class="form-control get_category" id="category" name="catgory">
                                                    <option> Select Material</option>
                                                    @foreach ($civilCategories as $row)

                                                        <option> {{ $row->material_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>





                                        <div style="margin-top:30px;" id="total_price">

                                        </div>
                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="show_site" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Materials</th>
                                                        <th>Unit</th>
                                                        <th>Ammount</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>



                                </div>


                            </div>
                            <!-- End A_category -->



                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane" id="B_category">

                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation"><a href="#tab_3" aria-controls="home" role="tab" data-toggle="tab">Add Detail</a></li>
                                    <li role="presentation"><a href="#tab_4" aria-controls="profile" role="tab" data-toggle="tab">Show details</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="tab_3">
                                        <form role="form" class="form-horizontal" id="Bcategory_form" action="{{ url('construction/Add_b_category') }}">


                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">Select List</label>

                                                <div class="col-sm-5">
                                                    <select class="form-control" name="catgory" id="b_catecory">
                                                        <option> Select Material</option>
                                                        @foreach ($finishCategories as $row)

                                                            <option> {{ $row->material_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Detail</label>

                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="Detail" id="field-1" placeholder="Add Detail" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Quantity</label>

                                                <div class="col-sm-5">
                                                    <input type="number" class="form-control" name="brick_quantity" id="field-1" placeholder="Add Quantity" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Price</label>

                                                <div class="col-sm-5">
                                                    <input type="number" class="form-control" name="brick_price" id="field-1" placeholder="Add Price" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Date</label>
                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="selected_date2" id="datepicker2" placeholder="Select a date" required>
                                                </div>
                                            </div>
                                            <div class="form-group">

                                                
                                                <div class="col-sm-5">
                                                    <input type="hidden" class="form-control" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-3 col-sm-5">
                                                    <button type="submit" class="btn btn-default submit-form">Add</button>
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="tab_4">
                                        <div class="form-group" style="display: flex;">
                                            <label class="col-sm-3 control-label label_class">Select List</label>

                                            <div class="col-sm-2">
                                                <select class="form-control get__b_category" id="b_category" name="catgory">
                                                    <option> Select Material</option>
                                                    @foreach ($finishCategories as $row)

                                                        <option> {{ $row->material_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>





                                        <div style="margin-top:30px;" id="total_price_b">

                                        </div>
                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="b_categoer_table" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
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

                            <div role="tabpanel" class="tab-pane" id="labour">

                                <ul class="nav nav-tabs" role="tablist">

                                    <li role="presentation"><a href="#instalment" aria-controls="home" role="tab" data-toggle="tab">Add Instalment</a></li>
                                    <li role="presentation"><a href="#show_instalment" aria-controls="profile" role="tab" data-toggle="tab">Show Instalment</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="instalment">
                                        <form role="form" class="form-horizontal" id="labour_form" action="{{ url('construction/labour_instalment') }}">

                                            
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">Select List</label>

                                                <div class="col-sm-5">
                                                    <select class="form-control" name="labour_type" id="c_category">
                                                        <option> Select </option>
                                                        @foreach ($labourTypes as $row)

                                                            <option> {{ $row->type }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Detail</label>

                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="Detail" id="field-1" placeholder="Add Detail" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Paid</label>

                                                <div class="col-sm-5">
                                                    <input type="number" class="form-control" name="bill_labour" id="field-1" placeholder="Add ammount" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Date</label>
                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="selected_date" id="datepicker" placeholder="Select a date" required>
                                                </div>
                                            </div>
                                            <div class="form-group">


                                                <div class="col-sm-5">
                                                    <input type="hidden" class="form-control" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-3 col-sm-5">
                                                    <button type="submit" class="btn btn-default submit-form">Add</button>
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="show_instalment">
                                        <div class="form-group" style="display: flex;">
                                            <label class="col-sm-3 control-label label_class">Select List</label>

                                            <div class="col-sm-2">
                                                <select class="form-control get__labour" id="labour_value" name="labour_value">
                                                    <option> Select </option>
                                                    @foreach ($labourTypes as $row)

                                                        <option> {{ $row->type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>






                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="labour_table" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
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
                                            <div style="margin-top:30px;" id="total_price_labour">

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- End labour -->

                            <!-- Start Miscellaneous -->
                            <div role="tabpanel" class="tab-pane" id="Misc">

                                <ul class="nav nav-tabs" role="tablist">

                                    <li role="presentation"><a href="#micsc_detail" aria-controls="home" role="tab" data-toggle="tab">Add Detail</a></li>
                                    <li role="presentation"><a href="#show_misc" aria-controls="profile" role="tab" data-toggle="tab">Show Miscellaneous</a></li>

                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane" id="micsc_detail">
                                        <form role="form" class="form-horizontal" id="misc_form" action="{{ url('construction/misc_add') }}">

                                            

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Detail</label>

                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="Detail_misc" id="field-1" placeholder="Add Detail" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-3 control-label">Ammount</label>

                                                <div class="col-sm-5">
                                                    <input type="number" class="form-control" name="ammoun_misc" id="field-1" placeholder="Add Price" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Date</label>
                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" name="selected_date3" id="datepicke6" placeholder="Select a date" required>
                                                </div>
                                            </div>
                                            <div class="form-group">


                                                <div class="col-sm-5">
                                                    <input type="hidden" class="form-control" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-3 col-sm-5">
                                                    <button type="submit" class="btn btn-default submit-form">Add</button>
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="show_misc">
                                        <div class="container" style="width: 90%;margin-top: 15px;">
                                            <div id="misclanious_total"></div>
                                            <table id="misc_table" width="100%" class="table table-bordered">
                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
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

                            <div role="tabpanel" class="tab-pane" id="payment">

                                <ul class="nav nav-tabs" role="tablist">

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

                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="civil_account" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                            <div id="civil_total324"></div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="finish_total">


                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="finish_account" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
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
                                            <div style="margin-top:10px;" id="finish_total324"></div>
                                        </div>

                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="Total_entries">


                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="Total_entries_account" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Detail</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                        <th>Source</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                            <div style="margin-top:10px;" id="finish_total324"></div>
                                        </div>

                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="Miscellaneous_total">


                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="miscle_account" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Detail</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                            <div style="margin-top:10px;" id="miscle_account123"></div>
                                        </div>

                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="Labour_total">


                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="Labour_account" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Date</th>
                                                        <th>Labour</th>
                                                        <th>Detail</th>
                                                        <th>Installment</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                            <div style="margin-top:10px;" id="Labour_account123"></div>
                                        </div>

                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="return">

                                        <ul class="nav nav-tabs" role="tablist">

                                            <li role="presentation"><a href="#return_add" aria-controls="home" role="tab" data-toggle="tab">Return Add</a></li>
                                            <li role="presentation"><a href="#return_show" id="return_tab" aria-controls="profile" role="tab" data-toggle="tab">Return Show</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div role="tabpanel" class="tab-pane" id="return_add">
                                                <form role="form" class="form-horizontal" id="return_form" action="{{ url('construction/return_payment') }}">

                                                    

                                                    <div class="form-group">
                                                        <label for="field-1" class="col-sm-3 control-label">Detail</label>

                                                        <div class="col-sm-5">
                                                            <input type="text" class="form-control" name="Detail_misc" id="field-1" placeholder="Add Detail" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="field-1" class="col-sm-3 control-label">Ammount</label>

                                                        <div class="col-sm-5">
                                                            <input type="number" class="form-control" name="ammoun_misc" id="field-1" placeholder="Add Price" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="field-2" class="col-sm-3 control-label">Date</label>
                                                        <div class="col-sm-5">
                                                            <input type="text" class="form-control" name="selected_date3" id="datepicke3" placeholder="Select a date" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">


                                                        <div class="col-sm-5">
                                                            <input type="hidden" class="form-control" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-3 col-sm-5">
                                                            <button type="submit" class="btn btn-default submit-form">Add</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="return_show">
                                                <div class="container" style="width: 90%;margin-top: 15px;">
                                                    <div id="return_total"></div>
                                                    <table id="return_table" width="100%" class="table table-bordered">
                                                        <thead class="thead-dark">
                                                            <tr style="background-color: aliceblue;">
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


                                        <div class="container" style="width: 90%;margin-top: 15px;">


                                            <table id="grand_account" width="100%" class="table table-bordered">

                                                <thead class="thead-dark">
                                                    <tr style="background-color: aliceblue;">
                                                        <!-- Add your table headers here -->
                                                        <th>Sr No</th>
                                                        <th>Type</th>
                                                        <th>Total</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                            <div style="margin-top:30px;" id="total_price_managments">
                                                <h3 style="display: inline; margin-left:60px;">Payments Recieved: {{ $payment_recieved }}&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspRemaining Balace: {{ $Remainung_Balace }} </h3>
                                            </div>

                                        </div>
                                    </div>
                                </div>


                                <!-- End Payment -->

                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="updateForm">



                                    <div class="form-group">
                                        <label for="field-1">Material</label>

                                        <input type="text" class="form-control" name="type" id="field-1" placeholder="Plot No" required>

                                        <input type="hidden" class="form-control" name="id" id="civil_id" placeholder="Material" required>

                                    </div>



                                    <div class="form-group">
                                        <label for="field-1">Quantity</label>


                                        <input type="text" class="form-control" name="quantity" id="sector" placeholder="Quantity" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Price</label>


                                        <input type="text" class="form-control" name="price" id="field-1" placeholder="Price" required>

                                    </div>



                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="finishModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="finishForm">



                                    <div class="form-group">
                                        <label for="field-1">Material</label>

                                        <input type="text" class="form-control" name="type" id="field-1" placeholder="Plot No" required>

                                        <input type="hidden" class="form-control" name="id" id="finish_id" placeholder="Material" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Detail</label>


                                        <input type="text" class="form-control" name="detail" id="detail" placeholder="detail" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Quantity</label>


                                        <input type="text" class="form-control" name="quantity" id="sector" placeholder="Quantity" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Price</label>


                                        <input type="text" class="form-control" name="price" id="field-1" placeholder="Price" required>

                                    </div>



                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="modal fade" id="openlabourModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Update Labour</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="labourForm">



                                    <div class="form-group">
                                        <label for="field-1">Labour</label>

                                        <input type="text" class="form-control" name="type" id="field-1" placeholder="Labour" required>

                                        <input type="hidden" class="form-control" name="id" id="labour_id" placeholder="Material" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Description</label>


                                        <input type="text" class="form-control" name="description" id="detail" placeholder="Description" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Paid</label>


                                        <input type="text" class="form-control" name="instalmet" id="sector" placeholder="Instalmet" required>

                                    </div>






                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="modal fade" id="openmiscrModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Update Miscellaneous</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="miscForm">



                                    <div class="form-group">
                                        <label for="field-1">Detail</label>

                                        <input type="text" class="form-control" name="detail" id="field-1" placeholder="Detail" required>

                                        <input type="hidden" class="form-control" name="id" id="misc_id" placeholder="Material" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Ammount</label>


                                        <input type="text" class="form-control" name="price" id="detail" placeholder="Ammount" required>

                                    </div>







                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="modal fade" id="openreturnrModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Update Miscellaneous</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Update form goes here -->
                                <form id="returnform">



                                    <div class="form-group">
                                        <label for="field-1">Detail</label>

                                        <input type="text" class="form-control" name="detail" id="field-1" placeholder="Detail" required>

                                        <input type="hidden" class="form-control" name="id" id="return_id" placeholder="Material" required>

                                    </div>

                                    <div class="form-group">
                                        <label for="field-1">Ammount</label>


                                        <input type="text" class="form-control" name="price" id="detail" placeholder="Ammount" required>

                                    </div>







                                    <!-- Add other fields as needed -->

                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
                $('#total_price').html('<h4 style="display: inline; margin-left:60px;">Total Amount: ' + total_price + '</h4>');

                var oAllLinksTable = $('#show_site').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        }, {
                            "data": "date"
                        },
                        {
                            // Combined column for name and buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<div>' +
                                    '<div>' + data.type + '</div>' +
                                    '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal(' + data.id + ')"></i>&nbsp' +
                                    '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deleteCivil(' + data.id + ')"></i>';


                                '</div>';
                            },
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },

                    ],

                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
                            customize: function(win) {


                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert "Civil Material" heading in the table header (centered with colspan="5")
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="5" style="text-align: center;">Civil Material</th>' +
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow); // Insert "Civil Material" heading in the table 

                                // Append total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' +
                                    '<td>Total Quantity: ' + quantity + '</td>' +
                                    '<td>Total Amount: ' + total_price + '</td>' +
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
                            className: 'btn btn-primary',
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
    $('.get__b_category').click(function() {
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

                $('#total_price_b').html('<h4 style="display: inline; margin-left:60px;">Total Amount: ' + total_price + '</h4>');

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
                                return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openfinishModal(' + data.id + ')"></i>&nbsp' +
                                    '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deletefinish(' + data.id + ')"></i>';

                            }
                        }
                    ],
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Finishing Materials</th><th></th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                $(win.document.body).find('table thead th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' +
                                    '<td>Total Quantity: ' + quantity + '</td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }

                        },

                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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
    $('.get__labour').click(function() {
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

                $('#total_price_labour').html('<h4 style="display: inline; margin-left:60px;">Projcet Done: ' + total_priceaaa + '  &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspTotal payed: ' + total_price + '</h4>');

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
                                return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openmiscrModal(' + data.id + ')"></i>&nbsp' +
                                    '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deletelabour(' + data.id + ')"></i>';

                            }
                        }
                    ],
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Labour Instalmennts</th><th></th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                $(win.document.body).find('table thead th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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

                $('#civil_total324').html('<h3 style="display: inline; margin-left:0px;">Total Amount: ' + total_price + '</h3>');

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
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Civil Total</th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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

                $('#misclanious_total').html('<h3 style="display: inline; margin-left:0px;">Total Amount: ' + total_price + '</h3>');

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
                                return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openmiscrModal(' + data.id + ')"></i>&nbsp' +
                                    '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deletemisc(' + data.id + ')"></i>';

                            }
                        }
                    ],
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Miscellaneous</th><th></th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                $(win.document.body).find('table thead th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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

                $('#return_total').html('<h3 style="display: inline; margin-left:0px;">Total Amount: ' + total_price + '</h3>');

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
                                return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openreturnrModal22(' + data.id + ')"></i>&nbsp' +
                                    '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="return_delete(' + data.id + ')"></i>';

                            }
                        }
                    ],
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Return</th><th></th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                $(win.document.body).find('table thead th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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

                $('#finish_total324').html('<h3 style="display: inline; margin-left:0px;">Total Amount: ' + total_price + '</h3>');
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
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Finish Total</th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="5"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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
                    columns: [{
                            data: 'serial_number',
                            title: '#'
                        },
                        {
                            data: 'date',
                            title: 'Date'
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
                         {
                            data: 'source',
                            title: 'source'
                        },
                    ],
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
                            exportOptions: {
                                columns: ':visible'
                            },
                            customize: function(win) {
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
                            className: 'btn btn-primary',
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

                $('#miscle_account123').html('<h3 style="display: inline; margin-left:0px;">Total Amount: ' + total_price + '</h3>');

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
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Miscellaneous Total</th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow);
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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

                $('#Labour_account123').html('<h3 style="display: inline; margin-left:0px;">Total Amount: ' + total_price + '</h3>');

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
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'print',
                            text: 'Print Record',
                            className: 'btn btn-secondary',
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
                                const headingRow1 = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                    '<th colspan="6" style="text-align: center;">Labour Total</th>' + // tableHeader is your variable for the header text
                                    '</tr>';

                                // Prepend the heading row to the table's thead
                                $(win.document.body).find('table thead').prepend(headingRow1);
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + total_price + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btn btn-primary',
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
            dom: 'lBfrtip',
            buttons: [{
                    extend: 'print',
                    text: 'Print Record',
                    className: 'btn btn-secondary',
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
                        const headingRows = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                            '<th colspan="6" style="text-align: center;">Grand Total</th>' + // tableHeader is your variable for the header text
                            '</tr>';
                        $(win.document.body).find('table thead').prepend(headingRows);

                    }
                },
                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btn btn-primary',
                    filename: 'data_export'
                }
            ]
        });

        // Add a new row with values
        dataTable.row.add([
            '1',
            'Civil Total',
            '{{ $civil_price }}',
        ]).draw();

        dataTable.row.add([
            '2',
            'Finish Total',
            '{{ $finish_price }}',
        ]).draw();

        dataTable.row.add([
            '3',
            'Labour total',
            '{{ $labour_price }}',
        ]).draw();

        dataTable.row.add([
            '4',
            'Miscellaneous Total',
            '{{ $misc_price }}',
        ]).draw();

        dataTable.row.add([
            '<h3>5</h3>',
            '<h3>Grand Total</h3>',
            '<h3>{{ $Grand_total }}</h3>',
        ]).draw();
    });
</script>
@endpush
