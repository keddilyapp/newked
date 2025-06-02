<?php

namespace Modules\CpanelAutomation\Http\Controllers\Admin;

use App\Models\StaticOption;
use App\Models\StaticOptionCentral;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\CpanelAutomation\Http\Services\CpanelHelper;

class LandlordSettingsController extends Controller
{
    public function settings(Request $request)
    {
        $settings = StaticOptionCentral::whereIn('option_name', ['_cpanel_username','_cpanel_automation_status','_cpanel_access_token','_cpanel_url'])
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->option_name => $item->option_value];
        });
        return view('cpanelautomation::landlord.admin.settings', compact('settings'));
    }

    public function update_settings(Request $request)
    {
        $request->validate([
            '_cpanel_username' => 'required|string|max:191',
            '_cpanel_automation_status' => 'nullable|string|max:191',
            '_cpanel_access_token' => 'required|string|max:191',
            '_cpanel_url' => 'required|string|max:191',
        ]);
        $settings = [
            '_cpanel_username' => $request->_cpanel_username,
            '_cpanel_automation_status' => $request->_cpanel_automation_status ?? 'off',
            '_cpanel_access_token' => $request->_cpanel_access_token,
            '_cpanel_url' => $request->_cpanel_url,
        ];

        foreach ($settings as $key => $value) {
            StaticOptionCentral::updateOrCreate(
                ['option_name' => $key],
                ['option_value' => $value]
            );
        }

        return back()->with(['type' => 'success', 'msg' => __('cPanel settings updated successfully')]);
    }

    public function test_database_creation(Request $request)
    {


        try {
        // Get cPanel settings
        $settings = StaticOptionCentral::whereIn('option_name', [
            '_cpanel_username',
            '_cpanel_access_token',
            '_cpanel_url'
        ])->get()->mapWithKeys(function ($item) {
            return [$item->option_name => $item->option_value];
        });

        // Generate random names for testing
        $testPrefix = 'test_' . Str::random(8);
        $dbName = $testPrefix . '_db';
        $userName = $testPrefix . '_user';
        $userPassword = Str::random(16);

        // Initialize cPanel helper
        $cpanel_helper = new CpanelHelper(
            cpanelUrl: $settings['_cpanel_url'],
            cpanelToken: $settings['_cpanel_access_token'] ?? '',
            cpanelUser: $settings['_cpanel_username']
        );

        // Step 1: Create database
        $createDb = $cpanel_helper->createDatabase($dbName);
        if (!$createDb['status']) {
            throw new \Exception('Failed to create database: ' . ($createDb['message'] ?? 'Unknown error'));
        }

        // Step 2: Create database user
        $createUser = $cpanel_helper->createDatabaseUser($userName, $userPassword);
        if (!$createUser['status']) {
            // Cleanup: Delete the database if user creation fails
            $cpanel_helper->deleteDatabase($dbName);
            throw new \Exception('Failed to create database user: ' . ($createUser['message'] ?? 'Unknown error'));
        }

        // Step 3: Assign user to database
        $assignUser = $cpanel_helper->assignUserToDatabase($userName, $dbName);
        if (!$assignUser['status']) {
            // Cleanup: Delete both database and user if assignment fails
            $cpanel_helper->deleteDatabase($dbName);
            $cpanel_helper->deleteDatabaseUser($userName);
            throw new \Exception('Failed to assign user to database: ' . ($assignUser['message'] ?? 'Unknown error'));
        }

        // Clean up test resources
        $cpanel_helper->deleteDatabase($dbName);
        $cpanel_helper->deleteDatabaseUser($userName);

        return redirect()->back()->with([
            'type' => 'success',
            'msg' => 'cPanel configuration test completed successfully! All operations are working as expected.'
        ]);

    } catch (\Exception $e) {
        return redirect()->back()->with([
            'type' => 'danger',
            'msg' => 'cPanel configuration test failed: ' . $e->getMessage()
        ]);
    }

 }

}
