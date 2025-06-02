<?php

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Modules\WooCommerce\Http\Controllers\WooCommerceController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Modules\WooCommerce\Http\Middleware\ConfirmCredentialMiddleware;

Route::group(['middleware' => [
    'auth:admin','adminglobalVariable', 'set_lang',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    ConfirmCredentialMiddleware::class
],'prefix' => 'admin-home/tenant/woocommerce', 'as' => 'tenant.admin.'],function () {
    Route::get("manage",[WooCommerceController::class,"index"])->name("woocommerce");
    Route::get("manage/import",[WooCommerceController::class,"import_all"])->name("woocommerce.import.all");
    Route::match(['get','post'],"manage/import/selective",[WooCommerceController::class,"import_selective"])->name("woocommerce.import.selective");

    Route::get("settings",[WooCommerceController::class,"settings"])->name("woocommerce.settings")->withoutMiddleware(ConfirmCredentialMiddleware::class);
    Route::post("settings",[WooCommerceController::class,"settings_update"])->withoutMiddleware(ConfirmCredentialMiddleware::class);

    Route::get("settings/import",[WooCommerceController::class,"import_settings"])->name("woocommerce.settings.import");
    Route::post("settings/import",[WooCommerceController::class,"import_settings_update"]);
});
