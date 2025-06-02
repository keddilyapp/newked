<?php

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Modules\Pos\Http\Controllers\PosController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    'web',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'auth:admin',
    'tenant_admin_glvar',
    'package_expire',
    'tenantAdminPanelMailVerify',
    'set_lang'
])->prefix('admin-home/pos')->name('tenant.admin.pos.')->group(function () {
    Route::get("view", [PosController::class, "index"])->name("view");
    Route::get("search-customer", [PosController::class, "search"]);
    Route::get("customer/{id?}", [PosController::class, "find"]);
    Route::post("customer/add", [PosController::class, "addNewCustomer"]);
    Route::post("order/submit", [PosController::class, "checkout"])->name("checkout");
    Route::get("payment-gateway/settings", [PosController::class, "paymentGatewaySettings"])->name("payment-gateway-settings");
    Route::post("payment-gateway/settings", [PosController::class, "updatePaymentGatewaySettings"]);
    Route::get("gateway-settings", [PosController::class, "getPaymentGatewaySettings"]);
    Route::post("default-tax/settings", [PosController::class, "taxSettings"])->name("tax-settings");

    Route::get("pwa/settings", [PosController::class, "pwaSettings"])->name("pwa-settings");
    Route::post("pwa/settings", [PosController::class, "updatePwaSettings"]);

    Route::post("tax-classes", [PosController::class, "taxClasses"])->name("tax-classes");
    Route::get("store-location", [PosController::class, "storeLocation"]);
    Route::get("location-settings", [PosController::class, "locationSettings"]);
    Route::post("hold-order", [PosController::class, "holdOrder"]);
    Route::get("get-hold-order", [PosController::class, "getHoldOrder"]);
    Route::get("delete-hold-order", [PosController::class, "deleteHoldOrder"]);
    Route::get("restore-hold-order", [PosController::class, "restoreHoldOrder"]);
});
