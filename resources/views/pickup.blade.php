
@extends('layout')
@section('title')
    <title>{{__('user.Checkout')}}</title>
@endsection
@section('meta')
    <meta name="description" content="{{__('user.Checkout')}}">
@endsection

@section('public-content')

    <!--=============================
        BREADCRUMB START
    ==============================-->
    <section class="tf__breadcrumb" style="background: url({{ asset($breadcrumb) }});">
        <div class="tf__breadcrumb_overlay">
            <div class="container">
                <div class="tf__breadcrumb_text">
                    <h1>PickUp</h1>
                    <ul>
                        <li><a href="{{ route('home') }}">{{__('user.Home')}}</a></li>
                        <li><a href="javascript:;">PickUp</a></li>
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
                            <button class="common_btn"onclick="getLocation()">Find Restaurants Near Me</button><br><br>
                            <div style = "height:400px; width:400px; border-radius : 25px;" id="map"></div>
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
        var map;

        function initMap() {
        console.log("Initializing map...");
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -34.397, lng: 150.644}, // Default center
            zoom: 15 // Default zoom level
            });
        }

function getLocation() {
    console.log("Getting user location...");
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, function(error) {
            console.log("Error getting location:", error);
        });
    } else {
        console.log("Geolocation is not supported by this browser.");
    }
}

function showPosition(position) {
    console.log("Showing user position...");
    var userLocation = { lat: position.coords.latitude, lng: position.coords.longitude };
    map.setCenter(userLocation);

    var marker = new google.maps.Marker({
        position: userLocation,
        map: map,
        title: 'Your Location'
    });

    marker.addListener('click', function() {
        alert('You are here!');
    });

    // AJAX request to fetch nearby restaurants and add markers
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'loc?lat=' + position.coords.latitude + '&lng=' + position.coords.longitude);
    xhr.send();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var restaurants = JSON.parse(xhr.responseText);
                console.log('Nearby restaurants:', restaurants);
                restaurants.forEach(function(restaurant) {
                    var restaurantLocation = { lat: restaurant.latitude, lng: restaurant.longitude };
                    var restaurantMarker = new google.maps.Marker({
                        position: restaurantLocation,
                        map: map,
                        title: restaurant.name
                    });

                    restaurantMarker.addListener('click', function() {
                        alert('You clicked on ' + restaurant.name);
                        // You can add more actions here when a restaurant marker is clicked
                    });
                });
            } else {
                console.log('Error fetching nearby restaurants. Status:', xhr.status);
            }
        }
    };
    
}
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2EqH1cqg0L0yTJ86hiGsr_ZAfEl1khss&callback=initMap"></script>
    <script>
        (function($) {
            "use strict";
                $(document).ready(function () {
                    $("input[name='address_id']").on("change", function() {
                        var delivery_id = $("input[name='address_id']:checked").val();

                        // Ajax call to update delivery charge
                        $.ajax({
                            type: 'get',
                            data: {delivery_id : delivery_id},
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
                    window.location.href = "{{ route('payment') }}";
                });

            });
        })(jQuery);
    </script>



@endsection
