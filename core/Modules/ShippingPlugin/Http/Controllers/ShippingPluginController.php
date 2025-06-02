<?php

namespace Modules\ShippingPlugin\Http\Controllers;

use App\Helpers\FlashMsg;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ShippingPlugin\Entities\ShippingApiOrderStatus;
use Modules\ShippingPlugin\Http\Services\AuthorizationService;
use Modules\ShippingPlugin\Http\Services\Gateways\DHL;
use Modules\ShippingPlugin\Http\Services\ShippingService;

class ShippingPluginController extends Controller
{
    public function index()
    {
        $active_shipping_gateway = get_static_option('active_shipping_gateway');

        $has_orders = false;
        $orders = [];
        if (!empty($active_shipping_gateway))
        {
            $gateways = ShippingService::gateways();
            $has_orders = $gateways[$active_shipping_gateway]['api_order_flag'];

            $orders = ShippingApiOrderStatus::orderByDesc('id')->paginate(10);
        }

        return view('shippingplugin::backend.index', compact('orders', 'active_shipping_gateway', 'has_orders'));
    }

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
        $track_order = $track_order->track();

        return back()->with(FlashMsg::explain($track_order['status'] ? 'success' : 'danger', $track_order['title']))
            ->with('tracking_data', $track_order);
    }

    public function availability()
    {
        return [
            'status' => empty(get_static_option('active_shipping_gateway')),
            'response' => back()->with(FlashMsg::explain('danger', __('Service not available right now')))
        ];
    }

    public function settings()
    {
        $gateways = ShippingService::gateways();
        return view('shippingplugin::backend.settings', compact('gateways'));
    }

    public function UpdateSettings(Request $request)
    {
        $validated = $request->validate([
            'shipping_gateway_name' => 'required',
            'dhl_api_key' => 'required_if:shipping_gateway_name,dhl',
            'shiprocket_api_user_email' => 'required_if:shipping_gateway_name,shiprocket',
            'shiprocket_api_user_password' => 'required_if:shipping_gateway_name,shiprocket',
            'shiprocket_api_authorization_token' => 'nullable'
        ]);

        $shipping_gateway_name = $request->shipping_gateway_name;
        unset($request->shipping_gateway_name);
        unset($validated['shipping_gateway_name']);

        foreach ($validated ?? [] as $index => $item)
        {
            update_static_option($index, esc_html($item));
        }

        $gateway_name = $shipping_gateway_name.'_api_authorization_token';
        if(empty($request->$gateway_name))
        {
            (new AuthorizationService(esc_html($shipping_gateway_name)))->checkAuthorization()->saveAuthorization();
        }

        return back()->with(FlashMsg::settings_update('Shipping plugin settings updated'));
    }

    public function UpdateConfiguration(Request $request)
    {
        $rules = [
            'shipping_gateway_name' => 'required',
            'shiprocket_auto_create_order_option' => 'nullable',
            'shiprocket_order_tracking_option' => 'nullable',
            'shiprocket_pickup_location' => 'required'
        ];

        $request->validate($rules);
        unset($request->shipping_gateway_name);
        unset($rules['shipping_gateway_name']);

        foreach ($rules ?? [] as $index => $item)
        {
            update_static_option($index, empty(esc_html($request->$index)) ? null : trim(esc_html($request->$index)));
        }

        return back()->with(FlashMsg::settings_update('Shipping plugin settings updated'));
    }

    public function changeStatus()
    {
        $request = \request();
        $this->validation($request);

        if (empty(get_static_option("active_shipping_gateway")))
        {
            update_static_option("active_shipping_gateway" , esc_html($request->option));
        } else {
            if (get_static_option('active_shipping_gateway') == esc_html($request->option))
            {
                delete_static_option("active_shipping_gateway");
            } else {
                delete_static_option("active_shipping_gateway");
                update_static_option("active_shipping_gateway" , esc_html($request->option));
            }
        }

        return response()->json([
            'type' => "success"
        ]);
    }

    private function validation($request)
    {
        abort_if(!$request->has('option'), 404);
        abort_if(empty($request->option), 404);

        $gateway_slugs_array = data_get(ShippingService::gateways(), '*.slug');
        abort_if(!in_array($request->option, $gateway_slugs_array), 404);
    }
}
