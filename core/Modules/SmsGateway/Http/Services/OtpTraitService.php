<?php

namespace Modules\SmsGateway\Http\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\SmsGateway\Http\Traits\OtpGlobalTrait;
use function Clue\StreamFilter\fun;

class OtpTraitService
{
    use OtpGlobalTrait;

    public static function gateways(): array
    {
        return [
            'twilio' => __('Twilio'),
            'msg91' => __('MSG91'),
            'sendra' => __('Sendra'),
        ];
    }

    public function send($data, $type='notify', $sms_type='register', $user='user')
    {
        return $this->sendSms($data, $type, $sms_type, $user);
    }

    public static function getLink(string $gateway_name): ?string
    {
        return match ($gateway_name) {
            'twilio' => 'https://www.twilio.com/',
            'msg91' => 'https://www.msg91.com/',
            'sendra' => 'https://sendra.app/',
            default => null,
        };
    }

    public function getSendraTemplates()
    {
        $config = $this->sendraConfig();
        $landlord_tenant = tenant() ? 'tenant-'.tenant()->id : 'landlord';

        $response = Cache::remember($landlord_tenant, now()->addSeconds(30), function () use ($config) {
            return Http::withQueryParameters([
                'token' => $config['token'],
            ])->get('https://app.sendra.app/api/wpbox/getTemplates');
        });

        if ($response->status() !== 200) {
            Cache::forget($landlord_tenant);
        }

        $templates = array_filter($response->json()['templates'] ?? [], function ($item) {
            return $item['status'] === 'APPROVED';
        });

        return [
            'status' => $response->status(),
            'templates' => $templates ?? []
        ];
    }
}
