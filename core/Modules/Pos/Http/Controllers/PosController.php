<?php

namespace Modules\Pos\Http\Controllers;

use App\Helpers\FlashMsg;
use App\Mail\BasicMail;
use App\Models\MediaUploader;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mail;
use Modules\CountryManage\Entities\City;
use Modules\CountryManage\Entities\Country;
use Modules\CountryManage\Entities\State;
use Modules\MobileApp\Http\Resources\Api\MobileFeatureProductResource;
use Modules\Pos\Http\Requests\AddNewCustomerRequest;
use Modules\Pos\Http\Requests\PosGatewayRequest;
use Modules\Pos\Http\Requests\PosOrderRequest;
use Modules\Pos\Services\CustomerServices;
use Modules\Pos\Services\OrderCheckoutService;
use Modules\Pos\Services\OrderService;
use Modules\Product\Entities\Product;
use Modules\TaxModule\Entities\TaxClass;
use Modules\TaxModule\Entities\TaxClassOption;
use Throwable;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:admin");
        $this->middleware('permission:pos|pos-settings', ['only', ['paymentGatewaySettings', 'updatePaymentGatewaySettings']]);
        $this->middleware('permission:pos|pos-pwa-settings', ['only', ['pwaSettings', 'updatePwaSettings']]);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('pos::index');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            "email" => "nullable"
        ]);

        return CustomerServices::searchCustomer($data['email'])->transform(function ($item) {
            $image = get_attachment_image_by_id($item->image);
            $item->image = !empty($image) ? $image['img_url'] : asset("assets/frontend/img/pos/profile.png");

            return $item;
        });
    }

    public function find($id)
    {
        $customer = CustomerServices::findCustomer($id);

        $customer->date = $customer->created_at->format("d M Y");

        $image = get_attachment_image_by_id($customer->image);
        $customer->image = !empty($image) ? $image['img_url'] : asset("assets/frontend/img/pos/profile.png");

        unset($customer->created_at);

        return $customer;
    }

    public function addNewCustomer(AddNewCustomerRequest $request)
    {
        try {
            User::create($request->validated());

            $message = "<p>" . __('Your account details,') . "</p>";
            $message .= "<p><strong>" . __('Name:') . "</strong> @name</p>";
            $message .= "<p><strong>" . __('Email:') . "</strong> @email</p>";
            $message .= "<p><strong>" . __('Username:') . "</strong> @username</p>";
            $message .= "<p><strong>" . __('Password:') . "</strong> @password</p>";
            $message = str_replace(["@name", "@email", "@username", "@password"], [$request->name, $request->email, $request->username, $request->account_password], $message);

            Mail::to(get_static_option('tenant_site_global_email'))->send(new BasicMail(
                $message,
                get_static_option('user_register_subject') ?? __('New User Register Email'),
            ));

            return response()->json([
                "msg" => __("Successfully added new customer"),
                "type" => "success"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "msg" => __("Failed to create customer") . $e->getMessage(),
                "type" => "danger"
            ]);
        }
    }

    /**
     * @throws Throwable
     */
    public function checkout(PosOrderRequest $request)
    {
        return OrderService::sendOrder($request, 'pos');
    }

    public function paymentGatewaySettings()
    {
        $countries = Country::published()->get();
        $states = State::where('country_id', get_static_option('pos_tax_country'))->get();
        $cities = City::where('state_id', get_static_option('pos_tax_state'))->get();

        return view("pos::payment-gateway-settings.gateway-settings", compact('countries','states','cities'));
    }

    public function updatePaymentGatewaySettings(PosGatewayRequest $request)
    {
        update_static_option("pos_payment_gateway_enable", (bool)$request->pos_payment_gateway_enable ?? false);
        update_static_option("pos_card_payment_gateway_enable", (bool)$request->pos_card_payment_gateway_enable ?? false);
        update_static_option("pos_e_wallet_payment_gateway_enable", (bool)$request->pos_e_wallet_payment_gateway_enable ?? false);
        update_static_option("pos_e_wallet_credential", json_encode([
            "e_wallet_name" => $request->e_wallet_name,
            "e_wallet_image" => $request->e_wallet_image,
        ]));

        return back()->with([
            "msg" => __("Successfully updated pos payment gateway"),
            "type" => "success"
        ]);
    }

    public function getPaymentGatewaySettings()
    {
        return [
            "pos_payment_gateway_enable" => get_static_option("pos_payment_gateway_enable"),
            "pos_card_payment_gateway_enable" => get_static_option("pos_card_payment_gateway_enable")
        ];
    }

    private function getPwaManifest()
    {
        $manifest_file = 'assets/pwa/manifest.json';

        $manifest_data = null;
        if (file_exists($manifest_file) && !is_dir($manifest_file))
        {
            $jsonString = file_get_contents($manifest_file);
            $manifest_data = json_decode($jsonString);
        }

        return $manifest_data;
    }

    private function pwaImageHandle($request)
    {
        try {
            $file = $request->file('icon');
            $file_name = 'logo.'.$file->getClientOriginalExtension();

            $old_file_destination = 'assets/pwa/'.$file_name;

            if (file_exists($old_file_destination))
            {
                unlink($old_file_destination);
            }

            $file->move('assets/pwa', $file_name);

            return [
                'status' => true,
                'file_name' => $file_name,
                'type' => "image/{$file->getClientOriginalExtension()}"
            ];
        } catch (\Exception $exception)
        {
            return [
                'status' => true
            ];
        }
    }

    private function getPwaShortName($sentence)
    {
        $words = explode(' ', $sentence);
        $firstLetters = '';

        foreach ($words ?? [] as $word) {
            $firstLetters .= $word[0];
        }

        return $firstLetters;
    }

    public function pwaSettings()
    {
        $manifest_data = $this->getPwaManifest();
        return view('pos::payment-gateway-settings.pwa-settings', compact('manifest_data'));
    }

    public function updatePwaSettings(Request $request)
    {
        $display_mode = [
            'fullscreen' => 'fullscreen',
            'standalone' => 'standalone'
        ];

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'display_mode' => 'required|in:'.implode(',',array_values($display_mode)),
            'color' => 'required',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png|max:2048|dimensions:ratio=1/1'
        ]);

        $manifest_data = $this->getPwaManifest();
        if (!empty($manifest_data))
        {
            if (property_exists($manifest_data, 'icons') && !empty($manifest_data->icons))
            {
                $manifest_data->start_url = '/index.php';
                $manifest_data->name = e(strip_tags($request->name));
                $manifest_data->short_name = $this->getPwaShortName(e(strip_tags($request->name)));
                $manifest_data->description = e(strip_tags($request->description));
                $manifest_data->display = $request->display_mode;
                $manifest_data->theme_color = e(strip_tags($request->color));
                $manifest_data->background_color = e(strip_tags($request->color));

                $icons = current($manifest_data->icons);
                if (property_exists($icons, 'src') && !empty($icons->src))
                {
                    if ($request->has('icon'))
                    {
                        $image_status = $this->pwaImageHandle($request);
                        if ($image_status['status'])
                        {
                            $manifest_data->icons[0]->src = $image_status['file_name'];
                            $manifest_data->icons[0]->type = $image_status['type'];
                        }
                    }
                } else {
                    $request->validate([
                        'icon' => 'required|image|mimes:jpg,jpeg,png|max:2048|dimensions:ratio=1/1'
                    ]);
                }

                $newJsonString = json_encode($manifest_data, JSON_UNESCAPED_SLASHES);
                file_put_contents('assets/pwa/manifest.json', $newJsonString);
            }
        } else {
            return back()->with(FlashMsg::explain('danger', __('Manifest file not found, Contact your administrator')));
        }

        return back()->with(FlashMsg::explain('success', 'PWA settings updated'));
    }

    public function taxSettings(Request $request)
    {
        $request->validate([
            'country' => 'required',
            'state' => 'nullable',
            'city' => 'nullable'
        ]);

        update_static_option('pos_tax_country', e(strip_tags($request->country)));
        update_static_option('pos_tax_state', (($request->state ?? null)));
        update_static_option('pos_tax_city', (($request->city ?? null)));
        update_static_option('pos_tax_postal_code', e(strip_tags($request->postal_code)));

        return back()->with([
            "msg" => __("Successfully updated POS default tax"),
            "type" => "success"
        ]);
    }

    public function taxClasses(Request $request)
    {
        abort_if($request->method() == "GET", 404);

        $tax_options_id = TaxClassOption::where('country_id', $request->country)
            ->when($request->state, function ($query) use ($request) {
                return $query->where('state_id', $request->state);
            })
            ->when($request->city, function ($query) use ($request) {
                return $query->where('city_id', $request->city);
            })
            ->when($request->postal_code, function ($query) use ($request) {
                return $query->where('postal_code', e(strip_tags($request->postal_code)));
            })
            ->distinct()
            ->pluck('class_id');

        $tax_classes = TaxClass::whereIn('id', $tax_options_id)->get(['id', 'name']);
        $markup = "<option value=''>".__('Select an option')."</option>";
        foreach ($tax_classes ?? [] as $item)
        {
            $markup .= "<option value='{$item->id}'>{$item->name}</option>";
        }

        return response()->json([
            'type' => 'success',
            'data' => $tax_classes,
            'markup' => $markup
        ]);
    }

    public function storeLocation()
    {
        return response()->json([
            'location' => get_static_option('pos_tax_country') ?? null
        ]);
    }

    public function locationSettings()
    {
        return route('tenant.admin.pos.payment-gateway-settings');
    }

    public function holdOrder(Request $request) {
        $request->validate([
            'cart_name'
        ]);

        $cart_name = e(strip_tags(trim($request->cart_name)));

        try {
            Cart::store($cart_name);
            Cart::destroy();
        } catch (\Exception $exception)
        {
            return response()->json([
                "msg" => __("Cart with this name already saved before"),
                "type" => "danger"
            ]);
        }

        return response()->json([
            "msg" => __("The order saved for later"),
            "type" => "success"
        ]);
    }

    public function getHoldOrder()
    {
        return \DB::table('shoppingcart')->select('identifier as name')->get();
    }

    public function deleteHoldOrder()
    {
        $id = trim(e(strip_tags(\request()->id)));

        try {
            \DB::table('shoppingcart')->where('identifier', $id)->delete();
        } catch (\Exception $exception) {
        }

        return response()->json([
            'type' => 'success'
        ]);
    }

    public function restoreHoldOrder()
    {
        if (count(Cart::content()) > 0)
        {
            return response()->json([
                'type' => 'danger',
                'msg' => __('Clear or complete current order first')
            ]);
        }

        $id = trim(e(strip_tags(\request()->id)));

        try {
            $cart = \DB::table('shoppingcart')->where('identifier', $id)->first();

            if ($cart)
            {
                $cartContent = unserialize($cart->content);
                \DB::table('shoppingcart')->where('identifier', $id)->delete();

                foreach ($cartContent as $item) {
                    Cart::add($item->id, $item->name, $item->qty, $item->price, 0, $item->options->toArray());
                }

                return response()->json([
                    'type' => 'success',
                    'msg' => __('Order is restored')
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'type' => 'danger',
                'msg' => __('Something went wrong')
            ]);
        }
    }
}
