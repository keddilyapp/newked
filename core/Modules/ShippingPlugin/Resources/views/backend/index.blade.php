@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Shipping Plugin') }}
@endsection

@section('style')
    <style>
        td *, th, td{
            text-align: left;
        }
        .tableWrap {
            overflow-x: auto;
        }
        .head-table{
            background-color: #fff9ec;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .body-table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        .body-table tr {
            border-spacing: 0 5px;
        }

        th {
            background-color: #f6f6f6;
            padding: 8px;
            color: #444;
        }

        td {
            text-align: left;
            padding: 8px;
            font-weight: 400;
            color: #666;
            min-width: 60px;
        }

        .head-table td {
            padding:  20px;
        }
        .head-table td p {
            font-size: 16px;
            font-weight: 400;
            line-height: 20px;
            color: #666;
        }
        .head-table td p:not(:last-child) {
            margin-bottom: 5px;
        }
        .head-table td p strong {
            color: #444;
        }

        .order-link{
            text-decoration: none;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row">
            <div class="col-md-12">
                <x-flash-msg/>
                <x-error-msg/>

                <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper d-flex justify-content-between">
                        <div class="header-wrap">
                            <h4 class="header-title mb-2">{{__("Order Tracking")}}</h4>
                            <p>{{__('Track your order using the waybill/housebill/tracking number')}}</p>
                        </div>
                    </div>

                    <div class="body-wrap my-4">
                        <form action="{{route('tenant.admin.shipping.plugin.track')}}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="tracking_number">{{__('Waybill or Tracking Number')}}</label>
                                <input type="text" class="form-control" id="tracking_number" name="tracking_number"
                                       placeholder="{{__('eg: 6458412354')}}">
                            </div>

                            <div class="form-group text-end">
                                <button class="btn btn-success" type="submit">{{__('Track')}}
                                    <x-btn.button-loader class="d-none"/>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if(session('tracking_data'))
                    @php
                        $tracking_data = session('tracking_data');
                    @endphp

                    @include("shippingplugin::backend.track-table.{$tracking_data['gateway']}", $tracking_data)
                @endif
            </div>
        </div>
    </div>

    @if($has_orders)
        <div class="dashboard-recent-order mt-5">
            <div class="row">
                <div class="col-md-12">

                    <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                        <div class="wrapper d-flex justify-content-between">
                            <div class="header-wrap">
                                <h4 class="header-title mb-2">{{__("Order Creation Statuses")}}</h4>
                                <p>{{__("All order list created through API from this platform")}}</p>
                            </div>
                        </div>

                        <div class="body-wrap my-4">
                            <table>
                                <thead>
                                    <th>{{__('ID')}}</th>
                                    <th>{{__('Order ID')}}</th>
                                    <th>{{__('Gateway')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Message')}}</th>
                                    <th>{{__('Date Time')}}</th>
                                </thead>
                                <tbody>
                                    @forelse($orders ?? [] as $order)
                                        @php
                                            $info = [];
                                            $message = $order->message;
                                            if ($order->status === 'success')
                                                {
                                                    $message = json_decode($message);
                                                    if ($message):
                                                    $info = [
                                                            'message' => __('Order creation successful'),
                                                            'shipping_order_id' => $message->order_id,
                                                            'channel_order_id' => $message->channel_order_id,
                                                            'shipment_id' => $message->shipment_id,
                                                        ];
                                                    endif;
                                                }
                                            else {
                                                $array_text = str_split($message);

                                                for($i=0; $i<count($array_text); $i++)
                                                    {
                                                        if ($array_text[$i] != '{')
                                                            {
                                                                $array_text[$i] = null;
                                                            }
                                                        else
                                                            {
                                                                break;
                                                            }
                                                    }

                                                $flag = false;
                                                for($i=count($array_text)-1; $i>=0; $i--)
                                                    {
                                                        if ($array_text[$i] != '}')
                                                            {
                                                                $array_text[$i] = null;
                                                            }
                                                        else
                                                            {
                                                                break;
                                                            }
                                                    }

                                                $final_text = implode("", $array_text);
                                                $message = json_decode($final_text);

                                                if ($message):
                                                    $err_msg = [];
                                                    foreach ($message->errors ?? [] as $err)
                                                    {
                                                        $err_msg[] = $err;
                                                    }

                                                    $info = [
                                                        'message' => __('Order creation failed'),
                                                        'info' => __($message->message),
                                                        'errors' => current($err_msg)
                                                    ];
                                                endif;
                                            }
                                        @endphp

                                        <tr>
                                            <td>{{$order->id}}</td>
                                            <td>
                                                <a class="order-link" href="{{route('tenant.admin.product.order.manage.view', $order->order_id)}}" target="_blank">{{$order->order_id}}</a>
                                            </td>
                                            <td class="text-capitalize">{{$order->gateway}}</td>
                                            <td>
                                                <span class="text-capitalize badge {{$order->status == 'success' ? 'bg-success' : 'bg-danger'}}">{{$order->status}}</span>
                                            </td>
                                            <td>
                                                @foreach($info ?? [] as $index => $item)
                                                    @if(is_array($item))
                                                        <div class="d-flex gap-1">
                                                            <p class="text-capitalize text-danger"><strong>{{str_replace('_',' ', $index)}}:</strong></p>
                                                            <ol>
                                                                @foreach($item ?? [] as $value)
                                                                    <li>{{$value}}</li>
                                                                @endforeach
                                                            </ol>
                                                        </div>
                                                    @else
                                                        <p class="text-capitalize"><strong>{{str_replace('_',' ', $index)}}:</strong> {{$item}}</p>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>
                                                <p>{{$order->created_at?->format('d-m-Y H:i A')}}</p>
                                                <p><small>{{$order->created_at?->diffForHumans(parts: 2)}}</small></p>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">{{__('No Data Available')}}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {!! $orders->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (function ($) {
            "use strict";

            $(document).on('click', 'form button[type=submit]', function () {
                $(this).find('span').toggleClass('d-none');
            });
        })(jQuery)
    </script>
@endsection
