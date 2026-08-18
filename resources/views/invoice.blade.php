@php
if (!function_exists('formatINR')) {
function formatINR($num)
{
$num = round((float) $num, 2);
$negative = $num < 0; $num=abs($num); $parts=explode('.', number_format($num, 2, '.' , '' )); $intPart=$parts[0]; $decPart=$parts[1]; $lastThree=substr($intPart, -3); $other=substr($intPart, 0, -3); if ($other !=='' ) { $other=preg_replace('/\B(?=(\d{2})+(?!\d))/', ',' , $other); $lastThree=',' . $lastThree; } $formatted=($negative ? '-' : '' ) . $other . $lastThree; if ((float) $decPart> 0) {
    $formatted .= '.' . $decPart;
    }

    return '₹' . $formatted;
    }
    }

    if (!function_exists('publicAssetPath')) {
    function publicAssetPath($relative)
    {
    if (function_exists('public_path')) {
    return public_path($relative);
    }
    return base_path('public/' . ltrim($relative, '/'));
    }
    }

    if (!function_exists('imageToDataUri')) {
    function imageToDataUri($path)
    {
    if (!is_file($path)) {
    return '';
    }
    $type = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
    $data = base64_encode(file_get_contents($path));
    return "data:image/{$type};base64,{$data}";
    }
    }

    // Bake faintness into the PNG's alpha channel via GD rather than relying
    // on CSS opacity, which dompdf does not composite reliably.
    if (!function_exists('faintPngDataUri')) {
    function faintPngDataUri($path, $alphaBoost = 100)
    {
    if (!is_file($path) || !function_exists('imagecreatefrompng')) {
    return imageToDataUri($path);
    }

    $src = @imagecreatefrompng($path);
    if (!$src) {
    return imageToDataUri($path);
    }

    imagealphablending($src, false);
    imagesavealpha($src, true);
    imagefilter($src, IMG_FILTER_COLORIZE, 0, 0, 0, $alphaBoost);

    ob_start();
    imagepng($src);
    $data = ob_get_clean();
    imagedestroy($src);

    return 'data:image/png;base64,' . base64_encode($data);
    }
    }

    $logoSrc = imageToDataUri(publicAssetPath('images/logo.png'));
    $watermarkSrc = faintPngDataUri(publicAssetPath('images/water_mark.png'), 118);

    // ---------- Invoice data ----------
    // Pass one or more line items via `items` (array of
    // ['description' => ..., 'qty' => ..., 'amount' => ...]).
    // The old single `item` key still works for backward compatibility.
    //
    // view('invoice', ['invoice' => [
    // 'invoiceNo' => '...', 'invoiceDate' => '...', 'invoiceTerms' => '...',
    // 'customer' => ['name' => '...', 'mobile' => '...', 'email' => '...'],
    // 'items' => [
    // ['description' => '...', 'qty' => 1, 'amount' => 10000],
    // ['description' => '...', 'qty' => 1, 'amount' => 5000],
    // ['description' => '...', 'qty' => 1, 'amount' => 2500],
    // ],
    // 'cgstRate' => 9, 'sgstRate' => 9,
    // ]]);
    $invoiceNo = $data['invoice']['invoice_no'];
    $invoiceDate =$data['invoice']['created_at'];
    // $invoiceNo = $invoice['invoiceNo'] ?? 'SMTA-IN-001';
    // $invoiceDate = $invoice['invoiceDate'] ?? '';
    // $invoiceTerms = $invoice['invoiceTerms'] ?? '';
    $custName = $data['user']['name'];
    $custMobile = $data['user']['mobile'];
    $custEmail = $data['user']['email'];
    // $custName = $invoice['customer']['name'] ?? '';
    // $custMobile = $invoice['customer']['mobile'] ?? '';
    // $custEmail = $invoice['customer']['email'] ?? '';
    $items = [['description'=>$data['invoice_details']['subscription']['plan_name'],
    'qty'=>1,'amount'=>$data['invoice_details']['subscription']['amount']]];

    // Accept the new `items` array, or fall back to the old single `item`.
    // $items = $invoice['items'] ?? (isset($invoice['item']) ? [$invoice['item']] : []);
    // if (empty($items)) {
    // $items = [['description' => '', 'qty' => 1, 'amount' => 0]];
    // }

    // $cgstRate = $invoice['cgstRate'] ?? 9;
    // $sgstRate = $invoice['sgstRate'] ?? 9;
    $cgstRate = 9;
    $sgstRate = 9;

    // $itemsTotal = 0;
    // foreach ($items as $it) {
    // $itemsTotal += (float) ($it['amount'] ?? 0);
    // }
    $itemsTotal = $data['invoice']['grand_total'];

    // $cgst = $itemsTotal * $cgstRate / 100;
    // $sgst = $itemsTotal * $sgstRate / 100;
    // $grandTotal = $itemsTotal + $cgst + $sgst;

    // Keep the card's total height roughly the same as the original
    // single-item design (which used a 250px filler) by shrinking the
    // filler space as more item rows are added above it.
    $fillerHeight = max(250 - ((count($items) - 1) * 34), 20);
    @endphp
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Smart Trade — Invoice</title>
        <style>
            /* dompdf-safe: table layout only, no grid/flex, DejaVu Sans font, base64 images.

     @page margin is what fixes true centering: without it, dompdf's default
     page margin plus a wide wrapper left ~0px of spare room, so
     `margin:0 auto` had nothing to distribute and the card sat flush
     against the page edge instead of centered. */
            @page {
                margin: 35px 26px;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0 auto !important;
                padding: 0;
                font-family: "DejaVu Sans", sans-serif;
                color: #1a1a1a;
                font-size: 12px;
                background-color: #eef1f6;
            }

            .container {
                margin-top: 2%;
            }

            .invoice-wrap {
                width: 640px;
                margin: 0 auto;
                padding: 26px 32px 30px 32px;
                background-color: #ffffff;
                border: 1px solid #e2e6ee;
            }

            /* ---------- Header ---------- */
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 22px;
            }

            .header-table td {
                vertical-align: middle;
            }

            .header-table .logo-cell img {
                width: 210px;
            }

            .header-table .tag-cell {
                text-align: right;
            }

            .invoice-tag {
                display: inline-block;
                background: #2f4f8f;
                color: #ffffff;
                padding: 18px 42px;
                font-size: 22px;
                letter-spacing: 4px;
                font-weight: bold;
            }

            /* ---------- Bill To / meta ---------- */
            .meta-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .meta-table td {
                vertical-align: top;
                padding: 0;
            }

            .bill-to h2 {
                margin: 0 0 10px 0;
                font-size: 15px;
                color: #1a1a1a;
            }

            .bill-to .field {
                font-size: 11px;
                margin: 6px 0;
                line-height: 1.4;
            }

            .bill-to .field .label {
                font-weight: bold;
                color: #1a1a1a;
            }

            .bill-to .field .value {
                color: #2f4f8f;
                font-weight: 600;
            }

            .invoice-info {
                text-align: right;
                font-size: 11px;
            }

            .invoice-info .row {
                margin: 6px 0;
                line-height: 1.4;
            }

            .invoice-info .row .label {
                font-weight: bold;
                color: #1a1a1a;
            }

            .invoice-info .row .value {
                font-weight: bold;
                color: #2f4f8f;
                margin-left: 8px;
            }

            /* ---------- Line item table ---------- */
            /* Full border on every th/td (not just border-left) so the grid actually
     closes on all four edges once border-collapse merges shared lines. */
            .table-frame {
                position: relative;
            }

            .invoice-table {
                width: 100%;
                border-collapse: collapse;
                border: 1.5px solid #2f4f8f;
                table-layout: fixed;
                position: relative;
                z-index: 1;
            }

            .invoice-table th {
                background: #2f4f8f;
                color: #fff;
                font-size: 11.5px;
                letter-spacing: 0.5px;
                padding: 11px 8px;
                text-align: center;
                border-left: 1px solid #2f4f8f;
                border-right: 1px solid #2f4f8f;
                border-top: none;
                border-bottom: none;
            }

            .invoice-table td {
                border-left: 1px solid #2f4f8f;
                border-right: 1px solid #2f4f8f;
                border-top: none;
                border-bottom: none;
                padding: 10px 8px;
                font-size: 11px;
                vertical-align: top;
            }

            .invoice-table {
                width: 100%;
                table-layout: fixed;
                border-collapse: collapse;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 6px;
                border-left: 1px solid #2f4f8f;
                border-right: 1px solid #2f4f8f;
                border-top: none;
                border-bottom: none;
            }

            .invoice-table th:nth-child(1),
            .invoice-table td:nth-child(1) {
                width: 7%;
                text-align: center;
            }

            .invoice-table th:nth-child(2),
            .invoice-table td:nth-child(2) {
                width: 56%;
            }

            .invoice-table th:nth-child(3),
            .invoice-table td:nth-child(3) {
                width: 17%;
                text-align: center;
            }

            .invoice-table th:nth-child(4),
            .invoice-table td:nth-child(4) {
                width: 20%;
                text-align: right;
            }

            .sl-cell {
                text-align: center;
                color: #000000;
                font-weight: bold;
                padding-left: 8px;
                padding-right: 8px;
            }

            .desc-cell {
                text-align: left;
                line-height: 1.5;
            }

            .top-value {
                text-align: center;
                padding-top: 12px;
            }

            .top-value.qty-accent {
                color: #000000;
                font-weight: bold;
                padding-left: 8px;
                padding-right: 8px;
            }

            .filler-row td {
                height: 250px;
            }

            .tax-line {
                text-align: right;
                font-weight: bold;
                padding-top: 7px;
                padding-bottom: 7px;
                color: #33415c;
            }

            .grand-line {
                background: #2f4f8f;
                color: #fff;
                font-weight: bold;
                font-size: 12.5px;
                text-align: right;
                padding-top: 16px !important;
                padding-bottom: 16px !important;
                border-color: #2f4f8f;
            }

            /* Watermark is positioned against .table-frame (a plain relative div
     wrapping just the table) rather than the whole page — this keeps it
     confined to the table area, matching the reference image, and it sits
     behind the table (z-index 0) so it only shows through cells that have
     no background color, i.e. the description/filler area. */
            .watermark {
                position: absolute;
                top: 80px;
                left: 130px;
                width: 240px;
                z-index: 0;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <div class="invoice-wrap">

                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            @if($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Smart Trade">
                            @else
                            <strong style="font-size:20px;color:#2f4f8f;">SMART TRADE</strong>
                            @endif
                        </td>
                        <td class="tag-cell">
                            <span class="invoice-tag">INVOICE</span>
                        </td>
                    </tr>
                </table>

                <table class="meta-table">
                    <tr>
                        <td style="width: 55%;">
                            <div class="bill-to">
                                <h2>Bill To</h2>
                                <div class="field"><span class="label">Name:</span> <span class="value">{{ $custName }}</span></div>
                                <div class="field"><span class="label">Mobile No:</span> <span class="value">{{ $custMobile }}</span></div>
                                <div class="field"><span class="label">Email:</span> <span class="value">{{ $custEmail }}</span></div>
                            </div>
                        </td>
                        <td style="width: 45%;">
                            <div class="invoice-info">
                                <div class="row"><span class="label">Invoice No:</span><span class="value">{{ $invoiceNo }}</span></div>
                                <div class="row"><span class="label">Invoice Date:</span><span class="value">{{ $invoiceDate }}</span></div>
                                {{-- <div class="row"><span class="label">Invoice Terms:</span><span class="value">{{ $invoiceTerms }}</span>
                            </div> --}}
            </div>
            </td>
            </tr>
            </table>

            <div class="table-frame">

                @if($watermarkSrc)
                <img class="watermark" src="{{ $watermarkSrc }}" alt="">
                @endif

                <table class="invoice-table">
                    <colgroup>
                        <col style="width: 7%;">
                        <col style="width: 56%;">
                        <col style="width: 17%;">
                        <col style="width: 20%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Sl.No</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- One plain row per JSON item — Sl.No / Description / Qty / Amount --}}
                        @foreach($items as $index => $it)
                        <tr>
                            <td class="sl-cell">{{ $index + 1 }}.</td>
                            <td class="desc-cell">{{ $it['description'] ?? '' }}</td>
                            <td class="top-value qty-accent">{{ $it['qty'] ?? 1 }}</td>
                            <td class="top-value">{{ formatINR($it['amount'] ?? 0) }}</td>
                        </tr>
                        @endforeach
                        <tr class="filler-row">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        {{-- From here down, columns 1-2 are merged (rowspan) across the
             filler + tax rows, exactly like the original single-item
             layout, so the watermark area and tax block look unchanged. --}}
                        <tr>
                            <td rowspan="4"></td>
                            <td rowspan="4"></td>
                            <td class="tax-line">CGST({{ $cgstRate }}%)</td>
                            {{-- <td class="tax-line">{{ formatINR($cgst) }}</td> --}}
                        </tr>

                        <tr>
                            <td class="tax-line">SGST({{ $sgstRate }}%)</td>
                            {{-- <td class="tax-line">{{ formatINR($sgst) }}</td> --}}
                        </tr>
                        <tr>
                            <td class="grand-line">Grand Total (₹)</td>
                            <td class="grand-line">{{ formatINR($grandTotal) }}</td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div>
        </div>

    </body>
    </html>
