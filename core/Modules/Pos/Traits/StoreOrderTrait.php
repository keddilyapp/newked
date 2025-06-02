<?php

namespace Modules\Pos\Traits;

use App\Enums\ProductTypeEnum;
use App\Models\OrderProducts;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Support\Str;

trait StoreOrderTrait
{
    /**
     * @param $request
     * @return mixed
     */
    private static function storeOrder($request, $type = null, $cartContent = null): mixed
    {
        $self = (new self);

        $userId = $request["selected_customer"] ?? null;
        $user = User::find($userId) ?? "";

        return ProductOrder::create([
            "name" => $user ? $user->name : "Walk in customer",
            "phone" => $user ? $user->mobile : "No Number",
            "coupon" => $request["coupon"] ?? "",
            "coupon_amount" => $self->couponAmount,
            "order_details" => $cartContent,
            "payment_track" => Str::random(10) . Str::random(10),
            "payment_gateway" => $request['payment_gateway'],
            "transaction_id" => Str::random(10) . Str::random(10),
            "status" => 'pending',
            "payment_status" => 'pending',
            "user_id" => $userId,
            "type" => $type,
            "note" => $request["note"] ?? null
        ]);
    }

    private static function updatePaymentMeta($order_id, $sub_total, $coupon_amount, $shipping_cost, $tax_amount, $total_amount)
    {
        return ProductOrder::find($order_id)->update([
            'total_amount' => $total_amount,
            'payment_meta' => json_encode([
                "subtotal" => $sub_total,
                "coupon_amount" => $coupon_amount,
                "shipping_cost" => $shipping_cost,
                "product_tax" => $tax_amount,
                "total" => $total_amount
            ])
        ]);
    }

    private static function storeOrderProducts($orderItem)
    {
        foreach ($orderItem ?? [] as $item)
        {
            $order = ProductOrder::find($item['order_id'])->first();

            OrderProducts::create([
                'user_id' => $order?->user_id,
                'order_id' => $item['order_id'],
                'product_id' => $item['product_id'],
                'variant_id' => !empty($item['variants_id']) ? $item['variants_id'] : null,
                'quantity' => $item['quantity'] ?? null,
                'price' => $price ?? $item['price'],
                'product_type' => ProductTypeEnum::PHYSICAL,
            ]);
        }
    }
}
