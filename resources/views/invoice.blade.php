@php

/*
|--------------------------------------------------------------------------
| Helper: Indian Currency Format
|--------------------------------------------------------------------------
*/

if (!function_exists('formatINR')) {
function formatINR($num)
{
$num = round((float) $num, 2);

$negative = $num < 0; $num=abs($num); $parts=explode( '.' , number_format($num, 2, '.' , '' ) ); $intPart=$parts[0]; $decPart=$parts[1]; $lastThree=substr($intPart, -3); $other=substr($intPart, 0, -3); if ($other !=='' ) { $other=preg_replace( '/\B(?=(\d{2})+(?!\d))/' , ',' , $other ); $lastThree=',' . $lastThree; } $formatted=($negative ? '-' : '' ) . $other . $lastThree; if ((float) $decPart> 0) {
    $formatted .= '.' . $decPart;
    }

    return '₹' . $formatted;
    }
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Public Asset Path
    |--------------------------------------------------------------------------
    */

    if (!function_exists('publicAssetPath')) {
    function publicAssetPath($relative)
    {
    if (function_exists('public_path')) {
    return public_path($relative);
    }

    return base_path(
    'public/' . ltrim($relative, '/')
    );
    }
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Image To Data URI
    |--------------------------------------------------------------------------
    */

    if (!function_exists('imageToDataUri')) {
    function imageToDataUri($path)
    {
    if (!is_file($path)) {
    return '';
    }

    $type =
    pathinfo(
    $path,
    PATHINFO_EXTENSION
    ) ?: 'png';

    $data =
    base64_encode(
    file_get_contents($path)
    );

    return "data:image/{$type};base64,{$data}";
    }
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Faint Watermark
    |--------------------------------------------------------------------------
    */

    if (!function_exists('faintPngDataUri')) {
    function faintPngDataUri(
    $path,
    $alphaBoost = 100
    ) {
    if (
    !is_file($path) ||
    !function_exists('imagecreatefrompng')
    ) {
    return imageToDataUri($path);
    }

    $src =
    @imagecreatefrompng($path);

    if (!$src) {
    return imageToDataUri($path);
    }

    imagealphablending(
    $src,
    false
    );

    imagesavealpha(
    $src,
    true
    );

    imagefilter(
    $src,
    IMG_FILTER_COLORIZE,
    0,
    0,
    0,
    $alphaBoost
    );

    ob_start();

    imagepng($src);

    $data =
    ob_get_clean();

    imagedestroy($src);

    return
    'data:image/png;base64,' .
    base64_encode($data);
    }
    }


    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    $logoSrc =
    imageToDataUri(
    publicAssetPath(
    'images/logo.png'
    )
    );

    $watermarkSrc =
    faintPngDataUri(
    publicAssetPath(
    'images/water_mark.png'
    ),
    118
    );


    /*
    |--------------------------------------------------------------------------
    | Main Data
    |--------------------------------------------------------------------------
    */

    $user =
    $data['user'] ?? [];

    $invoice =
    $data['invoice'] ?? [];

    $invoiceDetails =
    $data['invoice_details'] ?? [];

    $subscription =
    $invoiceDetails['subscription'] ?? [];


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    $userId =
    $user['id'] ?? '';

    $custName =
    $user['name'] ?? '';

    $custMobile =
    $user['mobile'] ?? '';

    $custEmail =
    $user['email'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Invoice
    |--------------------------------------------------------------------------
    */

    $invoiceId =
    $invoice['id'] ?? '';

    $invoiceNo =
    $invoice['invoice_no'] ?? '';

    $orderId =
    $invoice['order_id'] ?? '';

    $invoiceUserId =
    $invoice['user_id'] ?? '';

    $invoiceDate =
    $invoice['created_at'] ?? '';

    $discount =
    (float) (
    $invoice['discount'] ?? 0
    );

    $discountType =
    $invoice['discount_type'] ?? '';

    $subTotal =
    (float) (
    $invoice['sub_total'] ?? 0
    );

    $tax =
    (float) (
    $invoice['tax'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | Subscription
    |--------------------------------------------------------------------------
    */

    $subscriptionId =
    $subscription['id'] ?? '';

    $subscriptionPlanId =
    $subscription['subscription_id'] ?? '';

    $planName =
    $subscription['plan_name'] ?? '';

    $duration =
    $subscription['duration'] ?? '';

    $validity =
    $subscription['validity'] ?? '';

    $subscriptionAmount =
    (float) (
    $subscription['amount'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | Discount Calculation
    |--------------------------------------------------------------------------
    */

    $discountAmount = 0;

    if ($discountType === 'Percentage') {

    $discountAmount =
    ($subTotal * $discount) / 100;

    } elseif ($discountType === 'Flat') {

    $discountAmount =
    $discount;
    }


    /*
    |--------------------------------------------------------------------------
    | Taxable Amount
    |--------------------------------------------------------------------------
    */

    $taxableAmount =
    $subTotal -
    $discountAmount;


    /*
    |--------------------------------------------------------------------------
    | GST
    |--------------------------------------------------------------------------
    */

    $cgstRate = 9;

    $sgstRate = 9;

    $cgst =
    ($taxableAmount * $cgstRate) / 100;

    $sgst =
    ($taxableAmount * $sgstRate) / 100;


    /*
    |--------------------------------------------------------------------------
    | Grand Total
    |--------------------------------------------------------------------------
    */

    $grandTotal =
    (float) (
    $invoice['grand_total'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | Invoice Items
    |--------------------------------------------------------------------------
    */

    $descriptionParts = array_filter([
    $planName,
    $duration,
    $validity
    ]);

    $description =
    implode(
    ' - ',
    $descriptionParts
    );

    $items = [
    [
    'description' => $description,
    'qty' => 1,
    'amount' => $subscriptionAmount
    ]
    ];

    @endphp


    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <title>
            Smart Trade — Invoice
        </title>

        <style>
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

                font-family:
                    "DejaVu Sans",
                    sans-serif;

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

                padding:
                    26px 32px 30px 32px;

                background-color:
                    #ffffff;

                border:
                    1px solid #e2e6ee;
            }


            /* =========================================================
           HEADER
        ========================================================= */

            .header-table {

                width: 100%;

                border-collapse:
                    collapse;

                margin-bottom:
                    22px;
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

                background:
                    #2f4f8f;

                color:
                    #ffffff;

                padding:
                    18px 42px;

                font-size:
                    22px;

                letter-spacing:
                    4px;

                font-weight:
                    bold;
            }


            /* =========================================================
           BILL TO
        ========================================================= */

            .meta-table {

                width: 100%;

                border-collapse:
                    collapse;

                margin-bottom:
                    20px;
            }

            .meta-table td {

                vertical-align:
                    top;

                padding:
                    0;
            }

            .bill-to h2 {

                margin:
                    0 0 10px 0;

                font-size:
                    15px;

                color:
                    #1a1a1a;
            }

            .bill-to .field {

                font-size:
                    11px;

                margin:
                    6px 0;

                line-height:
                    1.4;
            }

            .bill-to .field .label {

                font-weight:
                    bold;

                color:
                    #1a1a1a;
            }

            .bill-to .field .value {

                color:
                    #2f4f8f;

                font-weight:
                    600;
            }


            /* =========================================================
           INVOICE INFORMATION
        ========================================================= */

            .invoice-info {

                text-align:
                    right;

                font-size:
                    11px;
            }

            .invoice-info .row {

                margin:
                    6px 0;

                line-height:
                    1.4;
            }

            .invoice-info .row .label {

                font-weight:
                    bold;

                color:
                    #1a1a1a;
            }

            .invoice-info .row .value {

                font-weight:
                    bold;

                color:
                    #2f4f8f;

                margin-left:
                    8px;
            }


            /* =========================================================
           TABLE
        ========================================================= */

            .table-frame {

                position:
                    relative;
            }

            .invoice-table {

                width:
                    100%;

                border-collapse:
                    collapse;

                border:
                    1.5px solid #2f4f8f;

                table-layout:
                    fixed;

                position:
                    relative;

                z-index:
                    1;
            }

            .invoice-table th {

                background:
                    #2f4f8f;

                color:
                    #ffffff;

                font-size:
                    11.5px;

                letter-spacing:
                    0.5px;

                padding:
                    11px 8px;

                text-align:
                    center;

                border-left:
                    1px solid #2f4f8f;

                border-right:
                    1px solid #2f4f8f;

                border-top:
                    none;

                border-bottom:
                    none;
            }

            .invoice-table td {

                border-left:
                    1px solid #2f4f8f;

                border-right:
                    1px solid #2f4f8f;

                border-top:
                    none;

                border-bottom:
                    none;

                padding:
                    6px;

                font-size:
                    11px;

                vertical-align:
                    top;
            }


            /* COLUMN WIDTHS */

            .invoice-table th:nth-child(1),
            .invoice-table td:nth-child(1) {

                width:
                    7%;

                text-align:
                    center;
            }

            .invoice-table th:nth-child(2),
            .invoice-table td:nth-child(2) {

                width:
                    56%;
            }

            .invoice-table th:nth-child(3),
            .invoice-table td:nth-child(3) {

                width:
                    17%;

                text-align:
                    center;
            }

            .invoice-table th:nth-child(4),
            .invoice-table td:nth-child(4) {

                width:
                    20%;

                text-align:
                    right;
            }


            /* =========================================================
           ITEM ROW
        ========================================================= */

            .item-row td {

                height:
                    45px;

                vertical-align:
                    top;
            }

            .sl-cell {

                text-align:
                    center;

                color:
                    #000000;

                font-weight:
                    bold;

                padding-left:
                    8px;

                padding-right:
                    8px;
            }

            .desc-cell {

                text-align:
                    left;

                line-height:
                    1.5;

                word-wrap:
                    break-word;

                overflow-wrap:
                    break-word;
            }

            .top-value {

                text-align:
                    center;

                padding-top:
                    12px;
            }

            .top-value.qty-accent {

                color:
                    #000000;

                font-weight:
                    bold;

                padding-left:
                    8px;

                padding-right:
                    8px;
            }


            /* =========================================================
           FIXED EMPTY SPACE
        ========================================================= */

            .space-row td {

                height:
                    250px;
            }


            /* =========================================================
           TAX
        ========================================================= */

            .tax-line {

                text-align:
                    right;

                font-weight:
                    bold;

                padding-top:
                    7px;

                padding-bottom:
                    7px;

                color:
                    #33415c;
            }


            /* =========================================================
           GRAND TOTAL
        ========================================================= */

            .grand-line {

                background:
                    #2f4f8f;

                color:
                    #ffffff;

                font-weight:
                    bold;

                font-size:
                    12.5px;

                text-align:
                    right;

                padding-top:
                    16px !important;

                padding-bottom:
                    16px !important;

                border-color:
                    #2f4f8f;
            }


            /* =========================================================
           WATERMARK
        ========================================================= */

            .watermark {

                position:
                    absolute;

                top:
                    80px;

                left:
                    130px;

                width:
                    240px;

                z-index:
                    0;
            }

        </style>

    </head>


    <body>

        <div class="container">

            <div class="invoice-wrap">


                <!-- =========================================================
     HEADER
========================================================= -->

                <table class="header-table">

                    <tr>

                        <td class="logo-cell">

                            @if($logoSrc)

                            <img src="{{ $logoSrc }}" alt="Smart Trade">

                            @else

                            <strong style="
        font-size:20px;
        color:#2f4f8f;
    ">
                                SMART TRADE
                            </strong>

                            @endif

                        </td>


                        <td class="tag-cell">

                            <span class="invoice-tag">

                                INVOICE

                            </span>

                        </td>

                    </tr>

                </table>


                <!-- =========================================================
     CUSTOMER
========================================================= -->

                <table class="meta-table">

                    <tr>

                        <td style="width:55%;">

                            <div class="bill-to">

                                <h2>
                                    Bill To
                                </h2>


                                <div class="field">

                                    <span class="label">
                                        Name:
                                    </span>

                                    <span class="value">
                                        {{ $custName }}
                                    </span>

                                </div>


                                <div class="field">

                                    <span class="label">
                                        Mobile No:
                                    </span>

                                    <span class="value">
                                        {{ $custMobile }}
                                    </span>

                                </div>


                                <div class="field">

                                    <span class="label">
                                        Email:
                                    </span>

                                    <span class="value">
                                        {{ $custEmail }}
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td style="width:45%;">

                            <div class="invoice-info">

                                <div class="row">

                                    <span class="label">
                                        Invoice No:
                                    </span>

                                    <span class="value">
                                        {{ $invoiceNo }}
                                    </span>

                                </div>


                                <div class="row">

                                    <span class="label">
                                        Invoice Date:
                                    </span>

                                    <span class="value">
                                        {{ $invoiceDate }}
                                    </span>

                                </div>

                            </div>

                        </td>

                    </tr>

                </table>


                <!-- =========================================================
     INVOICE TABLE
========================================================= -->

                <div class="table-frame">

                    @if($watermarkSrc)

                    <img class="watermark" src="{{ $watermarkSrc }}" alt="">

                    @endif


                    <table class="invoice-table">


                        <colgroup>

                            <col style="width:7%;">

                            <col style="width:56%;">

                            <col style="width:17%;">

                            <col style="width:20%;">

                        </colgroup>


                        <thead>

                            <tr>

                                <th>
                                    Sl.No
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Qty
                                </th>

                                <th>
                                    Amount (₹)
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <!-- =====================================================
     ITEM
====================================================== -->

                            @foreach($items as $index => $item)

                            <tr class="item-row">

                                <td class="sl-cell">

                                    {{ $index + 1 }}.

                                </td>


                                <td class="desc-cell">

                                    {{ $item['description'] ?? '' }}

                                </td>


                                <td class="top-value qty-accent">

                                    {{ $item['qty'] ?? 1 }}

                                </td>


                                <td class="top-value">

                                    {{ formatINR($item['amount'] ?? 0) }}

                                </td>

                            </tr>

                            @endforeach


                            <!-- =====================================================
     EMPTY SPACE
====================================================== -->

                            <tr class="space-row">

                                <td></td>

                                <td></td>

                                <td></td>

                                <td></td>

                            </tr>


                            <!-- =====================================================
     SUBTOTAL
====================================================== -->

                            <tr>

                                <td></td>

                                <td></td>

                                <td class="tax-line">

                                    Subtotal

                                </td>

                                <td class="tax-line">

                                    {{ formatINR($subTotal) }}

                                </td>

                            </tr>


                            <!-- =====================================================
     DISCOUNT
====================================================== -->

                            <tr>

                                <td></td>

                                <td></td>

                                <td class="tax-line">

                                    Discount

                                    @if($discountType === 'Percentage')

                                    ({{ $discount }}%)

                                    @else

                                    (Flat)

                                    @endif

                                </td>

                                <td class="tax-line">

                                    - {{ formatINR($discountAmount) }}

                                </td>

                            </tr>


                            <!-- =====================================================
     CGST
====================================================== -->

                            <tr>

                                <td></td>

                                <td></td>

                                <td class="tax-line">

                                    CGST
                                    ({{ $cgstRate }}%)

                                </td>

                                <td class="tax-line">

                                    {{ formatINR($cgst) }}

                                </td>

                            </tr>


                            <!-- =====================================================
     SGST
====================================================== -->

                            <tr>

                                <td></td>

                                <td></td>

                                <td class="tax-line">

                                    SGST
                                    ({{ $sgstRate }}%)

                                </td>

                                <td class="tax-line">

                                    {{ formatINR($sgst) }}

                                </td>

                            </tr>


                            <!-- =====================================================
     GRAND TOTAL
====================================================== -->

                            <tr>

                                <td></td>

                                <td></td>

                                <td class="grand-line">

                                    Grand Total (₹)

                                </td>

                                <td class="grand-line">

                                    {{ formatINR($grandTotal) }}

                                </td>

                            </tr>


                        </tbody>

                    </table>

                </div>


            </div>

        </div>

    </body>

    </html>
