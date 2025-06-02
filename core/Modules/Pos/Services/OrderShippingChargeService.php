<?php

namespace Modules\Pos\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\ShippingModule\Entities\AdminShippingMethod;
use Modules\ShippingModule\Entities\VendorShippingMethod;

class OrderShippingChargeService
{
    public static function getShippingCharge($shippingCost): array
    {
        $adminShippingMethodId = $shippingCost["admin"] ?? 0;
        unset($shippingCost["admin"]);

        return [
            "vendor" => !empty($shippingCost) ? self::vendorShippingCharge($shippingCost) : [],
            "admin" => self::adminShippingCharge($adminShippingMethodId),
        ];
    }

    private static function adminShippingCharge(int $id): ?AdminShippingMethod
    {
        return AdminShippingMethod::where("status_id", 1)
            ->where("id", $id)
            ->first();
    }

    private static function vendorShippingCharge($shippingMethods): Collection|array
    {
        $shippingMethodQuery = VendorShippingMethod::query();

        foreach($shippingMethods as $vendorId => $methodId){
            $shippingMethodQuery->where([
                ["id" ,"=",$methodId] ,
                ["vendor_id" ,"=",$vendorId] ,
                ["status_id" ,"=",1]
            ]);
        }

        return $shippingMethodQuery->get();
    }
}
