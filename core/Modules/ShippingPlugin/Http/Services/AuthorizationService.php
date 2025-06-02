<?php

namespace Modules\ShippingPlugin\Http\Services;

use Illuminate\Support\Facades\Http;

class AuthorizationService
{
    private object $response;
    private bool $authorization_status;
    private string $authorization_token;
    protected string $apiEndpoint;
    protected string $method = 'GET';
    protected array $header = [];
    protected array|string $body = [];

    public function __construct(protected string $gateway_name){}

    public function checkAuthorization()
    {
        $gateway = $this->selectedGateway();
        $this->authorization_status = $gateway['authorization'];
        return $this;
    }

    private function getAuthorization()
    {
        if ($this->authorization_status)
        {
            try {
                $gateway = $this->selectedGateway();
                $class = $gateway['service_class'];
                $authorization_resource = (new $class)->authorizationResource();

                $this->apiEndpoint = $authorization_resource['end_point'];
                $this->header = $authorization_resource['header'];
                $this->body = $authorization_resource['body'];
                $this->method = $authorization_resource['method'];

                $this->response = Http::retry(3, 100)->withHeaders($this->header);
                return $this->match();

            } catch (\Exception $exception)
            {
                return $exception;
            }
        }

        return false;
    }

    public function saveAuthorization()
    {
        $client_response = $this->getAuthorization();

        if ($client_response && $client_response->getStatusCode() === 200)
        {
            try {
                $gateway = $this->selectedGateway();
                $class = $gateway['service_class'];
                $authorization_token = (new $class)->getAuthorizationToken($client_response->body());

                if (!empty($authorization_token))
                {
                    $gateway_name = strtolower(trim($this->gateway_name));
                    update_static_option("{$gateway_name}_api_authorization_token", $authorization_token);

                    return true;
                }
            } catch (\Exception $exception)
            {
                return null;
            }
        }

        return false;
    }

    private function match()
    {
        if (strtolower($this->method) === 'get')
        {
            return $this->response->get($this->apiEndpoint, $this->body);
        }
        elseif (strtolower($this->method) === 'post')
        {
            return $this->response->post($this->apiEndpoint, $this->body);
        }

        return response("Invalid http type exception", 400);
    }

    private function selectedGateway(): array
    {
        $gateways = ShippingService::gateways();
        return $gateways[$this->gateway_name];
    }
}
