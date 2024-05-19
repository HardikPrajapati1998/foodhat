<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class PrinterService
{
    protected $kitchenPrinterIP;
    protected $deskPrinterIP;

    public function __construct()
    {
        $this->kitchenPrinterIP = env('KITCHEN_PRINTER_IP');
        $this->deskPrinterIP = env('DESK_PRINTER_IP');
    }

    public function printToKitchen($data)
    {
        $connector = new NetworkPrintConnector($this->kitchenPrinterIP, 9100);
        $printer = new Printer($connector);

        $printer->text($data);
        $printer->cut();
        $printer->close();
    }

    public function printToDesk($data)
    {
        $connector = new NetworkPrintConnector($this->deskPrinterIP, 9100);
        $printer = new Printer($connector);

        $printer->text($data);
        $printer->cut();
        $printer->close();
    }
}
