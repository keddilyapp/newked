<?php

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Modules\SmsGateway\Http\Controllers\admin\LandlordSettingsController;
use Modules\SmsGateway\Http\Controllers\frontend\LandlordFrontendController;
use Modules\SmsGateway\Http\Controllers\frontend\TenantFrontendController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


// LANDLORD
Route::middleware(['auth:admin', 'adminglobalVariable', 'set_lang'])
    ->prefix('admin-home/sms-gateway/')
    ->name('landlord.')
    ->controller(LandlordSettingsController::class)
    ->group(function () {
        Route::get('/', 'sms_settings')->name('admin.sms.settings');
        Route::post('/', 'update_sms_settings');
        Route::match(['get', 'post'], '/sms-options', 'update_sms_option_settings')->name('admin.sms.options');
        Route::match(['get', 'post'], '/update-status', 'update_status')->name('admin.sms.status');
        Route::get('/login-otp-status', 'login_otp_status')->name('admin.sms.login.otp.status');

        Route::get('/get-sms-templates', 'getTemplates')->name('admin.sms.templates');

        Route::post('/test/otp', 'send_test_sms')->name('admin.sms.test');
    });

// LANDLORD HOME PAGE FRONTEND TENANT OTP LOGIN
Route::middleware(['landlord_glvar', 'maintenance_mode', 'set_lang'])
    ->controller(LandlordFrontendController::class)
    ->name('landlord.')
    ->group(function () {
        Route::get('/login/otp', 'showOtpLoginForm')->name('user.login.otp');
        Route::post('/login/otp', 'sendOtp');
        Route::get('/login/otp/verification', 'showOtpVerificationForm')->name('user.login.otp.verification');
        Route::post('/login/otp/verification', 'verifyOtp');
        Route::get('/login/otp/resend', 'resendOtp')->name('user.login.otp.resend');
    });


// TENANT ADMIN
Route::group(['middleware' => [
    'auth:admin', 'adminglobalVariable', 'set_lang',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
], 'prefix' => 'admin-home/tenant/sms-gateway/', 'as' => 'tenant.'], function () {
    Route::get('/', [LandlordSettingsController::class, 'sms_settings'])->name('admin.sms.settings');
    Route::post('/', [LandlordSettingsController::class, 'update_sms_settings']);
    Route::match(['get', 'post'], '/sms-options', [LandlordSettingsController::class, 'update_sms_option_settings'])->name('admin.sms.options');
    Route::match(['get', 'post'], '/update-status', [LandlordSettingsController::class, 'update_status'])->name('admin.sms.status');
    Route::get('/login-otp-status', [LandlordSettingsController::class, 'login_otp_status'])->name('admin.sms.login.otp.status');
    
    Route::get('/get-sms-templates', [LandlordSettingsController::class, 'getTemplates'])->name('admin.sms.templates');
    Route::post('/test/otp', [LandlordSettingsController::class, 'send_test_sms'])->name('admin.sms.test');
});

// TENANT HOME PAGE FRONTEND USER OTP LOGIN
Route::middleware([
    'web',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'tenant_glvar',
    'maintenance_mode',
    'set_lang'
])->controller(TenantFrontendController::class)
    ->name('tenant.')
    ->group(function () {
        Route::get('/otp/login', 'showOtpLoginForm')->name('user.login.otp');
        Route::post('/otp/login', 'sendOtp');
        Route::get('/otp/login/verification', 'showOtpVerificationForm')->name('user.login.otp.verification');
        Route::post('/otp/login/verification', 'verifyOtp');
        Route::get('/otp/login/resend', 'resendOtp')->name('user.login.otp.resend');
    });
