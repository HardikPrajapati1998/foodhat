<?php

namespace App\Http\Controllers\WEB\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderProduct;
use App\Models\OrderAddress;
use App\Models\Reservation;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use Dompdf\Dompdf;
use Dompdf\Options;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        $orders = Order::with('user')->orderBy('id','desc')->get();
        $title = trans('admin_validation.All Orders');
        $setting = Setting::first();
        $orderStatus = -1;

        return view('admin.order', compact('orders','title','orderStatus','setting'));

    }

    public function pendingOrder(){
        $orders = Order::with('user')->orderBy('id','desc')->where('order_status',0)->get();
        $title = trans('admin_validation.Pending Orders');
        $setting = Setting::first();
        $orderStatus = 0;
        return view('admin.order', compact('orders','title','orderStatus','setting'));
    }

    public function pregressOrder(){
        $orders = Order::with('user')->orderBy('id','desc')->where('order_status',1)->get();
        $title = trans('admin_validation.Pregress Orders');
        $setting = Setting::first();
        $orderStatus = 1;

        return view('admin.order', compact('orders','title','orderStatus','setting'));
    }

    public function deliveredOrder(){
        $orders = Order::with('user')->orderBy('id','desc')->where('order_status',2)->get();
        $title = trans('admin_validation.Delivered Orders');
        $setting = Setting::first();
        $orderStatus = 2;

        return view('admin.order', compact('orders','title','orderStatus','setting'));
    }

    public function completedOrder(){
        $orders = Order::with('user')->orderBy('id','desc')->where('order_status',3)->get();
        $title = trans('admin_validation.Completed Orders');
        $setting = Setting::first();
        $orderStatus = 3;
        return view('admin.order', compact('orders','title','orderStatus','setting'));
    }

    public function declinedOrder(){
        $orders = Order::with('user')->orderBy('id','desc')->where('order_status',4)->get();
        $title = trans('admin_validation.Declined Orders');
        $setting = Setting::first();
        $orderStatus = 4;
        return view('admin.order', compact('orders','title','orderStatus','setting'));
    }

    public function cashOnDelivery(){
        $orders = Order::with('user')->orderBy('id','desc')->where('cash_on_delivery',1)->get();
        $title = trans('admin_validation.Cash On Delivery');
        $setting = Setting::first();
        $orderStatus = 5;
        return view('admin.order', compact('orders','title','orderStatus','setting'));
    }

    public function show($id){
        $order = Order::with('user','orderProducts','orderAddress')->find($id);
        $setting = Setting::first();

        return view('admin.show_order',compact('order','setting'));
    }

    public function updateOrderStatus(Request $request , $id){
        $rules = [
            'order_status' => 'required',
            'payment_status' => 'required',
        ];
        $this->validate($request, $rules);

        $order = Order::find($id);
        if($request->order_status == 0){
            $order->order_status = 0;
            $order->save();
        }else if($request->order_status == 1){
            $order->order_status = 1;
            $order->order_approval_date = date('Y-m-d');
            $order->save();
        }else if($request->order_status == 2){
            $order->order_status = 2;
            $order->order_delivered_date = date('Y-m-d');
            $order->save();
        }else if($request->order_status == 3){
            $order->order_status = 3;
            $order->order_completed_date = date('Y-m-d');
            $order->save();
        }else if($request->order_status == 4){
            $order->order_status = 4;
            $order->order_declined_date = date('Y-m-d');
            $order->save();
        }

        if($request->payment_status == 0){
            $order->payment_status = 0;
            $order->save();
        }elseif($request->payment_status == 1){
            $order->payment_status = 1;
            $order->payment_approval_date = date('Y-m-d');
            $order->save();
        }

        $notification = trans('admin_validation.Order Status Updated successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->back()->with($notification);
    }


    public function destroy($id){
        $order = Order::find($id);
        $order->delete();
        $orderProducts = OrderProduct::where('order_id',$id)->get();
        $orderAddress = OrderAddress::where('order_id',$id)->first();
        OrderAddress::where('order_id',$id)->delete();

        $notification = trans('admin_validation.Delete successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->route('admin.all-order')->with($notification);
    }

    public function reservation(){
        $reservations = Reservation::with('user')->orderBy('id','desc')->get();

        return view('admin.reservation', compact('reservations'));
    }

    public function update_reservation_status(Request $request, $id){

        $reservation = Reservation::find($id);
        $reservation->reserve_status = $request->reserve_status;
        $reservation->save();

        $notification = trans('admin_validation.Status updated successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->back()->with($notification);
    }

    public function delete_reservation($id){

        $reservation = Reservation::find($id);
        $reservation->delete();

        $notification = trans('admin_validation.Deleted successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->back()->with($notification);
    }

    public function export(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $orderStatus = $request->input('order_status');
    $setting = Setting::first();
    if($orderStatus == -1){
        $orders = Order::with('user')->whereBetween('created_at', [$startDate, $endDate])->get();
    }elseif($orderStatus >= 0 && $orderStatus <= 4){
        $orders = Order::with('user')->whereBetween('created_at', [$startDate, $endDate])->where('order_status',$orderStatus)->get();
    }else{
        $orders = Order::with('user')->whereBetween('created_at', [$startDate, $endDate])->where('cash_on_delivery',$orderStatus)->get();
    }
    $memberGrid = [];
    foreach ($orders as $key => $member) {
        $memberGrid[$key] = [
            $member->id,
            $member->user ? $member->user->name : 'This user has been deleted',
            $member->grand_total,
            $member->created_at,
        ];
    }
    $headings = ['Order ID', 'Customer Name', 'Total Amount', 'Created At'];

    // Check the export type
    $exportType = $request->input('export_type');

    if ($exportType == 'pdf') {
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);
        $pdf->loadHtml(view('admin.orders_pdf', compact('memberGrid', 'headings','setting'))->render());
        $pdf->render();

        // Return the PDF as a download response
        return response()->streamDownload(function () use ($pdf, $startDate, $endDate) {
            echo $pdf->output();
        }, 'orders-'.$startDate.'_to_'.$endDate.'.pdf');
    } elseif ($exportType == 'xlsx') {
        // Return the XLSX download response
        $fname = 'orders-'.$startDate.'_to_'. $endDate .'.xlsx';
        return Excel::download(new OrdersExport($memberGrid, $headings), $fname);
    } else {
        // Invalid export type
        return redirect()->back()->with('error', 'Invalid export type selected.');
    }

}



}
