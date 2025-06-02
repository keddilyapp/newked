<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// LANDLORD
Route::middleware(['auth:admin', 'adminglobalVariable', 'set_lang'])
    ->prefix('admin-home/cpanel-automation/')
    ->name('landlord.')
    ->controller(\Modules\CpanelAutomation\Http\Controllers\Admin\LandlordSettingsController::class)
    ->group(function () {
        Route::get('/', 'settings')->name('admin.cpanel.automation.settings');
        Route::post('/', 'update_settings');
        Route::post('/create-test-database', 'test_database_creation')->name('admin.cpanel.automation.database.create.test');
    });
