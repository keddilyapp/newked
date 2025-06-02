<?php

namespace Modules\SmsGateway\Http\Traits;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\SmsGateway\Entities\SmsGateway;
use Modules\SmsGateway\Entities\UserOtp;
use Twilio\Rest\Client;

trait OtpGlobalTrait
{
    public string $twilio_number;
    public string|array|object $header;
    public string $headerType = 'OTP';
    public string $method = 'GET';
    public string $apiEndpoint = '';
    public string|array $body = [];
    public string $receiverNumber = '';
    public string $templateId = '';
    public string $templateLang = 'en_US';
    public string $providerType = '';
    public string $user;

    public function sendSms($data, $type='notify', $sms_type='register', $user='user')
    {
        [$receiverNumber, $message] = $data;
        $client = $this->config();

        if ($client['gateway'] === 'twilio')
        {
            $client['client']->messages->create($receiverNumber, [
                'from' => $this->twilio_number,
                'body' => $message
            ]);
        }
        elseif ($client['gateway'] === 'msg91')
        {
            $this->user = $user;

            // type : otp or notify
            // sms_type : register or order
            $otp_or_order_id = count($data) === 3 ? $data[count($data)-1] : 000000;
            $client = $this->otherOperator($client['gateway'], $type,'POST')
            ->setBody(str_replace('+','',$receiverNumber), $sms_type, $otp_or_order_id)->match();

            if ($client->ok())
            {
                return true;
            }
            return false;
        }
        else
        {
            $this->user = $user;

            $otp_or_order_id = count($data) === 3 ? $data[count($data)-1] : 000000;
            $client = $this->otherOperator($client['gateway'], $type,'POST')
                ->setBody(str_replace('+','',$receiverNumber), $sms_type, $otp_or_order_id)
                ->match();

            if ($client->ok())
            {
                return true;
            }
            return false;
        }

        return true;
    }

    public function generateOtp($phone_number, $model_name = 'User', $column_name = 'mobile', $relation_name = 'otpInfo', $is_module = false, $module_name = null)
    {
        $user = $this->getModel($model_name, $is_module, $module_name)::select('id')->where($column_name, $phone_number)->first();
        $userOtp = $user->$relation_name;

        if ($user) {
            $gateway = $this->activeSmsGateway();
            $now = now();

            $otp_expire_time = $gateway->otp_expire_time ?? 1;
            $times = $otp_expire_time == 30 ? 'addSeconds' : 'addMinutes';

            if ($userOtp) {
                $userOtp->update([
                    'user_id' => $user->id,
                    'user_type' => $user->getTable(),
                    'otp_code' => rand(111111, 999999),
                    'expire_date' => $now->$times($otp_expire_time)
                ]);
            } else {
                $userOtp = UserOtp::create([
                    'user_id' => $user->id,
                    'user_type' => $user->getTable(),
                    'otp_code' => rand(111111, 999999),
                    'expire_date' => $now->$times($otp_expire_time)
                ]);
            }
        }

        return $userOtp;
    }

    private function getModel($model_name = 'User', $is_module = false, $module_name = null): string
    {
        // Use CamelCase for Model and Module Name

        $model_path = '';
        if ($is_module) {
            $model_path = 'Modules\\' . ucwords($module_name) . '\Entities\\' . ucwords($model_name);
        } else {
            $model_path = '\App\Models\\' . ucwords($model_name);
        }

        return $model_path;
    }

    private function activeSmsGateway()
    {
        return SmsGateway::active()->first();
    }

    private function config()
    {
        $sms_gateway = $this->activeSmsGateway();
        $this->providerType = $sms_gateway->name;
        $client = '';

        try {
            retry(2, function () use ($sms_gateway, &$client) {
                $client = null;

                if ($sms_gateway->name == 'twilio') {
                    $credentials = $this->twilioConfig($sms_gateway);
                    $client = new Client(...$credentials);
                }
            }, 1000);
        } catch (Exception $e) {
            info("error: " . $e->getMessage());
        }

        return [
                'gateway' => $sms_gateway?->name ?? '',
                'client' => $client ?? []
            ];
    }

