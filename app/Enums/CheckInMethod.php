<?php

namespace App\Enums;

enum CheckInMethod: string
{
    case QrScan = 'qr_scan';
    case BarcodeScanner = 'barcode_scanner';
    case ManualEntry = 'manual_entry';
}
