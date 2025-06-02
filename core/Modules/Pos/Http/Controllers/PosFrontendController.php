<?php

namespace Modules\Pos\Http\Controllers;

use App\Enums\ProductTypeEnum;
use App\Helpers\FlashMsg;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Attributes\Entities\Color;
use Modules\Attributes\Entities\Size;
use Modules\Campaign\Entities\CampaignSoldProduct;
use Modules\Pos\Traits\OrderTrait;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductInventory;
use Modules\Product\Entities\ProductInventoryDetail;
use Modules\TaxModule\Services\CalculateTaxBasedOnCustomerAddress;

class PosFrontendController extends Controller
{
    use OrderTrait;
    public function cart_items()
    {
        return Cart::instance('default')->content();
    }

    public function taxAmount()
    {
        return [
            'tax' => get_static_option('default_shopping_tax'),
        ];
    }

    public function updateQuantity(Request $request)
    {
        Cart::instance('default')->update($request->rowId, $request->qty);

        return response()->json([
            'msg' => __('Updated'),
        ]);
    }

    public function clearCartItems()
    {
        Cart::instance('default')->destroy();

        return response()->json(FlashMsg::explain('success', __('Cart cleared')), 200);
    }

    public function removeCartItem(Request $request)
    {
        $request->validate([
            'rowId' => 'required|string',
        ]);

        Cart::instance('default')->remove($request->rowId);

        return response()->json(FlashMsg::explain('success', __('Item removed from cart')));
    }

    public function add_to_cart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required',
            'pid_id' => 'nullable',
            'product_variant' => 'nullable',
            'selected_size' => 'nullable',
            'selected_color' => 'nullable'
        ]);

        $product_inventory = ProductInventory::where('product_id', $request->product_id)->first();
        if ($request->product_variant) {
            $product_inventory_details = ProductInventoryDetail::where('id', $request->product_variant)->first();
        } else {
            $product_inventory_details = null;
        }

        if ($product_inventory_details && $request->quantity > $product_inventory_details->stock_count) {
            return response()->json([
                'type' => 'warning',
                'quantity_msg' => __('Requested quantity is not available. Only available quantity is added to cart')
            ]);
        } elseif ($product_inventory && $request->quantity > $product_inventory->stock_count) {
            return response()->json([
                'type' => 'warning',
                'quantity_msg' => __('Requested quantity is not available. Only available quantity is added to cart')
            ]);
        }

        if (!empty($product->campaign_product)) {
            $sold_count = CampaignSoldProduct::where('product_id', $request->product_id)->first();
            $product = Product::where('id', $request->product_id)->withSum('taxOptions','rate')->first();

            $product_left = $sold_count !== null ? $product->campaign_product->units_for_sale - $sold_count->sold_count : null;
        } else {
            $product_left = $product_inventory_details->stock_count ?? $product_inventory->stock_count;
        }

        // now we will check if product left is equal or bigger than quantity than we will check
        if (!($request->quantity <= $product_left) && $sold_count) {
            return response()->json([
                'type' => 'warning',
                'quantity_msg' => __('Requested amount can not be cart. Campaign product stock limit is over!')
            ]);
        }

        try {
            $cart_data = $request->all();
            $product = Product::withSum('taxOptions','rate')->findOrFail($cart_data['product_id']);

            $sale_price = $product->sale_price;
            $additional_price = 0;

            if ($product->campaign_product && campaign_running_status($product)) {
                $sale_price = $product?->campaign_product?->campaign_price;
            }

            if ($product_inventory_details) {
                $additional_price = $product_inventory_details->additional_price;
                $additional_cost = $product_inventory_details->add_cost;
            }

            $final_sale_price = $sale_price + $additional_price;

            $product_image = $product->image_id;
            if ($cart_data['product_variant']) {
                $size_name = Size::find($cart_data['selected_size'] ?? 0)->name ?? '';
                $color_name = Color::find($cart_data['selected_color'] ?? 0)->name ?? '';

                $product_detail = ProductInventoryDetail::where("id", $cart_data['product_variant'])->first();

                $product_attributes = $product_detail->attribute?->pluck('attribute_value', 'attribute_name', 'inventory_details')
                    ->toArray();

                $options = [
                    'variant_id' => $request->product_variant,
                    'color_name' => $color_name,
                    'size_name' => $size_name,
                    'attributes' => $product_attributes,
                    'image' => $product_detail->image ?? $product_image
                ];
            } else {
                $options = ['image' => $product_image];
            }

            $category = $product?->category?->id;
            $subcategory = $product?->subCategory?->id ?? null;

            $options['used_categories'] = [
                'category' => $category,
                'subcategory' => $subcategory
            ];
            $options['base_cost'] = $product->cost + ($additional_cost ?? 0);
            $options['type'] = ProductTypeEnum::PHYSICAL;
            $options["tax_options_sum_rate"] = $product->tax_options_sum_rate ?? 0;

            Cart::instance("default")->add(['id' => $cart_data['product_id'], 'name' => $product->name, 'qty' => $cart_data['quantity'], 'price' => $final_sale_price, 'weight' => '0', 'options' => $options]);

            return response()->json([
                'type' => 'success',
                'msg' => __('Item added to cart')
            ]);
        } catch (\Exception $exception) {

            return response()->json([
                'type' => 'error',
                'error_msg' => __('Something went wrong!'),
            ]);
        }
    }
}
