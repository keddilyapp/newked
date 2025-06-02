<?php

namespace Modules\WooCommerce\Http\Controllers;

use App\Helpers\FlashMsg;
use Automattic\WooCommerce\Client;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\WooCommerce\Http\Services\WooCommerceService;

class WooCommerceController extends Controller
{
    public function index()
    {
        $api_products = new WooCommerceService();

            $all_products = $api_products->getProducts();
            if (in_array($all_products, [404,401,0,1]))
            {
                return view('woocommerce::woocommerce.error');
            }

        return view('woocommerce::woocommerce.index', compact('all_products'));
    }

    public function import_all()
    {
        $api_products = new WooCommerceService();
        $all_products = $api_products->getProducts();
        $all_prepared_products = $api_products->prepareProducts($all_products);

        $imported = false;
        foreach ($all_prepared_products as $product)
        {
            $imported = $api_products->filterForStore($product);
        }

        return back()->with(FlashMsg::explain($imported ? 'success' : 'danger', $imported ? __('All Product Imported') : __('Something Went Wrong')));
    }

    public function import_selective(Request $request)
    {
        abort_if($request->method() == 'GET', 403);

        $validated_data = $request->validate([
            'ids' => 'required|array'
        ]);

        $api_products = new WooCommerceService();
        $products = $api_products->getSelectiveProducts($validated_data['ids']);

        $imported = false;
        if (!empty($products))
        {
            $all_prepared_products = $api_products->prepareProducts($products);
            foreach ($all_prepared_products as $product)
            {
                $imported = $api_products->filterForStore($product);
            }
        }

        return response()->json([
            'type' => $imported ? 'success' : 'danger',
            'msg' => $imported ? __('Products Imported') : __('Something Went Wrong')
        ]);
    }

    public function settings()
    {
        return view('woocommerce::woocommerce.settings');
    }

    public function settings_update(Request $request)
    {
        $validated_data = $request->validate([
            'woocommerce_site_url' => 'required',
            'woocommerce_consumer_key' => 'required|starts_with:ck',
            'woocommerce_consumer_secret' => 'required|starts_with:cs'
        ],[
            'woocommerce_consumer_key.starts_with' => __('The consumer key is invalid'),
            'woocommerce_consumer_secret.starts_with' => __('The consumer secret is invalid'),
        ]);

        foreach ($validated_data ?? [] as $index => $value)
        {
            update_static_option($index, $value);
        }

        return back()->with(FlashMsg::settings_update(__('Woocommerce settings updated')));
    }

    public function import_settings()
    {
        return view('woocommerce::woocommerce.import_settings');
    }

    public function import_settings_update(Request $request)
    {
        $validated_data = $request->validate([
            'woocommerce_default_unit' => 'required',
            'woocommerce_default_uom' => 'required|int'
        ]);

        foreach ($validated_data ?? [] as $index => $value)
        {
            update_static_option($index, $value);
        }
        \Cache::forget('woocommerce_client');

        return back()->with(FlashMsg::settings_update(__('Woocommerce import settings updated')));
    }
}
