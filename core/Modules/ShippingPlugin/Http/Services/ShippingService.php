<?php

namespace Modules\ShippingPlugin\Http\Services;

use Modules\ShippingPlugin\Entities\ShippingApiOrderStatus;
use Modules\ShippingPlugin\Http\Services\Gateways\DHL;
use Modules\ShippingPlugin\Http\Services\Gateways\ShipRocket;
use phpDocumentor\Reflection\Types\This;

class ShippingService
{
    public function __construct(private readonly string $tracking_number = '000'){}

    public static function gateways(): array
    {
        return [
            "dhl" => [
                "name" => __("DHL"),
                "slug" => "dhl",
                "logo" => "dhl.webp",
                "reference" => "https://developer.dhl.com/api-reference/shipment-tracking#get-started-section",
                "info" => false,
                "configuration" => false,
                "authorization" => false,
                "service_class" => DHL::class,
                "api_order_flag" => false // It will enable-disable order list
            ],
            "shiprocket" => [
                "name" => __("ShipRocket"),
                "slug" => "shiprocket",
                "logo" => "shiprocket.webp",
                "reference" => "https://apidocs.shiprocket.in",
                "info" => false,
                "configuration" => true,
                "authorization" => true,
                "service_class" => ShipRocket::class,
                "api_order_flag" => true // It will enable-disable order list
            ]
        ];
    }

    public static function createOrder($body)
    {
        $order_info = [
            'order_id' => $body['order_id'],
            'gateway' => get_static_option('active_shipping_gateway'),
            'service_class' => self::gateways()[get_static_option('active_shipping_gateway')]['service_class'],
            'status' => 'pending',
            'message' => '',
        ];

        try {
            $response = (new $order_info['service_class']())->createOrder($body);

            if ($response->ok()) {
                $order_info['status'] = 'success';
                $order_info['message'] = trim($response->body());
            }
        } catch (\Exception $exception) {
            if ($exception->getCode() === 422) {
                $order_info['status'] = 'failed';
                $order_info['message'] = trim($exception->getMessage());
            }
        }

        if (count($order_info) > 2) {
            ShippingApiOrderStatus::create($order_info);
        }

        return $order_info;
    }

    public function track()
    {
        $active_gateway = get_static_option('active_shipping_gateway') ?? '';
        $method_name = "{$active_gateway}Tracking";

        if (method_exists($this::class, $method_name))
        {
            return ($this->$method_name());
        }

        return [
            'status' => false,
            'code' => 500,
            'title' => __('Plugin configuration incorrect'),
        ];
    }

    public function dhlTracking(): array
    {
        $number = esc_html($this->tracking_number);
        return (new DHL($number))->track();
    }

    public function shiprocketTracking(): array
    {
        $number = esc_html($this->tracking_number);
        return (new ShipRocket())->track($number);
    }
}
