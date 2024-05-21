<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

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
        $output .= "-------------------------\n";
        $output .= "Order No: " . $order->id . "\n";
        $output .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-------------------------\n";
        foreach ($order->items as $item) {
            $output .= $item->name . " x " . $item->quantity . " = " . $item->price . "\n";
        }
        $output .= "-------------------------\n";
        $output .= "Total: $" . $order->total . "\n";
        $output .= "-------------------------\n";
        $output .= "Thank you!\n";

        return $output;
    }

    public function printToKitchen($order)
    {
        try {
            $connector = new WindowsPrintConnector($this->kitchenPrinter);
            $printer = new Printer($connector);

            $printer->text($this->formatOrderDetails($order));
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            throw new \Exception('Failed to print to kitchen printer: ' . $e->getMessage());
        }
    }

    public function printToDesk($order)
    {
        try {
            $connector = new WindowsPrintConnector($this->deskPrinter);
            $printer = new Printer($connector);

            $printer->text($this->formatOrderDetails($order));
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            throw new \Exception('Failed to print to desk printer: ' . $e->getMessage());
        }
    }
}
