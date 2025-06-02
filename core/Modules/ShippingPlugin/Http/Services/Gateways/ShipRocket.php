<?php

namespace Modules\ShippingPlugin\Http\Services\Gateways;

use Illuminate\Support\Facades\Http;
use Modules\ShippingPlugin\Http\Services\ShippingService;

class ShipRocket
{
    private string $method = 'GET';
    private string $apiEndpoint = "https://apiv2.shiprocket.in/v1/external";
    private array $header = ["Content-Type" => "application/json"];
    private array|string $body;
    private string $token;
    private object $response;

    public function __construct()
    {
        $email = get_static_option('shiprocket_api_user_email');
        $password = get_static_option('shiprocket_api_user_password');
        $this->token = get_static_option('shiprocket_api_authorization_token') ?? '';
        $this->body = $this->token ? [] : ["email" => $email, "password" => $password];
    }

    public function authorizationResource(): array
    {
        return [
            'end_point' => $this->apiEndpoint."/auth/login",
            'header' => $this->header,
            'body' => $this->body,
            'method' => 'POST'
        ];
    }

    public function getAuthorizationToken($response_body)
    {
        $body = json_decode($response_body);
        return $body->token ?? null;
    }

    private function createOrderResource($body): array
    {
        return [
            'end_point' => $this->apiEndpoint = $this->apiEndpoint."/orders/create/adhoc",
            'header' => $this->header + ["Authorization" => "Bearer {$this->token}"],
            'body' => $this->body = $body,
            'method' => $this->method = 'POST'
        ];
    }

    public function createOrder($body)
    {
        if (!$this->active())
        {
            throw new \Exception('{"message":"Invalid configuration"}', 422);
        }

        $createOrderResource = $this->createOrderResource($body);
        $this->response = Http::retry(3, 100)->withHeaders($createOrderResource['header']);
        return $this->match();
    }

    private function pickupLocationResource(): array
    {
        return [
            'end_point' => $this->apiEndpoint = $this->apiEndpoint."/settings/company/pickup",
            'header' => $this->header + ["Authorization" => "Bearer {$this->token}"],
            'body' => $this->body,
            'method' => $this->method = 'GET'
        ];
    }

    public function getPickupLocations()
    {
        if (!$this->active())
        {
            return [];
        }

        $pickupLocationResource = $this->pickupLocationResource();
        $this->response = Http::retry(3, 100)->withHeaders($pickupLocationResource['header']);
        $response = $this->match();

        $list = [];
        if ($response->ok())
        {
            $response_list = json_decode($response);
            $list = $response_list?->data?->shipping_address;
        }

        return $list;
    }

    private function trackResource($trackingNumber): array
    {
        return [
            'end_point' => $this->apiEndpoint = $this->apiEndpoint."/courier/track?order_id={$trackingNumber}",
            'header' => $this->header + ["Authorization" => "Bearer {$this->token}"],
            'method' => $this->method = 'GET'
        ];
    }

    public function track($trackingNumber)
    {
        $trackResource = $this->trackResource($trackingNumber);
        $this->response = Http::retry(3, 100)->withHeaders($trackResource['header']);

        $return_message = [];
        try {
            $response = $this->match();
            $response_body = $response->body();

            if (!empty(json_decode($response_body)))
            {
                $return_message = [
                    'status' => true,
                    'code' => $response->status(),
                    'title' => __('Shipment information found'),
                    'gateway' => 'shiprocket',
                    'details' => $response->body()
                ];
            } else {
                $return_message = [
                    'status' => false,
                    'code' => $response->status(),
                    'title' => __('Shipment not found'),
                    'gateway' => 'shiprocket',
                    'details' => $response->body()
                ];
            }

        } catch (\Exception $exception) {
            if ($exception->getCode() === 404) {
                $return_message = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'title' => __('Shipment not found'),
                    'gateway' => 'shiprocket'
                ];
            } else if ($exception->getCode() === 401)
            {
                $return_message = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'title' => __('Unauthorized'),
                    'gateway' => 'shiprocket'
                ];
            }
        }

        return $return_message;
    }

    private function match()
    {
        if (in_array(strtolower($this->method), ['get','post']))
        {
            $method = strtolower($this->method);

            if (!empty($this->body))
            {
                return $this->response->$method($this->apiEndpoint, $this->body);
            }

            return $this->response->$method($this->apiEndpoint);
        }

        return response("Invalid http type exception", 400);
    }

    public function active()
    {
        return get_static_option('active_shipping_gateway') === 'shiprocket' && !empty(get_static_option('shiprocket_api_authorization_token'));
    }
}
