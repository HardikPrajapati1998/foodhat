<?php


namespace Modules\POS\Http\Controllers;

use Illuminate\Support\Facades\Validator;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\DeliveryArea;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderAddress;
use App\Models\Setting;

use Illuminate\Pagination\Paginator;
use Cart;
use Hash;
use Session;
use Mail;

use App\Mail\OrderSuccessfully;
use App\Helpers\MailHelper;
use App\Models\EmailTemplate;
use App\Services\PrinterService;



class POSController extends Controller
{
    protected $printerService;

    public function __construct(PrinterService $printerService)
    {
        $this->printerService = $printerService;
    }

    function countOrdersForUserWithinTimeframe($conn, $userId, $timeframe) {
        $sql = "SELECT COUNT(*) AS order_count FROM Order WHERE user_id = '$userId' AND order_date >= DATE_SUB(NOW(), INTERVAL $timeframe)";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["order_count"];
        } else {
            return 0;
        }
    }


    public function index(Request $request)
    {

        Paginator::useBootstrap();

        $products = Product::where('status', 1)->orderBy('id','desc');

        if($request->category_id) {

            $products = $products->where('category_id', $request->category_id);
        }

        if($request->name) {
            $products = $products->where('name','LIKE','%'.$request->name.'%');
        }

        $products = $products->paginate(18);
        $products = $products->appends($request->all());


        $categories = Category::where(['status' => 1])->get();
        $customers = User::orderBy('id','desc')->where('status',1)->get();

        $delivery_areas = DeliveryArea::where('status', 1)->get();

        $cart_contents = Cart::instance('POSCART')->content();

        $timeframe = '30 DAY';

        foreach($customers as $customer)
        {
            $orderCount = Order::where('user_id', $customer->id)
                        ->where('order_completed_date', '>=', now()->sub($timeframe))
                        ->count();
            $customer->orderCount = $orderCount;
        }

        $orders = Order::with('user')->orderBy('id', 'desc')->where('order_status', 0)->get();
        $pendingOrderCount = $orders->count();
        return view('pos::index')->with([
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers,
            'cart_contents' => $cart_contents,
            'delivery_areas' => $delivery_areas,
            'pendingOrderCount' => $pendingOrderCount
        ]);
    }

    public function load_products(Request $request){
        Paginator::useBootstrap();

        $products = Product::where('status', 1)->orderBy('id','desc');

        if($request->category_id) {

            $products = $products->where('category_id', $request->category_id);
        }

        if($request->name) {
            $products = $products->where('name','LIKE','%'.$request->name.'%');
        }

        $products = $products->paginate(18);
        $products = $products->appends($request->all());

        return view('pos::ajax_products')->with([
            'products' => $products,
        ]);
    }

    public function load_product_modal($product_id){
        $product = Product::with('category')->where(['status' => 1, 'id' => $product_id])->first();
        if(!$product){
            $notification = trans('Something went wrong');
            return response()->json(['message' => $notification],403);
        }

        if($product->size_variant != null){
            $size_variants = json_decode($product->size_variant);
        }else{
            $size_variants = array();
        }

        if($product->optional_item != null){
            $optional_items = json_decode($product->optional_item);
        }else{
            $optional_items = array();
        }

        return view('pos::ajax_product_modal')->with([
            'product' => $product,
            'size_variants' => $size_variants,
            'optional_items' => $optional_items,
        ]);
    }

    public function add_to_cart(Request $request){
        $product = Product::find($request->product_id);

        $optional_items = array();
        $optional_item_price = 0;
        if($request->optional_items){
            foreach($request->optional_items as $index => $optional_item){
                $arr = explode('(::)', $request->optional_items[$index]);
                $single_item = array(
                    'optional_name' => $arr[0],
                    'optional_price' => $arr[1]
                );
                $optional_items[] = $single_item;

                $optional_item_price += $arr[1];
            }
        }

        $variant_array = explode('(::)', $request->size_variant);

        $cart_contents = Cart::instance('POSCART')->content();
        $item_exist = false;

        foreach($cart_contents as $index => $cart_content){
            if($cart_content->id == $request->product_id){
                if($cart_content->options->size == $variant_array[0]){
                    $item_exist = true;
                }
            }
        }

        if($item_exist){
            $notification = trans('admin_validation.Item already added');
            return response()->json(['message' => $notification],403);
        }

        $data=array();
        $data['id'] = $product->id;
        $data['name'] = $product->name;
        $data['qty'] = $request->qty;
        $data['price'] = $request->variant_price;
        $data['weight'] = 1;
        $data['options']['image'] = $product->thumb_image;
        $data['options']['slug'] = $product->slug;
        $data['options']['size'] =  $variant_array[0];
        $data['options']['size_price'] = $variant_array[1];
        $data['options']['optional_items'] = $optional_items;
        $data['options']['optional_item_price'] = $optional_item_price;
        Cart::instance('POSCART')->add($data);

        $cart_contents = Cart::instance('POSCART')->content();

        return view('pos::ajax_cart')->with([
            'cart_contents' => $cart_contents
        ]);
    }

    public function cart_quantity_update(Request $request){
        Cart::instance('POSCART')->update($request->rowid, ['qty' => $request->quantity]);

        $cart_contents = Cart::instance('POSCART')->content();

        return view('pos::ajax_cart')->with([
            'cart_contents' => $cart_contents
        ]);
    }

    public function remove_cart_item($rowId){
        Cart::instance('POSCART')->remove($rowId);

        $cart_contents = Cart::instance('POSCART')->content();

        return view('pos::ajax_cart')->with([
            'cart_contents' => $cart_contents
        ]);
    }

    public function cart_clear(){

        Cart::instance('POSCART')->destroy();

        $notification = trans('admin_validation.Cart clear successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->back()->with($notification);

    }

    public function create_new_customer(Request $request){

        $validatedData = Validator::make($request->all(), [
            'name'=>'required',
            'email'=>'required|unique:users',
            'phone'=>'required',
        ],[
            'name.required' => trans('Name is required'),
            'email.required' => trans('Email is required'),
            'email.unique' => trans('Email already exist'),
            'phone.required' => trans('Phone is required'),
        ])->validate();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make(1234);
        $user->status = 1;
        $user->email_verified = 1;
        $user->save();

        $customers = User::orderBy('id','desc')->where('status',1)->get();

        $customer_html = "<option value=''>".trans('admin.Select Customer')."</option>";
        foreach($customers as $customer){
            $customer_html .= "<option value=".$customer->id.">".$customer->name . "-" .$customer->phone."</option>";
        }

        $notification = trans('admin_validation.Created Successfully');
        return response()->json(['customer_html' => $customer_html, 'message' => $notification]);

    }


    public function create_new_address(Request $request){

        $validatedData = Validator::make($request->all(), [
            'customer_id'=>'required',
            'delivery_area_id'=>'required',
            'first_name'=>'required',
            'last_name'=>'required',
            'address'=>'required',
            'address_type'=>'required',
        ],[
            'customer_id.required' => trans('admin_validation.Customer is required'),
            'delivery_area_id.required' => trans('admin_validation.Delivery area is required'),
            'first_name.required' => trans('admin_validation.First name is required'),
            'last_name.required' => trans('admin_validation.Last name is required'),
            'address.required' => trans('admin_validation.Address is required'),
            'address_type.required' => trans('admin_validation.Address type is required'),
            ])->validate();

        $user = User::find($request->customer_id);
        $is_exist = Address::where(['user_id' => $user->id])->count();
        $address = new Address();
        $address->user_id = $user->id;
        $address->delivery_area_id = $request->delivery_area_id;
        $address->first_name = $request->first_name;
        $address->last_name = $request->last_name;
        $address->email = $request->email;
        $address->phone = $request->phone;
        $address->address = $request->address;
        $address->type = $request->address_type;
        if($is_exist == 0){
            $address->	default_address = 'Yes';
        }
        $address->save();

        $delivery_area = DeliveryArea::find($request->delivery_area_id);

        $notification = trans('admin_validation.Create Successfully');
        return response()->json(['address' => $address,'delivery_fee' => $delivery_area->delivery_fee,'message' => $notification]);

    }

    public function place_order(Request $request){

        if(env('APP_MODE') == 0){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        if(Cart::instance('POSCART')->count() == 0){
            $notification = trans('admin_validation.Your cart is empty!');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $validatedData = Validator::make($request->all(), [
            'customer_id'=>'required',
        ],[
            'customer_id.required' => trans('admin_validation.Customer is required'),
        ])->validate();


        $user = User::find($request->customer_id);
        $cart_contents = Cart::instance('POSCART')->content();

        $calculate_amount = $this->calculate_amount($request->delivery_fee , $request->coupon_price);
        $order_result = $this->orderStore($user, $calculate_amount,  'Cash on Delivery', 'cash_on_delivery', 1, 0, $request->address_id);

        $this->sendOrderSuccessMail($user, $order_result, 'Cash on Delivery', 0);
        $customerDetails = $request->customerDetails ?? "walking";
        $this->printOrder($order_result,$customerDetails);
        $notification = trans('admin_validation.Order created successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->route('admin.pos')->with($notification);

    }

    public function calculate_amount($delivery_charge , $coupon_price){

        $sub_total = 0;
        $coupon = $coupon_price;

        $cart_contents = Cart::instance('POSCART')->content();
        foreach ($cart_contents as $index => $cart_content){
            $item_price = $cart_content->price * $cart_content->qty;
            $item_total = $item_price + $cart_content->options->optional_item_price;
            $sub_total += $item_total;
        }

        $grand_total = ($sub_total - $coupon) + $delivery_charge;

        return array(
            'sub_total' => $sub_total,
            'coupon_price' => $coupon,
            'delivery_charge' => $delivery_charge,
            'grand_total' => $grand_total,
        );
    }

    public function orderStore($user, $calculate_amount, $payment_method, $transaction_id, $payment_status, $cash_on_delivery, $address_id){

        $order = new Order();
        $order->order_id = substr(rand(0,time()),0,10);
        $order->user_id = $user->id;
        $order->grand_total = $calculate_amount['grand_total'];
        $order->delivery_charge = $calculate_amount['delivery_charge'];
        $order->coupon_price = $calculate_amount['coupon_price'];
        $order->sub_total = $calculate_amount['sub_total'];
        $order->product_qty = Cart::instance('POSCART')->count();
        $order->payment_method = $payment_method;
        $order->transection_id = $transaction_id;
        $order->payment_status = $payment_status;
        $order->order_status = 3;
        $order->order_approval_date = date('Y-m-d');
        $order->cash_on_delivery = $cash_on_delivery;
        $order->order_type = 'Dine-in';
        $order->save();

        $cart_contents = Cart::instance('POSCART')->content();
        foreach ($cart_contents as $index => $cart_content){
            $optional_item_arr = array();
            foreach($cart_content->options->optional_items as $index => $optional_item){
                $new_item = array(
                    'item' => $optional_item['optional_name'],
                    'price' => $optional_item['optional_price'],
                );
                $optional_item_arr[] = $new_item;
            }

            $orderProduct = new OrderProduct();
            $orderProduct->order_id = $order->id;
            $orderProduct->product_id = $cart_content->id;
            $orderProduct->product_name = $cart_content->name;
            $orderProduct->unit_price = $cart_content->price;
            $orderProduct->qty = $cart_content->qty;
            $orderProduct->product_size = $cart_content->options->size;
            $orderProduct->optional_price = $cart_content->options->optional_item_price;
            $orderProduct->optional_item = json_encode($optional_item_arr);
            $orderProduct->save();
        }

        // store address

        $find_address = Address::find($address_id);
        if (is_null($find_address)) {
            // If delivery_area_id is null
            $find_delivery_address = "";
        } else {
            // If delivery_area_id is not null
            $find_delivery_address = DeliveryArea::find($find_address->delivery_area_id);
        }
        $orderAddress = new OrderAddress();
        $orderAddress->order_id = $order->id;
        if (empty($find_address->last_name)) {
            $orderAddress->name = $find_address->first_name;
        } else {
            $orderAddress->name = $find_address->first_name.' '.$find_address->last_name;
        }
        $orderAddress->email = $find_address->email;
        $orderAddress->phone = $find_address->phone;
        $orderAddress->address = $find_address->address;
        $orderAddress->longitude = $find_address->longitude;
        $orderAddress->latitude = $find_address->latitude;
        if ($find_delivery_address === "") {
            // If $find_delivery_address is empty
            $orderAddress->delivery_time = 0;
        } else {
            // If $find_delivery_address is not empty
            $orderAddress->delivery_time = $find_delivery_address->min_time.' - '.$find_delivery_address->max_time;
        }

        $orderAddress->save();

        Session::forget('delivery_id');
        Session::forget('delivery_charge');
        Session::forget('coupon_price');
        Session::forget('offer_type');
        Session::forget('coupon_price');
        Session::forget('offer_type');
        Cart::instance('POSCART')->destroy();

        return $order;
    }

    public function sendOrderSuccessMail($user, $order_result, $payment_method, $payment_status){

        $setting = Setting::first();

        MailHelper::setMailConfig();

        $template=EmailTemplate::where('id',6)->first();

        $payment_status = $payment_status == 1 ? 'Success' : 'Pending';
        $subject=$template->subject;
        $message=$template->description;
        $message = str_replace('{{user_name}}',$user->name,$message);
        $message = str_replace('{{total_amount}}',$setting->currency_icon.$order_result->grand_total,$message);
        $message = str_replace('{{payment_method}}',$payment_method,$message);
        $message = str_replace('{{payment_status}}',$payment_status,$message);
        $message = str_replace('{{order_status}}','Processing',$message);
        $message = str_replace('{{order_date}}',$order_result->created_at->format('d F, Y'),$message);
        // Mail::to($user->email)->send(new OrderSuccessfully($message,$subject));
    }

    public function printOrder($order_result,$customerDetails)
    {
        $order = $this->getOrderDetails($order_result,$customerDetails);

        try {
            // Print to kitchen
            $this->printerService->printToKitchen($order);

            // Print to desk
            $this->printerService->printToDesk($order);

            return response()->json(['message' => 'Order printed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Printing failed: ' . $e->getMessage()], 500);
        }
    }

    private function getOrderDetails($order,$customerDetails)
    {
        $orderId = $order['id'];
        $orderProducts = OrderProduct::where('order_id', $orderId)->get();

        // Initialize an empty array to hold the formatted items
        $formattedItems = [];

        // Loop through each order product
        foreach ($orderProducts as $product) {
            // Create a new stdClass object for each item
            $formattedItem =  new \stdClass();
            $formattedItem->name = $product->product_name;
            $formattedItem->quantity = $product->qty;
            $formattedItem->price = $product->unit_price * $product->qty;

            // Add the formatted item to the array
            $formattedItems[] = $formattedItem;
        }

        // Return the result as an object
        return (object) [
            'id' => $orderId,
            'items' => $formattedItems,
            'discount' => $order['coupon_price'],
            'delivery' => $order['delivery_charge'],
            'total' => $order['grand_total'],
            'customerDetails' => $customerDetails
        ];
    }

    public function getPendingOrderCount() {
        $pendingOrderCount = Order::where('order_status', 0)->count();
        return response()->json(['count' => $pendingOrderCount]);
    }

}
