<?php

namespace Modules\SmsGateway\Http\Controllers\frontend;

use App\Helpers\FlashMsg;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\SmsGateway\Entities\UserOtp;
use Modules\SmsGateway\Http\Traits\OtpGlobalTrait;

class TenantFrontendController
{
    use OtpGlobalTrait;

    public function __construct()
    {
        abort_if(empty(get_static_option('otp_login_status')), 404);
    }

    // OTP Login
    public function showOtpLoginForm()
    {
        if (auth('web')->check()) {
            return redirect()->route('tenant.user.home');
        }

        return view('tenant.frontend.user.login-otp');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|regex:/^[0-9+]+$/|exists:users,mobile',
            'remember' => 'nullable'
        ], ['phone.exists' => __('No record found for this phone number.')]);

        $sentOtp = null;
        try {
            $otp = $this->generateOtp($validated['phone']);
            $sentOtp = $this->sendSms([$validated['phone'], __('Your login OTP: ') . $otp->otp_code, $otp->otp_code], 'otp');
            session()->put('user-otp', $otp);
        } catch (\Exception $exception) {
            if ($exception->getCode() == 20003)
            {
                return back()->with(FlashMsg::explain('danger', __('OTP login in unavailable right now.')));
            }
        }

        return $sentOtp ? to_route('tenant.user.login.otp.verification') : back()->with(FlashMsg::explain('danger', __('OTP send failed')));
    }

    public function showOtpVerificationForm()
    {
        if (auth('web')->check()) {
            return to_route('tenant.user.home');
        }

        $userOtp = session('user-otp');
        return view('tenant.frontend.user.otp-verify', compact('userOtp'));
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|numeric|digits:6',
            'remember' => 'nullable'
        ]);

        $userOtp = UserOtp::where('otp_code', $validated['otp'])->select('user_id', 'expire_date')->first();
        if (empty($userOtp)) {
            return back()->with(FlashMsg::explain('danger', __('The OTP code you have entered is not correct')));
        }

        $user = User::findOrFail($userOtp->user_id);

        if (!now()->isAfter($userOtp->expire_date)) {
            Auth::guard('web')->login($user, array_key_exists('remember', $validated));
            session()->forget('user-otp');

            return to_route('tenant.user.home');
        } else {
            return back()->with(FlashMsg::explain('danger', __('The OTP code is expired. Apply for new OTP code')));
        }
    }

    public function resendOtp()
    {
        $userOtp = session('user-otp');

        if (!empty($userOtp))
        {
            if (now()->isAfter($userOtp->expire_date)) {
                $number = $userOtp->user?->mobile;
                $otp = $this->generateOtp($number);
                $this->sendSms([$number, 'Your login OTP: ' . $otp->otp_code]);

                session()->put('user-otp', $otp);

                return to_route('tenant.user.login.otp.verification');
            }
            else
            {
                return back()->with(FlashMsg::explain('warning', __('You can request a new OTP after the countdown has finished.')));
            }
        }

        return back()->with(FlashMsg::explain('danger', 'Something went wrong.'));
    }
}
