<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CpanelAutomation\Http\Services\CpanelHelper;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Events\TenantCreated;
use App\Models\StaticOptionCentral;
use Illuminate\Support\Facades\Log;

class CreateDatabaseWithFallback extends CreateDatabase
{
    public function handle(DatabaseManager $databaseManager)
    {
        try {
            // Try the original database creation logic first
            parent::handle($databaseManager);
        } catch (\Exception $e) {
            Log::warning('Default database creation failed: ' . $e->getMessage());

            // Get CPanel automation status
            $cpanelAutomationStatus = StaticOptionCentral::where('option_name', '_cpanel_automation_status')
                ->first()?->option_value;

            if ($cpanelAutomationStatus) {
                try {
                    // Get CPanel configuration from StaticOptionCentral
                    $cpanelHost = StaticOptionCentral::where('option_name', '_cpanel_url')->first()?->option_value;
                    $cpanelUsername = StaticOptionCentral::where('option_name', '_cpanel_username')->first()?->option_value;
                    $cpanelPassword = StaticOptionCentral::where('option_name', '_cpanel_access_token')->first()?->option_value;

                    $cpanel = new CpanelHelper(
                        cpanelUrl: $cpanelHost,
                        cpanelToken: $cpanelPassword,
                        cpanelUser: $cpanelUsername
                    );
                    $dbName = 'db_'.$this->tenant->database;
                    $userName = 'db_user_'.$this->tenant->database;
                    $userPassword = Str::random(16);
                    $cpanel->createDatabase($this->tenant->database);
                    $cpanel->createDatabaseUser($userName, $userPassword);
                    $cpanel->assignUserToDatabase($userName, $dbName);
                    //store database information into the tenants table so that i can work with manual database create
                    DB::table('tenants')->where('id',$this->tenant->id)
                        ->update(['data' => json_encode([
                                "tenancy_db_name" => $dbName,
                                "tenancy_db_username"  => $userName,
                                "tenancy_db_password"  =>$userPassword
                            ])
                    ]);

                    return; // Successfully created database using CPanel
                } catch (\Exception $cpanelError) {
                    Log::error('CPanel database creation also failed: ' . $cpanelError->getMessage());
                    // Both methods failed, throw the CPanel error
                    throw $cpanelError;
                }
            }

            // If CPanel automation is not enabled, throw the original error
            throw $e;
        }
    }
}
