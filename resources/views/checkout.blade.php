
@extends('layout')
@section('title')
    <title>{{__('user.Checkout')}}</title>
@endsection
@section('meta')
    <meta name="description" content="{{__('user.Checkout')}}">
@endsection
@section('custom-style')
<style>
    .pac-container {
        z-index: 10000 !important;
    }
</style>
@endsection

@section('public-content')

    <!--=============================
        BREADCRUMB START
    ==============================-->
    <section class="tf__breadcrumb" style="background: url({{ asset($breadcrumb) }});">
        <div class="tf__breadcrumb_overlay">
            <div class="container">
                <div class="tf__breadcrumb_text">
                    <h1>{{__('user.Checkout')}}</h1>
                    <ul>
                        <li><a href="{{ route('home') }}">{{__('user.Home')}}</a></li>
                        <li><a href="javascript:;">{{__('user.Checkout')}}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--=============================
        BREADCRUMB END
    ==============================-->


        <!--============================
        CHECK OUT PAGE START
    ==============================-->
    <section class="tf__cart_view mt_125 xs_mt_95 mb_100 xs_mb_70">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-7 wow fadeInUp" data-wow-duration="1s">
                    <div class="tf__checkout_form">
                        <div class="tf__check_form">
                            @if ($addresses->count() > 0)
                                <h5>{{__('user.select address')}} <a href="#" data-bs-toggle="modal" data-bs-target="#address_modal"><i
                                            class="far fa-plus"></i> {{__('user.New Address')}}</a></h5>

                                <div class="tf__address_modal">
                                    <div class="modal fade" id="address_modal" data-bs-backdrop="static"
                                        data-bs-keyboard="false" aria-labelledby="address_modalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="address_modalLabel">{{__('user.add new address')}}
                                                    </h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('store-address-from-checkout') }}" method="POST">
                                                        @csrf

                                                        <div class="row">

                                                            <div class="col-12">
                                                                <div class="tf__check_single_form">
                                                                    <select name="delivery_area_id" class="modal_select2">
                                                                        <option value="">{{__('user.Select Delivery Area')}}</option>
                                                                        @foreach ($delivery_areas as $delivery_area)
                                                                            <option value="{{ $delivery_area->id }}">{{ $delivery_area->area_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 col-lg-12 col-xl-6">
                                                                <div class="tf__check_single_form">
                                                                    <input type="text" placeholder="{{__('user.First Name')}}*" name="first_name">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-lg-12 col-xl-6">
                                                                <div class="tf__check_single_form">
                                                                    <input type="text" placeholder="{{__('user.Last Name')}} *" name="last_name">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 col-lg-12 col-xl-6">
                                                                <div class="tf__check_single_form">
                                                                    <input type="text" placeholder="{{__('user.Phone')}}" name="phone">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-lg-12 col-xl-6">
                                                                <div class="tf__check_single_form">
                                                                    <input type="email" placeholder="{{__('user.Email')}}" name="email">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 col-lg-12 col-xl-12">
                                                                <div class="tf__check_single_form">
                                                                    <input type="text" id="address-input1" placeholder="Enter address">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 col-lg-12 col-xl-12">
                                                                <div class="tf__check_single_form">
                                                                    <textarea name="address" cols="3" rows="4"
                                                                        placeholder="{{__('user.Address')}} *" id="selected-address1"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="tf__check_single_form check_area">
                                                                    <div class="form-check">
                                                                        <input value="home" class="form-check-input" type="radio"
                                                                            name="address_type" id="flexRadioDefault1">
                                                                        <label class="form-check-label"
                                                                            for="flexRadioDefault1">
                                                                            {{__('user.home')}}
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input value="office" class="form-check-input" type="radio"
                                                                            name="address_type" id="flexRadioDefault2">
                                                                        <label class="form-check-label"
                                                                            for="flexRadioDefault2">
                                                                            {{__('user.office')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="tf__check_single_form m-0">
                                                                    <button type="submit" class="common_btn">{{__('user.Save Address')}}</button>
                                                                </div>
                                                            </div>
                                                        </div>


                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    @foreach ($addresses as $address)
                                    <div class="col-md-6">
                                        <div class="tf__checkout_single_address">
                                            <div class="form-check">
                                                <input value="{{ $address->id }}" data-delivery-charge="{{ $address->deliveryArea->delivery_fee }}" class="form-check-input address_id" type="radio" name="address_id"
                                                    id="home-{{ $address->id }}">

                                                    <label class="form-check-label" for="home-{{ $address->id }}">
                                                        @if ($address->type == 'home')
                                                            <span class="icon"><i class="fas fa-home"></i>{{__('user.Home')}}</span>
                                                        @else
                                                            <span class="icon"><i class="far fa-car-building"></i>{{__('user.Office')}}</span>
                                                        @endif
                                                        <span class="address">{{__('user.Name')}} : {{ $address->first_name.' '. $address->last_name }}</span>
                                                        <span class="address">{{__('user.Phone')}} : {{ $address->phone }}</span>
                                                        <span class="address">{{__('user.Delivery area')}} : {{ $address->deliveryArea->area_name }}</span>

                                                        <span class="address">{{__('user.Address')}} : {{ $address->address }}</span>
                                                    </label>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($addresses->count() == 0)
                                <form action="{{ route('store-address-from-checkout') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>{{__('user.add new address')}}</h5>
                                        </div>

                                        <div class="col-12">
                                            <div class="tf__check_single_form">
                                                <select name="delivery_area_id" class="select2">
                                                    <option value="">{{__('user.Select Delivery Area')}}</option>
                                                    @foreach ($delivery_areas as $delivery_area)
                                                        <option value="{{ $delivery_area->id }}">{{ $delivery_area->area_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-12 col-xl-6">
                                            <div class="tf__check_single_form">
                                                <input type="text" placeholder="{{__('user.First Name')}}*" name="first_name">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-12 col-xl-6">
                                            <div class="tf__check_single_form">
                                                <input type="text" placeholder="{{__('user.Last Name')}} *" name="last_name">
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-12 col-xl-6">
                                            <div class="tf__check_single_form">
                                                <input type="text" placeholder="{{__('user.Phone')}}" name="phone">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-12 col-xl-6">
                                            <div class="tf__check_single_form">
                                                <input type="email" placeholder="{{__('user.Email')}}" name="email">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <div class="tf__check_single_form">
                                                <input type="text" id="address-input2" placeholder="Enter address">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <div class="tf__check_single_form">
                                                <textarea name="address" cols="3" rows="4"
                                                    placeholder="{{__('user.Address')}} *" id="selected-address2"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="tf__check_single_form check_area">
                                                <div class="form-check">
                                                    <input value="home" class="form-check-input" type="radio"
                                                        name="address_type" id="flexRadioDefault1">
                                                    <label class="form-check-label"
                                                        for="flexRadioDefault1">
                                                        {{__('user.home')}}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input value="office" class="form-check-input" type="radio"
                                                        name="address_type" id="flexRadioDefault2">
                                                    <label class="form-check-label"
                                                        for="flexRadioDefault2">
                                                        {{__('user.office')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="common_btn">{{__('user.Save Address')}}</button>
                                        </div>
                                    </div>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>

                @php
                    $sub_total = 0;
                    $coupon_price = 0.00;
                @endphp
                @foreach ($cart_contents as $index => $cart_content)
                    @php
                        $item_price = $cart_content->price * $cart_content->qty;
                        $item_total = $item_price + $cart_content->options->optional_item_price;
                        $sub_total += $item_total;
                    @endphp
                @endforeach

                @if (Session::get('coupon_price') && Session::get('offer_type'))
                    @php
                        if(Session::get('offer_type') == 1) {
                            $coupon_price = Session::get('coupon_price');
                            $coupon_price = ($coupon_price / 100) * $sub_total;
                        }else {
                            $coupon_price = Session::get('coupon_price');
                        }
                    @endphp
                @endif

                <div class="col-lg-4 wow fadeInUp" data-wow-duration="1s">
                    <div id="sticky_sidebar" class="tf__cart_list_footer_button tf__cart_list_footer_button_text">
                        <h6>{{__('user.total price')}}</h6>
                        <p>{{__('user.subtotal')}}: <span>{{ $currency_icon }}{{ $sub_total }}</span></p>
                        <p>{{__('user.discount')}} (-): <span>{{ $currency_icon }}{{ $coupon_price }}</span></p>
                        <p>{{__('user.delivery')}} (+): <span class="delivery_charge">{{ $currency_icon }}0.00</span></p>
                        <p class="total"><span>{{__('user.Total')}}:</span> <span class="grand_total">{{ $currency_icon }}{{ $sub_total - $coupon_price }}</span></p>
                        <input type="hidden" id="grand_total" value="{{ $sub_total - $coupon_price }}">
                        <form action="{{ route('apply-coupon-from-checkout') }}">
                            <input name="coupon" type="text" placeholder="{{__('user.Coupon Code')}}">
                            <button type="submit">{{__('user.apply')}}</button>
                        </form>
                        <a class="common_btn" href="javascript:;" id="continue_to_pay">{{__('user.Continue to pay')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--============================
        CHECK OUT PAGE END
    ==============================-->

    <script>
        /**
         * @license
         * Copyright 2024 Google LLC. All Rights Reserved.
         * SPDX-License-Identifier: Apache-2.0
         */
        async function initAutocomplete() {
          // Initialize the Autocomplete service for the first input
          const addressInput1 = document.getElementById("address-input1");
          const selectedAddress1 = document.getElementById("selected-address1");
          const autocomplete1 = new google.maps.places.Autocomplete(addressInput1);
          autocomplete1.setFields(['address_components', 'formatted_address', 'name']);
          autocomplete1.addListener('place_changed', () => {
            const place1 = autocomplete1.getPlace();
            selectedAddress1.textContent = place1.formatted_address;
          });

          // Initialize the Autocomplete service for the second input
          const addressInput2 = document.getElementById("address-input2");
          const selectedAddress2 = document.getElementById("selected-address2");
          const autocomplete2 = new google.maps.places.Autocomplete(addressInput2);
          autocomplete2.setFields(['address_components', 'formatted_address', 'name']);
          autocomplete2.addListener('place_changed', () => {
            const place2 = autocomplete2.getPlace();
            selectedAddress2.textContent = place2.formatted_address;
          });
        }
      </script>
      <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2EqH1cqg0L0yTJ86hiGsr_ZAfEl1khss&libraries=places&callback=initAutocomplete" async defer></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function () {

                $("input[name='address_id']").on("change", function() {
                    var delivery_id = $("input[name='address_id']:checked").val();
                    var deliveryCharge = $("input[name='address_id']:checked").data('delivery-charge');

                    $(".delivery_charge").html(`{{ $currency_icon }}${deliveryCharge}`);
                    let grand_total = $("#grand_total").val();
                    grand_total = parseInt(grand_total) + parseInt(deliveryCharge);
                    $(".grand_total").html(`{{ $currency_icon }}${grand_total}`);

                    $.ajax({
                        type: 'get',
                        data: {delivery_id : delivery_id, charge : deliveryCharge},
                        url: "{{ url('/set-delivery-charge') }}",
                        success: function (response) {
                            console.log(response);
                        },
                        error: function(response) {
                            toastr.error("{{__('user.Server error occured')}}")
                        }
                    });


                });


                $("#continue_to_pay").on("click", function(e){
                    e.preventDefault();
                    if ($("input[name='address_id']").is(":checked")) {
                        window.location.href = "{{ route('payment') }}";
                    } else {
                        toastr.error("{{__('user.Please select an address')}}")
                    }
                });

            });
        })(jQuery);
    </script>



@endsection
