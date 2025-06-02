<?php

namespace Modules\ShippingPlugin\Http\Services\Gateways;

use Illuminate\Support\Facades\Http;
use Modules\ShippingPlugin\Http\Services\ShippingService;

class DHL
{
    private object $response;
    protected string $apiKey;
    protected string $apiEndpoint;

    public function __construct(protected string $trackingNumber = '000000')
    {
        $this->apiKey = get_static_option('dhl_api_key') ?? '';
        $this->apiTestKey = 'demo-key';
        $this->apiEndpoint = 'https://api-test.dhl.com/track/shipments';

        $this->response = Http::retry(3, 100)->withHeaders([
            'DHL-API-Key' => $this->apiKey
        ]);
    }

    public function track()
    {
        $return_message = [];
        try {
            $data = $this->getDetails();

            $return_message = [
                'status' => true,
                'code' => $data->status(),
                'title' => __('Shipment information found'),
                'gateway' => 'dhl',
                'details' => $data->body()
            ];

        } catch (\Exception $exception) {
            if ($exception->getCode() === 404) {
                $return_message = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'title' => __('Shipment not found'),
                    'gateway' => 'dhl'
                ];
            } else if ($exception->getCode() === 401)
            {
                $return_message = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'title' => __('Unauthorized'),
                    'gateway' => 'dhl'
                ];
            }
        }

        return $return_message;
    }

    private function getDetails()
    {
        return $this->response->get($this->apiEndpoint, [
            'trackingNumber' => $this->trackingNumber
        ]);
    }
}