    private function otherOperator($sms_gateway, $type, $method)
    {
        $this->headerType = $type;
        $headerFunction = $sms_gateway.'Header';
        $header = $this->$headerFunction();
        $this->header = Http::withHeaders($header);
        $this->method = $method;

        return $this;
    }

    private function setBody($receiverNumber, $sms_type, $otp_or_order_id = 000000): static
    {
        $this->setActiveTemplate($sms_type);
        $this->receiverNumber = $receiverNumber;

        match($this->providerType) {
            'msg91' => $this->setMsg91Body($sms_type, $otp_or_order_id),
            'sendra' => $this->setSendraBody($sms_type, $otp_or_order_id)
        };

        return $this;
    }

    private function setMsg91Body($sms_type, $otp_or_order_id)
    {
        if (strtolower($this->headerType) === 'otp')
        {
            $this->body = [
                "template_id" => $this->templateId,
                "recipients" => [
                    [
                        "mobiles" => $this->receiverNumber,
                        "CODE" => $otp_or_order_id,
                        "NAME" => "user",
                        "SITENAME" => get_static_option('site_title')
                    ]
                ]
            ];
        } else {
            if (strtolower($sms_type) === 'register')
            {
                $this->body = [
                    "template_id" => $this->templateId,
                    "recipients" => [
                        [
                            "mobiles" => $this->receiverNumber,
                            "SITENAME" => get_static_option('site_title')
                        ]
                    ]
                ];
            } else {
                $this->body = [
                    "template_id" => $this->templateId,
                    "recipients" => [
                        [
                            "mobiles" => $this->receiverNumber,
                            "ORDER" => $otp_or_order_id,
                            "SITENAME" => get_static_option('site_title')
                        ]
                    ]
                ];
            }
        }
    }

