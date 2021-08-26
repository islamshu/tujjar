<html>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>

        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;500;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Tajawal:wght@300;500;700&display=swap');



        *{
            margin:0;
            padding: 0;
            box-sizing: border-box;
        }
        body  {
            min-height: 100vh;
            margin: 50px auto;
            display: flex;
            font-size: 14px;
            text-align: right;
            font-family: 'Tajawal', 'Roboto', sans-serif;
            direction: rtl;
        }

        .wrapperAlert {
            width: 90%;
            margin: auto;
            overflow: hidden;
            border-radius: 12px;
        }

        .topHalf {
            width: 100%;
            color: white;
            overflow: hidden;
            min-height: 400px;
            position: relative;
        }

        .pay-head {
            padding: 20px 0;
            background: -webkit-linear-gradient(45deg, #007c5d, #6cf7c4);
            text-align: center;
        }

        .pay-head h1 {
            margin-top: 10px;
            font-size: 25px;
        }

        .pay-head svg {
            width: 50px;
        }

        .topHalf p {
            margin-bottom: 10px;
        }

        svg {
            fill: white;
        }

        .pay-info {
            background: #eceff4;
            padding: 10px;
        }

        .pay-info table {
            width: 100%;
        }

        .pay-info .logo-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .pay-info .logo img {
            height: 60px;
            display: block;
            margin: auto;
        }

        .pay-info .pay-hint p {
            margin: 10px 0;
        }

        .two-col td:nth-child(1) {
            text-align: right;
        }
        .two-col td:nth-child(2) {
            text-align: left;
        }

        .two-col td {
            padding-bottom: 5px;
        }

        .two-col .label {
            display: inline;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .table-bill-for {
            padding: 10px;
            background: #eceff4;
            border-top: 1px solid #DDD;
        }

        .table-bill-for table {
            width: 100%;
        }

        .table-bill-for .label {
            padding-left: 10px;
            font-weight: bold;
            float: right;
            font-size: 14px;
        }

        .table-bill-for .value {
            font-weight: normal;
            font-size: 14px;
        }

        .bill-main-table {
            margin-top: 20px;
            /*  */
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        .bill-main-table table {
            border-collapse: collapse;
            width: 100%;
        }

        .bill-main-table th {
            background-color: #d9e7ff;
            font-size: 14px;
            padding: 15px 3px;
            vertical-align: middle;
            text-align: center;
        }

        .bill-main-table tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        .bill-main-table tbody td {
            font-size: 14px;
            padding: 10px;
            vertical-align: middle;
            text-align: center;
        }

        .table-total {
            margin-top: 20px;
        }

        .table-total .label {
            padding-left: 20px;
            text-align: right;
            font-size: 14px;
        }

        .bottomHalf {
            margin-top: 20px;
            background-color: #eceff4;
            padding: 20px;
            text-align: center;
        }


    </style>
</head>
<body>
<div class="wrapperAlert">
    <div class="contentAlert">
        <div class="topHalf">
            <div class="pay-head">
                <svg viewBox="0 0 512 512" width="100" title="check-circle">
                    <path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" />
                </svg>
                <h1>تهانينا</h1>
            </div>
            @isset ($order)
            <div>
                <div class="pay-info">
                    <table class="logo-table">
                        <tr class="logo">
                            <td>
                                @if(get_setting('header_logo') != null)
                                    <img loading="lazy"  src="{{ uploaded_asset(get_setting('header_logo')) }}" height="40" style="display:inline-block;">
                                @else
                                    <img loading="lazy"  src="{{ static_asset('assets/img/logo.png') }}" height="40" style="display:inline-block;">
                                @endif
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr class="two-col">
                            <td class="">
                                <span class="label">{{ get_setting('site_name') }}</span>
                            </td>
                        </tr>
                        <tr class="two-col">
                            <td class="">
                                <span class="label">{{ get_setting('contact_address') }}</span>
                            </td>
                        </tr>
                        <tr class="two-col">
                            <td>
                                <span class="label">{{  translate('Email') }}: </span>
                                <span class="value">{{ get_setting('contact_email') }}</span>
                            </td>
                        </tr>
                        <tr class="two-col">
                            <td class="">
                                <span class="label">{{  translate('Order ID') }}:</span>
                                <span class="value">{{ $order->code }}</span>
                            </td>
                        </tr>
                        <tr class="two-col">
                            <td>
                                <span class="label">{{  translate('Phone') }}: </span>
                                <span class="value">{{ get_setting('contact_phone') }}</span>
                            </td>
                        </tr>
                        <tr class="two-col">
                            <td class="">
                                <span class="label">{{  translate('Order Date') }}:</span>
                                <span class="value">{{ date('d-m-Y', $order->date) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="table-bill-for">
                    <table>
                        @php
                            $shipping_address = json_decode($order->shipping_address);
                        @endphp
                        <tr class="two-span">
                            <td class="label">
                                <span class="value">{{ translate('Bill to') }}:</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="two-span">
                                <span class="label">{{ $shipping_address->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="two-span">
                                <span class="label">{{ $shipping_address->address }}, {{ $shipping_address->city }}, {{ $shipping_address->country }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="two-span">
                                <span class="label">{{ translate('Email') }}: {{ $shipping_address->email }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="two-span">
                                <span class="label">{{ translate('Phone') }}: {{ $shipping_address->phone }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="bill-main-table">
                    <table class="">
                        <thead>
                        <tr class="">
                            <th width="15%">{{ translate('Total') }}</th>
                            <th width="10%">{{ translate('Tax') }}</th>
                            <th width="15%">{{ translate('Unit Price') }}</th>
                            <th width="10%">{{ translate('Qty') }}</th>
                            <th width="15%">{{ translate('Delivery Type') }}</th>
                            <th width="35%">{{ translate('Product Name') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($order->orderDetails as $key => $orderDetail)
                            @if ($orderDetail->product != null)
                                <tr class="">
                                    <td width="15%"class="">{{ single_price($orderDetail->price+$orderDetail->tax) }}</td>
                                    <td width="10%" class="">{{ single_price($orderDetail->tax/$orderDetail->quantity) }}</td>
                                    <td  width="15%" class="">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
                                    <td width="10%" class="">{{ $orderDetail->quantity }}</td>
                                    <td width="15%">
                                        @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                            {{ translate('Home Delivery') }}
                                        @elseif ($orderDetail->shipping_type == 'pickup_point')
                                            @if ($orderDetail->pickup_point != null)
                                                {{ $orderDetail->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
                                            @endif
                                        @endif
                                    </td>
                                    <td  width="35%">{{ $orderDetail->product->getTranslation('name') }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-total">
                    <table class="">
                        <tbody>
                        <tr>
                            <th class="label">{{ translate('Sub Total') }}</th>
                            <td class="value">{{ single_price($order->orderDetails->sum('price')) }}</td>
                        </tr>
                        <tr>
                            <th class="label">{{ translate('Shipping Cost') }}</th>
                            <td class="value">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</td>
                        </tr>
                        <tr>
                            <th class="label">{{ translate('Total Tax') }}</th>
                            <td class="value">{{ single_price($order->orderDetails->sum('tax')) }}</td>
                        </tr>
                        <tr>
                            <th class="label">{{ translate('Grand Total') }}</th>
                            <td class="value">{{ single_price($order->grand_total) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset
        </div>
        <div class="bottomHalf">
            <p>تمت عملية الدفع بنجاح</p>
            <p>شكرا لك</p>
        </div>
    </div>
</div>
<script>
    function waitForBridge() {
        if (window.ReactNativeWebView.postMessage.length !== 1)
            setTimeout(waitForBridge, 200);
        else
            window.ReactNativeWebView.postMessage('abc');
    }
    window.onload = waitForBridge;
</script>
</body>
</html>