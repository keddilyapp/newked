<?php

namespace Database\Seeders\Tenant;

use App\Models\PaymentGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddNewTenantPaymentGateway extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addNewGateway();
    }

    private function addNewGateway()
    {
        $newPaymentGateway = PaymentGateway::where('name' ,'iyzipay')->first();
        if (empty($newPaymentGateway))
        {
            PaymentGateway::create([
                'name' => 'iyzipay',
                'image' => 0,
                'description' => '',
                'status' => 0,
                'test_mode' => 1,
                'credentials' => json_encode([
                    'secret_key' => '',
                    'api_key' => ''
                ])
            ]);
        }
    }
}
