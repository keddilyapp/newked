<?php

namespace Modules\ShippingPlugin\Observers;

use App\Models\ProductOrder;
use Modules\Product\Entities\ProductInventory;
use Modules\ShippingPlugin\Http\Services\ShippingService;

class OrderCreateObserver
{
    private object $order_details;

    public function created(ProductOrder $order)
    {
        if ($order->checkout_type === 'cod')
        {
            $this->order_details = $order;
            $this->shippingOrderCreate();
        }
    }

    public function updated(ProductOrder $order)
    {
        if ($order->checkout_type !== 'cod' && $order->payment_status === 'success')
        {
            $this->order_details = $order;
            $this->shippingOrderCreate();
        }
    }

    private function shippingOrderCreate()
    {
        if ((moduleExists('ShippingPlugin') && isPluginActive('ShippingPlugin')))
        {
            $active_gateway = get_static_option('active_shipping_gateway');
            if (!empty($active_gateway) && !empty(get_static_option("{$active_gateway}_auto_create_order_option")))
            {
                $order = $this->order_details;
                $items = json_decode($order->order_details);

                $old_sku = '';
                $order_items = [];
                foreach ($items ?? [] as $item)
                {
                    $sku = (ProductInventory::where('product_id', $item->id)->first())->sku ?? '';
                    $variant_color = strtolower($item->options?->color_name ?? null);
                    $variant_size = strtolower($item->options?->size_name ?? null);

                    $order_items[] = [
                        "name" => $variant_color ? $item->name." : $variant_color - $variant_size" : $item->name,
                        "sku" => $old_sku == $sku ? $sku."-$variant_color-$variant_size" : $sku,
                        "units" => $item->qty,
                        "selling_price" => $item->price,
                        "tax" => ($item->price * $item->options->tax_options_sum_rate) / 100,
                        "hsn" => ""
                    ];

                    $old_sku = $sku;
                }

                $payment_type = 'COD';
                if ($order->payment_status == 'success')
                {
                    $payment_type = 'Prepaid';
                }


                ShippingService::createOrder([
                    "order_id" => $order->id,
                    "order_date" => $order->created_at->format('Y-m-d H:i'),
                    "pickup_location" => get_static_option("{$active_gateway}_pickup_location"),
//                    "channel_id" => "",
                    "comment" => $order->message, //"Reseller: M/s Goku",
                    "billing_customer_name" => $order->name, //"Naruto",
                    "billing_last_name" => "",
                    "billing_address" => $order->address, //"House 221B, Leaf Village",
//                    "billing_address_2" => "Near Hokage House",
                    "billing_city" => $order->getCity?->name, //"New Delhi",
                    "billing_pincode" => $order->zipcode,
                    "billing_state" => $order->getState?->name, //"Delhi",
                    "billing_country" => $order->getCountry?->name, //"India",
                    "billing_email" => $order->email, //"naruto@uzumaki.com",
                    "billing_phone" => (int) $order->phone, //"9876543210",
                    "shipping_is_billing" => true,
                    "order_items" => $order_items,
                    "payment_method" => $payment_type,
                    "shipping_charges" => json_decode($order->payment_meta)->shipping_cost ?? 0,
                    "giftwrap_charges" => 0,
                    "transaction_charges" => 0,
                    "total_discount" => $order->coupon_discounted,
                    "sub_total" => json_decode($order->payment_meta)->total,
                    "length" => 10,
                    "breadth" => 10,
                    "height" => 10,
                    "weight" => 10
                ]);
            }
        }
    }
}
