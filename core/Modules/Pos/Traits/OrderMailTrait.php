<?php

namespace Modules\Pos\Traits;

use App\Mail\ProductOrderEmail;
use App\Models\User;

trait OrderMailTrait
{
    public static function sendOrderMail($order_process, $request, $type = null): void
    {
        if (!empty($request->selected_customer)) {
            $orderAddress = User::find($request->selected_customer)->toArray();
        }

        // check isUserMailable is true then send mail for ordered user
        if (self::isUserMailable()) {
            if (self::isMailable($type, $request)) {
                \Mail::to($orderAddress["email"])->send(new ProductOrderEmail($orderAddress));
            }
        }
    }

    /**
     * @param mixed $type
     * @param $request
     * @return bool
     */
    private static function isMailable(mixed $type, $request): bool
    {
        return $type == 'pos' && !empty($request->selected_customer) && $request->send_email == 'on';
    }
}
