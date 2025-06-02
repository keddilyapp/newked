<?php

namespace Modules\ShippingPlugin\Http\Controllers;

use App\Helpers\FlashMsg;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ShippingPlugin\Http\Services\Gateways\DHL;
use Modules\ShippingPlugin\Http\Services\ShippingService;

class ShippingPluginFrontendController extends Controller
{
    public function track(Request $request)
    {
        if ($this->availability()['status'])
        {
            return $this->availability()['response'];
        }

        $validated = $request->validate([
            'tracking_number' => 'required|alpha_num'
        ]);

        $track_order = new ShippingService($validated['tracking_number']);
        $tracking_data = $track_order->track();

        $markup = view("shippingplugin::addon-view.markup.{$tracking_data['gateway']}-track-result", compact('tracking_data'))->render();

        return response()->json([
            'status' => $tracking_data['status'],
            'msg' => $tracking_data['title'],
            'type' => $tracking_data['status'] == 200 ? 'success' : 'danger',
            'markup' => $markup
        ]);
    }

    public function availability()
    {
        return [
            'status' => empty(get_static_option('active_shipping_gateway')),
            'response' => response()->json([
                'status' => false,
                'msg' => __('Service not available right now'),
                'type' => 'danger',
                'markup' => []
            ])
        ];
    }
}
