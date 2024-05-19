@extends('layout')
@section('title')
<title>{{ $seo_setting->seo_title }}</title>
@endsection
@section('meta')
<meta name="description" content="{{ $seo_setting->seo_description }}">
@endsection
@section('public-content')
<!--=============================
        BANNER START
    ==============================-->
<section class="tf__banner">
    <div class="tf__banner_overlay">
        <div class="col-12">
            <div class="tf__banner_slider" style="background: url({{ asset($setting->slider_background) }});">
                <div class="tf__banner_slider_overlay">
                    <div class=" container">
                        <div class="row justify-content-center">
                            <div class="col-xxl-6 col-xl-6 col-md-10 col-lg-6">
                                <div class="tf__banner_text wow fadeInLeft" data-wow-duration="1s">
                                    <h3>{{ $setting->slider_header_one }}</h3>
                                    <h1>{{ $setting->slider_header_two }}</h1>
                                    <p>{{ $setting->slider_description }}</p>
                                    <form action="{{ route('products') }}">
                                        <input type="text" placeholder="{{__('user.Type here..')}}" name="search">
                                        <button type="submit" class="common_btn">{{__('user.search')}}</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-xxl-5 col-xl-6 col-sm-10 col-md-9 col-lg-6">
                                <div class="tf__banner_img wow fadeInRight" data-wow-duration="1s">
                                    <div class="img">
                                        <img src="{{ asset($setting->slider_offer_image) }}" alt="food item" class="img-fluid w-100">
                                        <span>
                                            {{ $setting->slider_offer_text }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=============================
        BANNER END
    ==============================-->
<!--=============================
        OFFER ITEM START
    ==============================-->
<section class="tf__offer_item pt_95 pb_100 xs_pt_65 xs_pb_70">
    <div class="container">
        <div class="row wow fadeInUp" data-wow-duration="1s">
            <div class="col-md-8 col-lg-7 col-xl-6">
                <div class="tf__section_heading mb_25">
                    <h4>{{__('user.daily offer')}}</h4>
                    <h2>{{__('user.up to 75% off for this day')}}</h2>
                </div>
            </div>
        </div>
        <div class="row offer_item_slider wow fadeInUp" data-wow-duration="1s">
            @foreach ($today_special_product->products as $product)
            <div class="col-xl-4">
                <div class="tf__offer_item_single" style="background: url({{ asset($product->thumb_image) }});">
                    @if ($product->is_offer)
                    <span>{{ $product->offer }}% {{__('user.off')}}</span>
                    @endif
                    <a class="title" href="{{ route('show-product', $product->slug) }}">{{ $product->name }}</a>
                    <p>{{ $product->short_description }}</p>
                    <ul class="d-flex flex-wrap">
                        <li><a href="javascript:;" onclick="load_product_model({{ $product->id }})"><i class="fas fa-shopping-basket"></i></a></li>
                        @auth('web')
                        <li><a href="javascript:;" onclick="add_to_wishlist({{ $product->id }})"><i class="fal fa-heart"></i></a></li>
                        @else
                        <li><a href="javascript:;" onclick="before_auth_wishlist({{ $product->id }})"><i class="fal fa-heart"></i></a></li>
                        @endauth
                        <li><a href="{{ route('show-product', $product->slug) }}"><i class="far fa-eye"></i></a></li>
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
<!--=============================
        OFFER ITEM END
    ==============================-->


<!--=============================
        RESERVATION START
    ==============================-->
<section class="tf__reservation mt_100 xs_mt_70">
    <div class="container">
        <div class="tf__reservation_bg" style="background: url({{ asset($setting->appointment_bg) }});">
            <div class="row">
                <div class="col-xl-6 ms-auto">
                    <div class="tf__reservation_form wow fadeInRight" data-wow-duration="1s">
                        <h2>{{__('user.book a table')}}</h2>
                        <form method="POST" action="{{ route('store-reservation') }}">
                            @csrf
                            @auth('web')
                            @php
                            $auth_user = Auth::guard('web')->user();
                            @endphp
                            <div class="row">
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="name">{{__('user.Name')}}</label>
                                        <input type="text" id="name" placeholder="{{__('user.Name')}}" name="name" value="{{ $auth_user->name }}" required readonly>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="email">{{__('user.Email')}}</label>
                                        <input type="email" id="email" placeholder="{{__('user.Email')}}" name="email" value="{{ $auth_user->email }}" required readonly>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="phone">{{__('user.Phone')}}</label>
                                        <input type="text" id="phone" placeholder="{{__('user.Phone')}}" name="phone" value="{{ $auth_user->phone }}" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="date">{{__('user.Select date')}}</label>
                                        <input type="date" id="date" name="reserve_date" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label>{{__('user.Select Time')}}</label>
                                        <select class="reservation_input select_js" name="reserve_time" required>
                                            <option value="">{{__('user.Select Time')}}</option>
                                            <option value="12:00 AM - 01:00 AM">12:00 AM - 01:00 AM</option>
                                            <option value="01:00 AM - 02:00 AM">01:00 AM - 02:00 AM</option>
                                            <option value="02:00 AM - 03:00 AM">02:00 AM - 03:00 AM</option>
                                            <option value="03:00 AM - 04:00 AM">03:00 AM - 04:00 AM</option>
                                            <option value="04:00 AM - 05:00 AM">04:00 AM - 05:00 AM</option>
                                            <option value="05:00 AM - 06:00 AM">05:00 AM - 06:00 AM</option>
                                            <option value="06:00 AM - 07:00 AM">06:00 AM - 07:00 AM</option>
                                            <option value="07:00 AM - 08:00 AM">07:00 AM - 08:00 AM</option>
                                            <option value="08:00 AM - 09:00 AM">08:00 AM - 09:00 AM</option>
                                            <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                                            <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                                            <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                                            <option value="12:00 PM - 01:00 PM">12:00 PM - 01:00 PM</option>
                                            <option value="01:00 PM - 02:00 PM">01:00 PM - 02:00 PM</option>
                                            <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                                            <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                                            <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                                            <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                                            <option value="06:00 PM - 07:00 PM">06:00 PM - 07:00 PM</option>
                                            <option value="07:00 PM - 08:00 PM">07:00 PM - 08:00 PM</option>
                                            <option value="08:00 PM - 09:00 PM">08:00 PM - 09:00 PM</option>
                                            <option value="09:00 PM - 10:00 PM">09:00 PM - 10:00 PM</option>
                                            <option value="10:00 PM - 11:00 PM">10:00 PM - 11:00 PM</option>
                                            <option value="11:00 PM - 12:00 AM">11:00 PM - 12:00 AM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="Person">{{__('user.Person')}}</label>
                                        <input type="number" id="Person" placeholder="{{__('user.Person')}}" name="person" required>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <button type="submit" class="common_btn">{{__('user.confirm')}}</button>
                                </div>
                            </div>
                            @else
                            <div class="row">
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="name">{{__('user.Name')}}</label>
                                        <input type="text" id="name" placeholder="{{__('user.Name')}}" name="name">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="email">{{__('user.Email')}}</label>
                                        <input type="email" id="email" placeholder="{{__('user.Email')}}" name="email">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="phone">{{__('user.Phone')}}</label>
                                        <input type="text" id="phone" placeholder="{{__('user.Phone')}}" name="phone">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="date">{{__('user.Select date')}}</label>
                                        <input type="date" id="date" name="reserve_date">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label>{{__('user.Select Time')}}</label>
                                        <select class="reservation_input select_js" name="reserve_time">
                                            <option value="">{{__('user.Select Time')}}</option>
                                            <option value="12:00 AM - 01:00 AM">12:00 AM - 01:00 AM</option>
                                            <option value="01:00 AM - 02:00 AM">01:00 AM - 02:00 AM</option>
                                            <option value="02:00 AM - 03:00 AM">02:00 AM - 03:00 AM</option>
                                            <option value="03:00 AM - 04:00 AM">03:00 AM - 04:00 AM</option>
                                            <option value="04:00 AM - 05:00 AM">04:00 AM - 05:00 AM</option>
                                            <option value="05:00 AM - 06:00 AM">05:00 AM - 06:00 AM</option>
                                            <option value="06:00 AM - 07:00 AM">06:00 AM - 07:00 AM</option>
                                            <option value="07:00 AM - 08:00 AM">07:00 AM - 08:00 AM</option>
                                            <option value="08:00 AM - 09:00 AM">08:00 AM - 09:00 AM</option>
                                            <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                                            <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                                            <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                                            <option value="12:00 PM - 01:00 PM">12:00 PM - 01:00 PM</option>
                                            <option value="01:00 PM - 02:00 PM">01:00 PM - 02:00 PM</option>
                                            <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                                            <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                                            <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                                            <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                                            <option value="06:00 PM - 07:00 PM">06:00 PM - 07:00 PM</option>
                                            <option value="07:00 PM - 08:00 PM">07:00 PM - 08:00 PM</option>
                                            <option value="08:00 PM - 09:00 PM">08:00 PM - 09:00 PM</option>
                                            <option value="09:00 PM - 10:00 PM">09:00 PM - 10:00 PM</option>
                                            <option value="10:00 PM - 11:00 PM">10:00 PM - 11:00 PM</option>
                                            <option value="11:00 PM - 12:00 AM">11:00 PM - 12:00 AM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="tf__reservation_input_single">
                                        <label for="Person">{{__('user.Person')}}</label>
                                        <input type="number" id="Person" placeholder="{{__('user.Person')}}" name="person">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <button type="button" class="common_btn" onclick="before_auth_wishlist()">{{__('user.confirm')}}</button>
                                </div>
                            </div>
                            @endauth
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=============================
        RESERVATION END
    ==============================-->
<!--=============================
        MENU ITEM START
    ==============================-->
<section class="tf__menu mt_95 xs_mt_65">
    <div class="container-fluid">
        <div class="scroll-sticky-container">
            <li class="left-arrow category-arrow"><div class="fas fa-chevron-left left-arrow-image"></div></li>
            <li class="right-arrow category-arrow"><div class="fas fa-chevron-right right-arrow-image"></div></li>
            <div class="row horizontal-scroll">
                <div class="col-12 menu-fil d-flex category-container">
                    <ul class="category-list">
                        @foreach ($menu_section->categories as $menu_category)
                        <li><a href="#category_{{ $menu_category->id }}"><b>{{ $menu_category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @foreach ($menu_section->categories as $menu_category)
        <div id="category_{{ $menu_category->id }}" class="row">
            <h4 class="category-heading">{{ $menu_category->name }}</h4>
        </div>
        <div class="row grid">
            @foreach ($menu_section->products as $menu_product)
            @if ($menu_product->category_id == $menu_category->id)
            <div class="col-xxl-3 col-sm-6 col-lg-4">
                <div class="tf__menu_item">
                    <div class="tf__menu_item_img">
                        <img src="{{ asset($menu_product->thumb_image) }}" alt="menu" class="img-fluid w-100">
                    </div>
                    <div class="tf__menu_item_text">
                        <a class="category" href="{{ route('products',['category' => $menu_product->category->slug]) }}">{{ $menu_product->category->name }}</a>
                        <a class="title" href="{{ route('show-product', $menu_product->slug) }}">{{ $menu_product->name }}</a>
                        @php
                        if ($menu_product->total_review > 0) {
                        $average = $menu_product->average_rating;
                        $int_average = intval($average);
                        $next_value = $int_average + 1;
                        $review_point = $int_average;
                        $half_review=false;
                        if($int_average < $average && $average < $next_value){ $review_point=$int_average + 0.5; $half_review=true; } } @endphp <p class="rating">
                            @if ($menu_product->total_review > 0)
                            @for ($i = 1; $i <=5; $i++) @if ($i <=$review_point) <i class="fas fa-star"></i>
                                @elseif ($i> $review_point )
                                @if ($half_review==true)
                                <i class="fas fa-star-half-alt"></i>
                                @php
                                $half_review=false
                                @endphp
                                @else
                                <i class="far fa-star"></i>
                                @endif
                                @endif
                                @endfor
                                @else
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                @endif
                                <span>({{ $menu_product->total_review }})</span>
                                </p>
                                @if ($menu_product->is_offer)
                                <h5 class="price">{{ $currency_icon }}{{ $menu_product->offer_price }} <del>{{ $currency_icon }}{{ $menu_product->price }}</del> </h5>
                                @else
                                <h5 class="price">{{ $currency_icon }}{{ $menu_product->price }}</h5>
                                @endif
                                <a class="tf__add_to_cart" href="javascript:;" onclick="load_product_model({{ $menu_product->id }})">{{__('user.add to cart')}}</a>
                                <ul class="d-flex flex-wrap justify-content-end">
                                    @auth('web')
                                    <li><a href="javascript:;" onclick="add_to_wishlist({{ $menu_product->id }})"><i class="fal fa-heart"></i></a></li>
                                    @else
                                    <li><a href="javascript:;" onclick="before_auth_wishlist({{ $menu_product->id }})"><i class="fal fa-heart"></i></a></li>
                                    @endauth
                                    <li><a href="{{ route('show-product', $menu_product->slug) }}"><i class="far fa-eye"></i></a></li>
                                </ul>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endforeach
    </div>
</section>
<script>
    const horizontalTabs = document.querySelector('.horizontal-scroll');
    const leftArrow = document.querySelector('.category-arrow.left-arrow');
    const rightArrow = document.querySelector('.category-arrow.right-arrow');

    function checkArrows() {
        if (horizontalTabs.scrollLeft >= 20) {
            leftArrow.style.display = 'flex';
        } else {
            leftArrow.style.display = 'none';
        }
        if (horizontalTabs.scrollLeft <= horizontalTabs.scrollWidth - horizontalTabs.clientWidth - 20) {
            rightArrow.style.display = 'flex';
        } else {
            rightArrow.style.display = 'none';
        }
    }
    
    rightArrow.addEventListener('click', () => {
        horizontalTabs.scrollLeft += 200;
        checkArrows();
    });

    leftArrow.addEventListener('click', () => {
        horizontalTabs.scrollLeft -= 200;
        checkArrows();
    });

    checkArrows();

    horizontalTabs.addEventListener('scroll', () => {
        checkArrows();
    });
</script>
<!--=============================
        MENU ITEM END
    ==============================-->
@endsection