<?php

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Modules\ShippingPlugin\Http\Controllers\ShippingPluginController;
use Modules\ShippingPlugin\Http\Controllers\ShippingPluginFrontendController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'auth:admin',
    'set_lang',
    'tenant_admin_glvar',
    'package_expire',
    'tenantAdminPanelMailVerify',
])->prefix('admin-home/shipping-plugin')->name('tenant.admin.shipping.plugin.')->group(function () {
    Route::get("/", [ShippingPluginController::class, "index"])->name("index");
    Route::post("track", [ShippingPluginController::class, "track"])->name("track");

    Route::get("settings", [ShippingPluginController::class, "settings"])->name("settings");
    Route::post("settings/update", [ShippingPluginController::class, "updateSettings"])->name("settings.update");
    Route::post("configuration/update", [ShippingPluginController::class, "updateConfiguration"])->name("configuration.update");

    Route::get("status", [ShippingPluginController::class, "changeStatus"])->name("status.change");

    Route::get("assets/shipping-gateway/logo/{logo}", function ($logo){
            return response()->file("core/Modules/ShippingPlugin/assets/logo/{$logo}");
    })->name("logo");
});


Route::middleware([
    'web',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'tenant_glvar',
    'set_lang',
    'maintenance_mode',
])->name('tenant.shipping.plugin.')->group(function () {
    Route::post("shipping-plugin/track", [ShippingPluginFrontendController::class, "track"])->name("track");
});
