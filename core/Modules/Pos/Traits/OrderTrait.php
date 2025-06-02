<?php

namespace Modules\Pos\Traits;

use App\Http\Services\CheckoutCouponService;
use App\Models\ProductOrder;
use Crypt;
use Exception;
use Gloudemans\Shoppingcart\Facades\Cart;
use Modules\Pos\Services\FrontendInventoryService;
use Modules\Pos\Services\OrderTaxService;
use Modules\TaxModule\Entities\TaxClassOption;
use Modules\TaxModule\Services\CalculateTaxBasedOnCustomerAddress;
use Modules\Wallet\Http\Services\WalletService;
use stdClass;
use Str;
use Throwable;

trait OrderTrait
{
    use StoreOrderTrait, OrderMailTrait;

    protected static function cartInstanceName(): string
    {
        return "default";
    }

    public static function groupByColumn(): string
    {
        return "options.vendor_id";
    }

    /**
     * @return bool
     */
    protected static function isVendorMailable(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    protected static function isAdminMailable(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    protected static function isUserMailable(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    protected static function isMailSendWithQueue(): bool
    {
        return true;
    }

    protected static function groupByCartContent($cartContent)
    {
        return $cartContent->groupBy(self::groupByColumn());
    }

    private static function cartContent($request = null, $type = null)
    {
        return Cart::instance(self::cartInstanceName())->content();
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    protected static function orderProcess($request, $type = null): array
    {
        // get all cart items and make group by with vendor_id
        $cartContent = self::cartContent($request, $type);
        $groupedData = self::groupByCartContent($cartContent);

        // Initialize OrderShippingChargeService for
        // declare a temporary variable for storing totalAmount
        $total_amount = 0;

        $order = self::storeOrder($request, $type, $cartContent);

        $tax = CalculateTaxBasedOnCustomerAddress::init();
        $uniqueProductIds = $cartContent->pluck("id")->unique()->toArray();

        $price_type = "";
        $taxProducts = collect([]);
        if (CalculateTaxBasedOnCustomerAddress::is_eligible()) {
            $taxProducts = $tax
                ->productIds($uniqueProductIds)
                ->customerAddress(
                    get_static_option('pos_tax_country'),
                    get_static_option('pos_tax_state') ?? null,
                    get_static_option('pos_tax_city') ?? null,
                )
                ->generate();

            $price_type = "billing_address";
        } elseif (CalculateTaxBasedOnCustomerAddress::is_eligible_inclusive()) {
            $price_type = "inclusive_price";
        }

        $totalTaxAmount = 0;

        foreach ($groupedData as $key => $data) {
            $vendor_id = $key;
            if ($key == "") {
                $vendor_id = null;
            }

            $orderItem = [];

            foreach ($data as $cart) {
                $total_amount += $cart->price * $cart->qty;

                if ($price_type == "billing_address") {
                    $taxAmount = !$taxProducts->isEmpty() ? $taxProducts->find($cart->id) : (object)[];
                    $taxAmount = calculateOrderedPrice($cart->price, $taxAmount->tax_options_sum_rate ?? 0, $price_type);
                } elseif ($price_type == "inclusive_price") {
                    $taxAmount = calculateOrderedPrice($cart->price, $cart->options['tax_options_sum_rate'] ?? 0, $price_type);
                } else {
                    $taxAmount = 0;
                }

                $orderItem[] = [
                    "order_id" => $order->id,
                    "product_id" => (int)$cart->id,
                    "variant_id" => (($cart->options["variant_id"] ?? null) != null) ?
                        $cart->options["variant_id"] : null,
                    "quantity" => (int)$cart->qty,
                    "price" => $cart->price,
                    "sale_price" => $cart->options['regular_price'] ?? 0,
                    "tax_amount" => $taxAmount,
                    "tax_type" => $price_type,
                ];

                $totalTaxAmount += $taxAmount * (int)$cart->qty;
            }

            self::storeOrderProducts($orderItem);
        }

        $coupon_amount = CheckoutCouponService::calculateCoupon((object)$request, $total_amount, $cartContent, 'DISCOUNT');
        $orderSubTotal = ($total_amount - $coupon_amount);

        $finalAmount = $orderSubTotal + $totalTaxAmount;
        // now store OrderPaymentMeta
        $orderPaymentMeta = self::updatePaymentMeta($order->id, $total_amount, $coupon_amount, 0, $totalTaxAmount, $finalAmount);

        // update product inventory
        FrontendInventoryService::updateInventory($order->id);

        return $orderPaymentMeta ? [
            "success" => true,
            "order_id" => $order->id,
            "total_amount" => $finalAmount,
            "tested" => encrypt($order->payment_status),
            "secrete_key" => Crypt::encryptString($order->transaction_id)
        ] : [
            "success" => false,
            "order_id" => null
        ];
    }

    /**
     * @param $request
     * @param null $type
     * @return mixed
     * @throws Throwable
     */
    public static function sendOrder($request, $type = null)
    {
        try {
            $order_process = self::orderProcess($request, $type);

            // send email using this method and this method will take care all the process
            if (!empty($request->selected_customer) && $request->send_email == 'on') {
                self::sendOrderMail(order_process: $order_process, request: $request, type: 'pos');
            }

            // update into database
            ProductOrder::where("id", $order_process["order_id"])->update([
                "status" => 'complete',
                "payment_status" => 'success',
            ]);

            Cart::instance(self::cartInstanceName())->destroy();

            return response()->json([
                "msg" => __("Purchase complete"),
                "type" => "success",
                "order_details" => self::invoice_details($order_process['order_id'])
            ]);
        } catch (Exception $exception) {
            return response()->json([
                "msg" => __("Purchase failed"),
                "type" => "error",
                "order_details" => []
            ]);
        }
    }

    private static function invoice_details($order_id)
    {
        $order_details = ProductOrder::find($order_id);

        return [
            'site_info' => [
                'name' => site_title(),
                'email' => get_static_option('order_receiving_email'),
                'website' => str_replace(['http://', 'https://'], '', url('/'))
            ],
            'customer' => [
                'name' => $order_details->name,
                'phone' => $order_details->phone,
                'email' => $order_details->email,
                'country' => $order_details->getCountry?->name,
                'state' => $order_details->getState?->name,
                'city' => $order_details->city,
                'address' => $order_details->address
            ],
            'invoice_number' => $order_details->id,
            'date' => $order_details->created_at->format('d/m/Y')
        ];
    }

    protected static function prepareOrderForAdmin($sub_order_id, $order_id, $product_id, $variant_id, $quantity, $price, $sale_price): array
    {
        return [
            "sub_order_id" => $sub_order_id,
            "order_id" => $order_id,
            "product_id" => $product_id,
            "variant_id" => $variant_id,
            "quantity" => $quantity,
            "price" => $price,
            "sale_price" => $sale_price,
        ];
    }

    protected static function prepareOrderForVendor($vendor_id, $order_id, $total_amount, $shipping_cost, $tax_amount, $order_address_id): array
    {
        return [
            "order_id" => $order_id,
            "vendor_id" => $vendor_id,
            "total_amount" => $total_amount,
            "shipping_cost" => $shipping_cost,
            "tax_amount" => $tax_amount,
            "order_address_id" => $order_address_id
        ];
    }
}
