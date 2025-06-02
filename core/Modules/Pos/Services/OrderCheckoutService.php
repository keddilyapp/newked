<?php

namespace Modules\Pos\Services;

use App\Http\Services\CheckoutToPaymentService;
use App\Http\Services\ProductCheckoutService;
use App\Models\ProductOrder;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderCheckoutService
{
    private string $totalAmount;
    private string $couponAmount;
    private string $subTotal;
    private string $taxAmount;
    private string $shippingCost;
    private static $self;

    public function __construct()
    {
        $this->couponAmount = 0;
    }

    public static function order($request, $type = 'pos')
    {
        $cartContent = self::cartContent($request, $type);
        $order = self::storeOrder($request,$type);

        $checkout_service = new ProductCheckoutService();
        $user = $checkout_service->getOrCreateUser($validated_data);
        if (empty($user))
        {
            return back()->withErrors(['error' => __('User exist with this username or email')]);
        }
        $order_log_id = $checkout_service->createOrder($validated_data, $user);

        // Checking shipping method is selected
        if(!$order_log_id) {
            return back()->withErrors(['error' => __('Please select a shipping method')]);
        }

        return CheckoutToPaymentService::checkoutToGateway(compact('order_log_id', 'validated_data')); // Sending multiple data compacting together in one array
    }

    private static function cartContent($request = null, $type = null){
        return Cart::instance('default')->content();
    }

    private static function storeOrder($request, $type=null): mixed
    {
        $self = (new self);
        $invoiceNumber = ProductOrder::select('id')->orderBy("id","desc")->first()?->id;

        $userId = $request["selected_customer"] ?? null;
        $user = User::find($userId) ?? "";

        return ProductOrder::create([
            "name" => $user ? $user->name : "No Name",
            "phone" => $user ? $user->mobile : "No Number",
            "coupon" => $request["coupon"] ?? "",
            "coupon_amount" => $self->couponAmount,
            "payment_track" => Str::random(10) . Str::random(10),
            "payment_gateway" => $request['payment_gateway'],
            "transaction_id" => Str::random(10) . Str::random(10),
            "order_status" => 'pending' ,
            "payment_status" => 'pending' ,
            "invoice_number" => $invoiceNumber ? $invoiceNumber + 1 : 111111,
            "user_id" => $userId,
            "type" => $type,
            "note" => $request["note"] ?? null
        ]);
    }
}
