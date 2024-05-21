@extends('admin.master_layout')
@section('title')
<title>{{__('admin.POS')}}</title>
@endsection
@section('admin-content')
      <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{__('admin.POS')}}</h1>
            </div>

            <div class="section-body">
                <div class="row mt-4">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <form id="product_search_form">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="name" placeholder="{{__('admin.Search here..')}}" autocomplete="off" value="{{ request()->get('name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <select name="category_id" id="category_id" class="form-control">
                                                <option value="">{{__('admin.Select Category')}}</option>
                                                @if (request()->has('category_id'))
                                                    @foreach ($categories as $category)
                                                    <option {{ request()->get('category_id') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                @else
                                                    @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary" id="search_btn_text">{{__('admin.Search')}}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="card-body product_body">

                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header" style="display: none;">
                                <div class="row w-100">
                                    <div class="col-md-8">
                                        <select name="customer_id" id="customer_id" class="form-control select2">
                                            <!-- <option value="">{{__('admin.Select Customer')}}</option> -->
                                            @foreach ($customers as $customer)
                                                @if ($customer->mobile == 0 || $customer->mobile == NULL)
                                                    <option value="{{ $customer->id }}" data-order-count="{{ $customer->orderCount }}">{{ $customer->name }} - {{ $customer->phone }} - {{ $customer->orderCount }}</option>
                                                @else
                                                    <option value="{{ $customer->id }}" data-order-count="{{ $customer->orderCount }}">{{ $customer->name }} - {{ $customer->mobile }} - {{ $customer->orderCount }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button data-toggle="modal" data-target="#createNewUser" type="button" class="btn btn-primary w-100"><i class="fa fa-plus" aria-hidden="true"></i>{{__('admin.New')}}</button>
                                    </div>
                                </div>

                            </div>

                            <div class="card-body">
                                <h5 style="display: flex; align-items:center; gap:0.5rem;">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    Place a new order
                                <!-- <button id="createNewAddressBtn" class="btn btn-primary btn-sm"><i class="fa fa-plus" aria-hidden="true"></i></button> -->
                                </h5>

                                <div class="shopping-card-body">
                                    <table class="table">
                                        <thead>
                                            <th>{{__('admin.Item')}}</th>
                                            <th>{{__('admin.Qty')}}</th>
                                            <th>{{__('admin.Price')}}</th>
                                            <th>{{__('admin.Action')}}</th>
                                        </thead>
                                        <tbody>
                                            @php
                                                $sub_total = 0;
                                                $coupon_price = 0.00;
                                            @endphp
                                            @foreach ($cart_contents as $cart_index => $cart_content)
                                                <tr>
                                                    <td>
                                                        <p>{{ $cart_content->name }}</p>

                                                    </td>
                                                    <td data-rowid="{{ $cart_content->rowId }}">
                                                        <input min="1" type="number" value="{{ $cart_content->qty }}" class="pos_input_qty">
                                                    </td>

                                                    @php
                                                        $item_price = $cart_content->price * $cart_content->qty;
                                                        $item_total = $item_price + $cart_content->options->optional_item_price;
                                                        $sub_total += $item_total;
                                                    @endphp

                                                    <td>{{ $currency_icon }}{{ $item_total }}</td>
                                                    <td>
                                                        <a href="javascript:;" onclick="removeCartItem('{{ $cart_content->rowId }}')"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>

                                    <div>
                                        <p><span>{{__('admin.Subtotal')}}</span> : <span>{{ $currency_icon }}{{ $sub_total }}</span></p>
                                        <p><span>{{__('admin.GST')}}</span> : <span id="report_delivery_fee">{{ $currency_icon }}0.00</span></p>

                                        <p><span>{{__('admin.Total')}}</span> : <span id="report_total_fee">{{ $currency_icon }}{{ $sub_total -  $coupon_price}}</span></p>
                                    </div>

                                    <input type="hidden" id="cart_sub_total" value="{{ $sub_total }}">

                                </div>
                                <p> Discount (-): <span id="report_couon_price">{{ $currency_icon }}0.00</span></p>
                                <br>
                                <div id="order_count_display" style="display:none;">
                                    <form id="coupon_form">
                                        <div class="input-group w-50">
                                            <input name="coupon" type="text" placeholder="{{__('user.Coupon Code')}}" class="form-control rounded-3 mr-2">
                                            <button type="submit" class="btn btn-success">{{__('user.apply')}}</button>
                                        </div>
                                    </form>
                                </div>
                                <br>


                                <div>
                                    <button id="placeOrderBtn" class="btn btn-success">{{__('admin.Place order')}}</button>
                                    <a href="{{ route('admin.cart-clear') }}" class="btn btn-danger">{{__('admin.Reset')}}</a>
                                </div>

                                <form id="placeOrderForm" action="{{ route('admin.place-order') }}" method="POST">
                                    @csrf
                                    <input type="hidden" value="{{ $sub_total }}" name="sub_total" id="order_sub_total">
                                    <input type="hidden" value="5" name="customer_id" id="order_customer_id">
                                    <input type="hidden" value="3" name="address_id" id="order_address_id">
                                    <input type="hidden" value="0.00" name="coupon_price" id="couon_price">
                                    <input type="hidden" value="0.00" name="delivery_fee" id="order_delivery_fee">
                                    <input type="hidden" value="{{ $sub_total }}" name="total_fee" id="order_total_fee">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <!-- Product Modal -->
    <div class="tf__dashboard_cart_popup">
        <div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <div class="modal-body">
                        <div class="load_product_modal_response">

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



    <!-- Create new user modal -->
    <div class="modal fade" id="createNewUser" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                    <div class="modal-header">
                            <h5 class="modal-title">{{__('admin.Create new customer')}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                        </div>
                <div class="modal-body">
                    <div class="">
                       <form id="createNewUserForm" method="POST">
                        @csrf
                            <div class="form-group">
                                <label for="">{{__('admin.Name')}} <span class="text-danger">*</span></label>
                                <input type="text" name="name" autocomplete="off" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('admin.Email')}} <span class="text-danger">*</span></label>
                                <input type="email" name="email" autocomplete="off" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('admin.Phone')}} <span class="text-danger">*</span></label>
                                <input type="text" name="phone" autocomplete="off" class="form-control">
                            </div>


                            <button class="btn btn-primary" type="submit">{{__('Save')}}</button>

                       </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Create New Address Modal -->
    <div class="modal fade" id="newAddress" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                    <div class="modal-header">
                            <h5 class="modal-title">{{__('admin.New address')}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                        </div>
                <div class="modal-body">
                    <div class="">
                        <form id="add_new_address_form" method="POST">
                            @csrf
                            <div class="row">

                                <input type="hidden" name="customer_id" value="5" id="address_customer_id">
                                <div class="form-group col-12">
                                    <label for="">{{__('admin.Delivery area')}} *</label>
                                    <select name="delivery_area_id" class="select2">
                                        <!-- <option value="">{{__('admin.Select Delivery Area')}}</option> -->
                                        @foreach ($delivery_areas as $delivery_area)
                                            <option value="{{ $delivery_area->id }}" @if($delivery_area->area_name === 'Punjabi Paradise') selected @endif>{{ $delivery_area->area_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-12 col-xl-6 form-group">
                                    <label for="">{{__('admin.Last Name')}} *</label>
                                    <input class="form-control" type="text" placeholder="{{__('admin.Last Name')}}" name="last_name" value="temp">
                                </div>

                                <div class="col-md-6 col-lg-12 col-xl-6 form-group">

                                    <label for="">{{__('admin.Phone')}}</label>
                                    <input class="form-control" type="text" placeholder="{{__('admin.Phone')}}" name="phone" value="temp">
                                </div>
                                <div class="col-md-6 col-lg-12 col-xl-6 form-group">
                                    <label for="">{{__('admin.Email')}}</label>
                                    <input class="form-control" type="email" placeholder="{{__('admin.Email')}}" name="email" value="temp@temp.temp">
                                </div>
                                <div class="col-md-12 col-lg-12 col-xl-12 form-group">
                                    <label for="">{{__('admin.Address')}} *</label>
                                    <input class="form-control" type="text" placeholder="{{__('admin.Address')}}" name="address" value="Punjabi Paradise">
                                </div>
                                <div class="col-12 form-group">
                                    <div class="wsus__check_single_form check_area d-flex flex-wrap">
                                        <div class="form-check">
                                            <input value="home" class="form-check-input" type="radio"
                                                name="address_type" id="flexRadioDefault1" checked>
                                            <label class="form-check-label"
                                                for="flexRadioDefault1">
                                                {{__('admin.Home')}}
                                            </label>
                                        </div>
                                        <div class="form-check ml-4">
                                            <input value="office" class="form-check-input" type="radio"
                                                name="address_type" id="flexRadioDefault2">
                                            <label class="form-check-label"
                                                for="flexRadioDefault2">
                                                {{__('admin.Office')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">

                                    <button type="submit" class="btn btn-primary">{{__('admin.Save Address')}}</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>




<script>



    (function($) {
        "use strict";
        $(document).ready(function () {

            $("#coupon_form").on("submit", function(e){
                    e.preventDefault();

                    $.ajax({
                        type: 'get',
                        data: $('#coupon_form').serialize(),
                        url: "{{ url('/apply-coupon') }}",
                        success: function (response) {
                            toastr.success(response.message)
                            $("#coupon_form").trigger("reset");

                            $("#couon_price").val(response.discount);
                            $("#couon_offer_type").val(response.offer_type);

                            calculateTotalFee();
                        },
                        error: function(response) {
                            if(response.status == 422){
                                if(response.responseJSON.errors.coupon)toastr.error(response.responseJSON.errors.coupon[0])
                            }

                            if(response.status == 500){
                                toastr.error("{{__('user.Server error occured')}}")
                            }

                            if(response.status == 403){
                                toastr.error(response.responseJSON.message)
                            }
                        }
                    });
                })
            loadProudcts()
            $(".pos_input_qty").on("change keyup", function(e){

                let quantity = $(this).val();
                let parernt_td = $(this).parents('td');
                let rowid = parernt_td.data('rowid')

                $.ajax({
                    type: 'get',
                    data: {rowid, quantity},
                    url: "{{ route('admin.cart-quantity-update') }}",
                    success: function (response) {
                        $(".shopping-card-body").html(response)
                        calculateTotalFee();
                    },
                    error: function(response) {
                        if(response.status == 500){
                            toastr.error("{{__('admin.Server error occured')}}")
                        }

                        if(response.status == 403){
                            toastr.error("{{__('admin.Server error occured')}}")
                        }
                    }
                });

            });

                console.log("pohanch gaya");
                function updateOrderCount() {
                    var selectedIndex = this.selectedIndex;
                    var selectedOption = this.options[selectedIndex];
                    var orderCount = selectedOption.getAttribute('data-order-count');

                    // Adding the form for coupon code
                    if(orderCount > 0){
                        $("#order_count_display").show();
                    }else{
                        $("#order_count_display").hide();
                    }
                }

                function updateCustomerID() {
                    var customer_id = $(this).val();
                    $("#address_customer_id").val(customer_id);
                    $("#order_customer_id").val(customer_id);
                }

                $("#customer_id").on("change", updateCustomerID);
                $("#customer_id").on("change", updateOrderCount);

            $("#createNewAddressBtn").on("click", function(){
                let customer_id = $("#customer_id").val();
                customer_id = 5;
                console.log("Update 1")
                if(customer_id){
                    $("#newAddress").modal('show');
                }else{
                    toastr.error("{{__('admin.Please select a customer')}}")
                }

            })

            $("#add_new_address_form").on("submit", function(e){
                e.preventDefault();

                var isDemo = "{{ env('APP_MODE') }}"
                if(isDemo == 0){
                    toastr.error('This Is Demo Version. You Can Not Change Anything');
                    return;
                }

                $.ajax({
                    type: 'POST',
                    data: $('#add_new_address_form').serialize(),
                    url: "{{ route('admin.create-new-address') }}",
                    success: function (response) {
                        toastr.success(response.message)
                        // $("#add_new_address_form").trigger("reset");
                        // $("#order_address_id").val(response.address.id);
                        $("#order_delivery_fee").val(response.delivery_fee);
                        $("#newAddress").modal('hide');

                        calculateTotalFee();
                    },
                    error: function(response) {
                        if(response.status == 422){
                            // if(response.responseJSON.errors.first_name)toastr.error(response.responseJSON.errors.first_name[0])
                            // if(response.responseJSON.errors.last_name)toastr.error(response.responseJSON.errors.last_name[0])
                            // if(response.responseJSON.errors.address)toastr.error(response.responseJSON.errors.address[0])
                            // if(response.responseJSON.errors.address_type)toastr.error(response.responseJSON.errors.address_type[0])
                            // if(response.responseJSON.errors.delivery_area_id)toastr.error(response.responseJSON.errors.delivery_area_id[0])
                            // if(response.responseJSON.errors.customer_id)toastr.error(response.responseJSON.errors.customer_id[0])

                        }

                        if(response.status == 500){
                            toastr.error("{{__('admin.Server error occured')}}")
                        }

                        if(response.status == 403){
                            toastr.error(response.responseJSON.message);
                        }
                    }
                });
            })

            $("#placeOrderBtn").on("click", function(){

                let customer_id = $("#order_customer_id").val();
                customer_id = 5;
                if(!customer_id){
                    toastr.error("{{__('admin.Please select a customer')}}")
                    return;
                }
                $("#placeOrderForm").submit();
            })

            $("#createNewUserForm").on("submit", function(e){
                e.preventDefault();

                var isDemo = "{{ env('APP_MODE') }}"
                if(isDemo == 0){
                    toastr.error('This Is Demo Version. You Can Not Change Anything');
                    return;
                }

                $.ajax({
                    type: 'POST',
                    data: $('#createNewUserForm').serialize(),
                    url: "{{ route('admin.create-new-customer') }}",
                    success: function (response) {
                        toastr.success(response.message)
                        $("#createNewUserForm").trigger("reset");
                        $("#createNewUser").modal('hide');

                        $("#customer_id").html(response.customer_html)

                    },
                    error: function(response) {
                        if(response.status == 422){
                            if(response.responseJSON.errors.name)toastr.error(response.responseJSON.errors.name[0])
                            if(response.responseJSON.errors.email)toastr.error(response.responseJSON.errors.email[0])
                            if(response.responseJSON.errors.phone)toastr.error(response.responseJSON.errors.phone[0])

                        }

                        if(response.status == 500){
                            toastr.error("{{__('admin.Server error occured')}}")
                        }

                        if(response.status == 403){
                            toastr.error(response.responseJSON.message);
                        }

                    }
                });

            })

            $("#product_search_form").on("submit", function(e){
                e.preventDefault();

                $("#search_btn_text").html(`{{__('admin.Searching...')}}`)

                $.ajax({
                    type: 'get',
                    data: $('#product_search_form').serialize(),
                    url: "{{ route('admin.load-products') }}",
                    success: function (response) {
                        $("#search_btn_text").html(`{{__('admin.Search')}}`)
                        $(".product_body").html(response)
                    },
                    error: function(response) {
                        $("#search_btn_text").html(`{{__('admin.Search')}}`)

                        if(response.status == 500){
                            toastr.error("{{__('admin.Server error occured')}}")
                        }

                        if(response.status == 403){
                            toastr.error(response.responseJSON.message);
                        }

                    }
                });
            })



        });
    })(jQuery);

    function load_product_model(product_id){

        $.ajax({
            type: 'get',
            url: "{{ url('admin/pos/load-product-modal') }}" + "/" + product_id,
            success: function (response) {
                $(".load_product_modal_response").html(response)
                $("#cartModal").modal('show');
            },
            error: function(response) {
                toastr.error("{{__('user.Server error occured')}}")
            }
        });
    }

    function removeCartItem(rowId){

        $.ajax({
            type: 'get',
            url: "{{ url('admin/pos/remove-cart-item') }}" + "/" + rowId,
            success: function (response) {
                $(".shopping-card-body").html(response)

                calculateTotalFee();
                toastr.success("{{__('admin.Remove successfully')}}")
            },
            error: function(response) {
                toastr.error("{{__('user.Server error occured')}}")
            }
        });
    }

    function calculateTotalFee(){

        let order_delivery_fee = $("#order_delivery_fee").val();
        let cart_sub_total = $("#cart_sub_total").val();
        let coupon_price = $("#couon_price").val();
        let couon_offer_type = $("#couon_offer_type").val();

        let apply_coupon_price = 0.00;
            if(couon_offer_type == 1) {
                let percentage = parseInt(coupon_price) / parseInt(100)
                apply_coupon_price = (parseFloat(percentage) * parseFloat(sub_total));
            }else{
                apply_coupon_price = coupon_price;
            }

        let order_total_fee = parseInt(order_delivery_fee) + parseInt(cart_sub_total) - parseInt(coupon_price);
        $("#order_total_fee").val(cart_sub_total);
        $("#coupon_price").val(coupon_price);
        let order_sub_total = $("#order_sub_total").val();

        $("#report_delivery_fee").html(`{{ $currency_icon }}${order_delivery_fee}`);
        $("#report_couon_price").html(`{{ $currency_icon }}${coupon_price}`);
        $("#report_total_fee").html(`{{ $currency_icon }}${order_total_fee}`);

    }

    function loadProudcts(){
        $.ajax({
            type: 'get',
            url: "{{ route('admin.load-products') }}",
            success: function (response) {
                $(".product_body").html(response)
            },
            error: function(response) {
                toastr.error("{{__('user.Server error occured')}}")
            }
        });
    }

    function loadPagination(url){
        $.ajax({
            type: 'get',
            url: url,
            success: function (response) {
                $(".product_body").html(response)
            },
            error: function(response) {
                toastr.error("{{__('user.Server error occured')}}")
            }
        });
    }
</script>
@endsection