    private function setSendraBody($sms_type, $otp_or_order_id)
    {
        if (strtolower($this->headerType) === 'otp')
        {
            $this->body = [
                'template_name' => $this->templateId,
                'template_language' => $this->templateLang,
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otp_or_order_id
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            if (strtolower($sms_type) === 'register')
            {
                $this->body = [
                    'template_name' => $this->templateId,
                    'template_language' => $this->templateLang,
                    'components' => [
                        [
                            'type' => 'body',
                            "parameters" => [
                                [
                                    'type' => 'text',
                                    'text' => $otp_or_order_id
                                ]
                            ]
                        ]
                    ]
                ];
            } else {
                $this->body = [
                    'template_name' => $this->templateId,
                    'template_language' => $this->templateLang,
                    'components' => [
                        [
                            'type' => 'text',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $otp_or_order_id
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        $config = $this->sendraConfig();
        $this->body['token'] = $config['token'];
        $this->body['phone'] = $this->receiverNumber;
    }

    private function match()
    {
        if (in_array(strtolower($this->method), ['get','post']))
        {
            $method = strtolower($this->method);

            if (!empty($this->body))
            {
                return $this->header->$method($this->apiEndpoint, $this->body);
            }

            return $this->header->$method($this->apiEndpoint);
        }

        return (new Exception("Invalid http type exception", 400));
    }

    private function msg91Header(): array
    {
        $config = $this->msg91Config();
        $this->apiEndpoint = 'https://control.msg91.com/api/v5/flow';

        return [
            'accept' => 'application/json',
            'authkey' => $config['auth_key'],
            'content-type' => 'application/json'
        ];
    }

    private function sendraHeader(): array
    {
        $this->apiEndpoint = 'https://app.sendra.app/api/wpbox/sendtemplatemessage';

        return [
            'accept' => 'application/json',
            'content-type' => 'application/json'
        ];
    }

    private function twilioConfig($sms_gateway): array
    {
        $credentials = json_decode($sms_gateway->credentials);
        $account_sid = $credentials->twilio_sid;
        $auth_token = $credentials->twilio_auth_token;
        $this->twilio_number = $credentials->twilio_number;

        return [$account_sid, $auth_token];
    }

    private function msg91Config(): array
    {
        $sms_gateway = $this->activeSmsGateway();
        $credentials = json_decode($sms_gateway->credentials);
        $auth_key = $credentials->msg91_auth_key;
        $msg91_otp_template_id = $credentials->msg91_otp_template_id;
        $msg91_notify_user_register_template_id = $credentials->msg91_notify_user_register_template_id;
        $msg91_notify_admin_register_template_id = $credentials->msg91_notify_admin_register_template_id;
        $msg91_notify_user_order_template_id = $credentials->msg91_notify_user_order_template_id;
        $msg91_notify_admin_order_template_id = $credentials->msg91_notify_admin_order_template_id;

        return [
            'auth_key' => $auth_key,
            'opt_template_id' => $msg91_otp_template_id,
            'notify_user_register_template_id' => $msg91_notify_user_register_template_id,
            'notify_admin_register_template_id' => $msg91_notify_admin_register_template_id,
            'notify_user_order_template_id' => $msg91_notify_user_order_template_id,
            'notify_admin_order_template_id' => $msg91_notify_admin_order_template_id
        ];
    }

    private function sendraConfig(): array
    {
        $sms_gateway = $this->activeSmsGateway();
        $credentials = json_decode($sms_gateway->credentials ?? '');
        $token = $credentials->sendra_api_token ?? '';
        $sendra_otp_template_id = $credentials->sendra_otp_template_id ?? '';
        $sendra_notify_user_register_template_id = $credentials->sendra_notify_user_register_template_id ?? '';
        $sendra_notify_admin_register_template_id = $credentials->sendra_notify_admin_register_template_id ?? '';
        $sendra_notify_user_order_template_id = $credentials->sendra_notify_user_order_template_id ?? '';
        $sendra_notify_admin_order_template_id = $credentials->sendra_notify_admin_order_template_id ?? '';

        $sendra_otp_template_language = $credentials->sendra_otp_template_language ?? '';
        $sendra_notify_user_register_template_language = $credentials->sendra_notify_user_register_template_language ?? '';
        $sendra_notify_admin_register_template_language = $credentials->sendra_notify_admin_register_template_language ?? '';
        $sendra_notify_user_order_template_language = $credentials->sendra_notify_user_order_template_language ?? '';
        $sendra_notify_admin_order_template_language = $credentials->sendra_notify_admin_order_template_language ?? '';

        return [
            'token' => $token,
            'opt_template_id' => $sendra_otp_template_id,
            'notify_user_register_template_id' => $sendra_notify_user_register_template_id,
            'notify_admin_register_template_id' => $sendra_notify_admin_register_template_id,
            'notify_user_order_template_id' => $sendra_notify_user_order_template_id,
            'notify_admin_order_template_id' => $sendra_notify_admin_order_template_id,

            'opt_template_language' => $sendra_otp_template_language,
            'notify_user_register_template_language' => $sendra_notify_user_register_template_language,
            'notify_admin_register_template_language' => $sendra_notify_admin_register_template_language,
            'notify_user_order_template_language' => $sendra_notify_user_order_template_language,
            'notify_admin_order_template_language' => $sendra_notify_admin_order_template_language,
        ];
    }

    private function setActiveTemplate($sms_type)
    {
        $config = match ($this->providerType) {
            'msg91' => $this->msg91Config(),
            'sendra' => $this->sendraConfig(),
        };

        if (strtolower($this->headerType) === 'otp')
        {
            $template_id = $config['opt_template_id'];
            $template_language = $config['opt_template_language'];
        } else {
            if (strtolower($sms_type) === 'register')
            {
                $template_id = $config["notify_".$this->user."_register_template_id"];
                $template_language = $config["notify_".$this->user."_register_template_language"];
            } else {
                $template_id = $config["notify_".$this->user."_order_template_id"];
                $template_language = $config["notify_".$this->user."_order_template_language"];
            }
        }

        $this->templateId = $template_id;
        $this->templateLang = $template_language;
    }
}
