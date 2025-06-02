<?php

namespace Database\Seeders;

use App\Models\UpdateInfo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $current_version = get_static_option_central('get_script_version');

        $update_info = [
            [
                'title' => 'New 3 payment gateway added',
                'description' => 'New 3 payment gateway added and they are Kineticpay, AWDPay and SSLCommerz. To use these gateways first update their logo and credentials.',
                'version' => $current_version,
            ],
            [
                'title' => 'Set default logo for all tenants',
                'description' => 'Now landlord can set default logo for all tenants. New tenant/store will be created with the default logo.',
                'url' => route('landlord.admin.price.plan.settings'),
                'version' => $current_version
            ]
        ];

        foreach ($update_info ?? [] as $update_info) {
            UpdateInfo::firstOrCreate($update_info);
        }
    }
}
