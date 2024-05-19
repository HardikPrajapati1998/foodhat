<form id="modal_add_to_cart_form"  method="POST">
    @csrf

<input type="hidden" name="product_id" value="{{ $product->id }}">
<input type="hidden" name="price" value="0" id="modal_price">
<input type="hidden" name="variant_price" value="0" id="modal_variant_price">


<div class="wsus__cart_popup_text">
    <h3 style="color:black;">{{ $product->name }}</h3>
    <p class="rating">
        @php
            if ($product->total_review > 0) {
                $average = $product->average_rating;

                $int_average = intval($average);

                $next_value = $int_average + 1;
                $review_point = $int_average;
                $half_review=false;
                if($int_average < $average && $average < $next_value){
                    $review_point= $int_average + 0.5;
                    $half_review=true;
                }
            }
        @endphp

        @if ($product->total_review > 0)
            @for ($i = 1; $i <=5; $i++)
                @if ($i <= $review_point)
                    <i class="fas fa-star"></i>
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

        <span>({{ $product->total_review }})</span>
    </p>

    @if ($product->is_offer)
        <h3 class="price">{{ $currency_icon }}{{ $product->offer_price }} <del>{{ $currency_icon }}{{ $product->price }}</del></h3>
    @else
        <h3 class="price">{{ $currency_icon }}{{ $product->price }} </h3>
    @endif

    <div class="details_size">
        <h5>{{__('admin.Select Size')}}</h5>
        @foreach ($size_variants as $index => $size_variant)
            <div class="form-check">
                <input name="size_variant" class="form-check-input" type="radio" name="flexRadioDefault" id="large-{{ $index }}" value="{{ $size_variant->size }}(::){{ $size_variant->price }}" data-variant-price="{{ $size_variant->price }}" data-variant-size="{{ $size_variant->size }}">
                <label class="form-check-label" for="large-{{ $index }}">
                    {{ $size_variant->size }} <span>- {{ $currency_icon }}{{ $size_variant->price }}</span>
                </label>
            </div>
        @endforeach
    </div>

    @if (count($optional_items) > 0)
    <div class="details_extra_item">
        <h5>{{__('admin.Select Addon')}} <span>({{__('admin.optional')}})</span></h5>
        @foreach ($optional_items as $index => $optional_item)
            <div class="form-check">
                <input data-optional-item="{{ $optional_item->price }}" name="optional_items[]" class="form-check-input check_optional_item" type="checkbox" value="{{ $optional_item->item }}(::){{ $optional_item->price }}" id="optional-item-{{ $index }}">
                <label class="form-check-label" for="optional-item-{{ $index }}">
                    {{ $optional_item->item }} <span>+ {{ $currency_icon }}{{ $optional_item->price }}</span>
                </label>
            </div>
        @endforeach
    </div>
    @endif

    <div class="details_quentity">
        <h5>{{__('admin.Select Quantity')}}</h5>
        <div class="quentity_btn_area d-flex flex-wrapa align-items-center">
            <div class="quentity_btn">
                <button type="button" class="btn btn-danger modal_decrement_qty_detail_page"><i class="fa fa-minus"></i></button>
                <input type="text" value="1" name="qty" class="modal_product_qty" readonly>
                <button  type="button" class="btn btn-success modal_increment_qty_detail_page"><i class="fa fa-plus"></i></button>
            </div>
            <h3 >{{ $currency_icon }} <span class="modal_grand_total">0.00</span></h3>
        </div>
    </div>
    <ul class="details_button_area d-flex flex-wrap">
        <li><a id="modal_add_to_cart" class="btn btn-primary"  href="javascript:;">{{__('admin.Add now')}}</a></li>
    </ul>
</div>

</form>

<script>
    (function($) {
        "use strict";
        $(document).ready(function () {
            $("#modal_add_to_cart").on("click", function(e){
                e.preventDefault();
                if ($("input[name='size_variant']").is(":checked")) {

                    $.ajax({
                        type: 'get',
                        data: $('#modal_add_to_cart_form').serialize(),
                        url: "{{ url('/admin/pos/add-to-cart') }}",
                        success: function (response) {
                            $(".shopping-card-body").html(response)
                            toastr.success("{{__('admin.Item added successfully')}}")
                            calculateTotalFee();

                            $("#cartModal").modal('hide');
                        },
                        error: function(response) {
                            if(response.status == 500){
                                toastr.error("{{__('admin.Server error occured')}}")
                            }

                            if(response.status == 403){
                                toastr.error(response.responseJSON.message)
                            }
                        }
                    });

                } else {
                    toastr.error("{{__('admin.Please select a size')}}")
                }
            });

            $("input[name='size_variant']").on("change", function(){
                $("#modal_variant_price").val($(this).data('variant-price'))
                calculateModalPrice()
            })

            $("input[name='optional_items[]']").change(function() {
                calculateModalPrice()
            });

            $(".modal_increment_qty_detail_page").on("click", function(){
                let product_qty = $(".modal_product_qty").val();
                let new_qty = parseInt(product_qty) + parseInt(1);
                $(".modal_product_qty").val(new_qty);
                calculateModalPrice();
            })

            $(".modal_decrement_qty_detail_page").on("click", function(){
                let product_qty = $(".modal_product_qty").val();
                if(product_qty == 1) return;
                let new_qty = parseInt(product_qty) - parseInt(1);
                $(".modal_product_qty").val(new_qty);
                calculateModalPrice();
            })

        });
    })(jQuery);

    function calculateModalPrice(){
        let optional_price = 0;
        let product_qty = $(".modal_product_qty").val();
        $("input[name='optional_items[]']:checked").each(function() {
            let checked_value = $(this).data('optional-item');
            optional_price = parseInt(optional_price) + parseInt(checked_value);
        });

        let variant_price = $("#modal_variant_price").val();
        let main_price = parseInt(variant_price) * parseInt(product_qty);

        let total = parseInt(main_price) + parseInt(optional_price);

        $(".modal_grand_total").html(total)
        $("#modal_price").val(total);
    }
</script>
