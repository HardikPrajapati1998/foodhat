<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Illuminate\Support\Facades\Log;

class PrinterService
{
    protected $kitchenPrinter;
    protected $deskPrinter;

    public function __construct()
    {
        $this->kitchenPrinter = env('KITCHEN_PRINTER');
        $this->deskPrinter = env('DESK_PRINTER');
    }

    protected function formatOrderDetails($order)
    {
        $output = "";
        $output .= "FoodHat Name\n";
        $output .= "Customer Details: ". $order->customerDetails ."\n";
        $output .= "-------------------------\n";
        $output .= "Order No: " . $order->id . "\n";
        $output .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-------------------------\n";
        foreach ($order->items as $item) {
            $output .= $item->name . " x " . $item->quantity . " = " . $item->price . "\n";
        }
        $output .= "-------------------------\n";
        $output .= "Discount: $" . $order->discount . "\n";
        $output .= "Delivery Charge: $" . $order->delivery . "\n";
        $output .= "Total: $" . $order->total . "\n";
        $output .= "-------------------------\n";
        $output .= "Thank you!\n";

        return $output;
    }

    public function printToKitchen($order)
    {
        if (!$this->kitchenPrinter) {
            return "Warning: Kitchen printer is not configured.";
        }
        try {
            $connector = new WindowsPrintConnector($this->kitchenPrinter);
            $printer = new Printer($connector);

            $printer->text($this->formatOrderDetails($order));
            $printer->cut();
            $printer->close();
            return "Order successfully sent to kitchen printer.";
        } catch (\Exception $e) {
            Log::error('Failed to print to kitchen printer: ' . $e->getMessage());
            return "Warning: Failed to print to kitchen printer.";
        }
    }

    public function printToDesk($order)
    {
        if (!$this->deskPrinter) {
            return "Warning: Desk printer is not configured.";
        }
        try {
            $connector = new WindowsPrintConnector($this->deskPrinter);
            $printer = new Printer($connector);

            $printer->text($this->formatOrderDetails($order));
            $printer->cut();
            $printer->close();
            return "Order successfully sent to desk printer.";
        } catch (\Exception $e) {
            Log::error('Failed to print to desk printer: ' . $e->getMessage());
            return "Warning: Failed to print to desk printer.";
        }
    }
}
