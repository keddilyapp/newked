<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use App\Models\PlanPaymentGateway;
use App\Models\PlanTheme;
use App\Models\PricePlan;
use Database\Seeders\Tenant\DefaultDataSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class PricePlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:price-plan-list|price-plan-edit|price-plan-delete',['only' => ['all_price_plan']]);
        $this->middleware('permission:price-plan-create',['only' => ['create_price_plan','store_new_price_plan']]);
        $this->middleware('permission:price-plan-edit',['only' => ['edit_price_plan','update']]);
        $this->middleware('permission:price-plan-delete',['only' => ['delete']]);
    }

    public function create_price_plan(){
        return view('landlord.admin.price-plan.create');
    }

    public function all_price_plan(){
        $all_plans = PricePlan::orderBy('id','desc')->get();
        return view('landlord.admin.price-plan.index',compact('all_plans'));
    }

    public function delete($id){

        if(!tenant()){
           $plan = PricePlan::findOrFail($id);
           $plan->plan_features()->delete();
           $plan->plan_themes()->delete();
           $plan->plan_payment_gateways()->delete();
           $plan->delete();
        }else{
            PricePlan::findOrFail($id)->delete();
        }

        return response()->danger(ResponseMessage::delete());
    }

    public function edit_price_plan($id){
        $plan = PricePlan::find($id);
        $plan_payment_gateways = $plan->plan_payment_gateways->pluck('payment_gateway_name', 'id')->toArray();
        $plan_payment_gateways = implode(',', $plan_payment_gateways);

        return view('landlord.admin.price-plan.edit',compact('plan', 'plan_payment_gateways'));
    }
    public function store_new_price_plan(Request $request){
        $this->validate($request,[
            'title' => 'required|string',
            'package_badge' => 'required|string',
            'package_description' => 'nullable|string',
            'features' => 'required',
            'themes' => 'required',
            'payment_gateways' => 'required',
            'type' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required|integer',
            'page_permission_feature'=> 'nullable|integer|min:-1',
            'blog_permission_feature'=> 'nullable|integer|min:-1',
            'product_permission_feature'=> 'nullable|integer|min:-1',
            'storage_permission_feature'=> 'required|integer|min:-1',
        ]);

        try {
            //create data for price plan
            $price_plan = new PricePlan();
            $price_plan->title = SanitizeInput::esc_html($request->title);
            $price_plan->package_badge = SanitizeInput::esc_html($request->package_badge);
            $price_plan->description = SanitizeInput::esc_html($request->package_description);

            if (!tenant()) {
                $faq_item = $request->faq ?? ['title' => ['']];

                if ($request->has_trial != null) {
                    $price_plan->has_trial = true;
                    $price_plan->trial_days = $request->trial_days;
                }

                $price_plan->page_permission_feature = $request->page_permission_feature;
                $price_plan->blog_permission_feature = $request->blog_permission_feature;
                $price_plan->product_permission_feature = $request->product_permission_feature;
                $price_plan->storage_permission_feature = $request->storage_permission_feature;
                $price_plan->faq = serialize($faq_item);

            }

            $price_plan->type = $request->type;
            $price_plan->price = $request->price;
            $price_plan->status = $request->status;
            $price_plan->save();

            if (!tenant()) {
                $features = $request->features;
                foreach ($features as $feat) {
                    PlanFeature::create([
                        'plan_id' => $price_plan->id,
                        'feature_name' => $feat,
                    ]);
                }
            }

            $themes = $request->themes;
            foreach ($themes as $theme) {
                PlanTheme::create([
                    'plan_id' => $price_plan->id,
                    'theme_slug' => $theme,
                ]);
            }

            $payment_gateways = $request->payment_gateways;
            $payment_gateways = array_filter(explode(',', $payment_gateways));
            foreach ($payment_gateways as $gateway) {
                if (empty($gateway))
                {
                    continue;
                }
                PlanPaymentGateway::create([
                    'plan_id' => $price_plan->id,
                    'payment_gateway_name' => $gateway,
                ]);
            }
        } catch (\Exception $e)
        {

        }

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function update(Request $request){
        $type_validation  = tenant() ? 'nullable' : 'required';
        $this->validate($request,[
            'id' => 'required|integer',
            'title' => 'required|string',
            'package_badge' => 'required|string',
            'package_description' => 'nullable|string',
            'features' => 'required',
            'themes' => 'required',
            'payment_gateways' => 'required',
            'type' => ''.$type_validation.'|integer',
            'price' => 'required|numeric',
            'status' => 'required|integer',
            'page_permission_feature'=> 'nullable|integer|min:-1',
            'blog_permission_feature'=> 'nullable|integer|min:-1',
            'product_permission_feature'=> 'nullable|integer|min:-1',
            'storage_permission_feature'=> 'required|integer|min:-1',
        ]);

        try {
            //create data for price plan
            $price_plan = PricePlan::find($request->id);
            $price_plan->title = SanitizeInput::esc_html($request->title);
            $price_plan->package_badge = SanitizeInput::esc_html($request->package_badge);
            $price_plan->description = SanitizeInput::esc_html($request->package_description);

            if (!tenant()) {
                $faq_item = $request->faq ?? ['title' => ['']];

                if (!empty($faq_item)) {
                    $faq_set = [];
                    foreach ($request->faq as $key => $faq) {
                        $faqs = [];
                        foreach ($faq as $f) {
                            $faqs[] = SanitizeInput::esc_html($f);
                        }
                        $faq_set[$key] = $faqs;
                    }
                }

                if ($request->has_trial != null) {
                    $price_plan->has_trial = true;
                    $price_plan->trial_days = $request->trial_days;
                } else {
                    $price_plan->has_trial = false;
                    $price_plan->trial_days = null;
                }

                $price_plan->page_permission_feature = $request->page_permission_feature;
                $price_plan->blog_permission_feature = $request->blog_permission_feature;
                $price_plan->product_permission_feature = $request->product_permission_feature;
                $price_plan->storage_permission_feature = $request->storage_permission_feature;
                $price_plan->faq = serialize($faq_set);
            }

            $price_plan->type = $request->type;
            $price_plan->price = $request->price;
            $price_plan->status = $request->status;
            $price_plan->save();

            if (!tenant()) {
                $price_plan->plan_features()->delete();
                $features = $request->features;
                foreach ($features as $feat) {
                    PlanFeature::where('plan_id', $price_plan->id)->create([
                        'plan_id' => $price_plan->id,
                        'feature_name' => $feat,
                    ]);
                }
            }

            $price_plan->plan_themes()->delete();
            $themes = $request->themes;
            foreach ($themes as $theme) {
                PlanTheme::create([
                    'plan_id' => $price_plan->id,
                    'theme_slug' => $theme,
                ]);
            }

            $price_plan->plan_payment_gateways()->delete();
            $payment_gateways = $request->payment_gateways;
            $payment_gateways = array_filter(explode(',', $payment_gateways));
            foreach ($payment_gateways as $gateway) {
                if (empty($gateway))
                {
                    continue;
                }
                PlanPaymentGateway::create([
                    'plan_id' => $price_plan->id,
                    'payment_gateway_name' => $gateway,
                ]);
            }
        } catch (\Exception $exception)
        {

        }

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function price_plan_settings()
    {
        return view('landlord.admin.price-plan.settings');
    }

    public function update_price_plan_settings(Request $request)
    {
        $languages = [
            'en_GB' => 'English (UK)',
            'ar' => 'العربية',
            'hi_IN' => 'हिन्दी',
            'tr_TR' => 'Türkçe',
            'it_IT' => 'Italiano',
            'pt_PT' => 'Português',
            'pt_BR' => 'Português do Brasil',
            'pt_AO' => 'Português de Angola'
        ];

        abort_if(!array_key_exists($request->default_language, $languages), 403);

        $request->validate([
            'package_expire_notify_mail_days'=> 'required|array',
            'package_expire_notify_mail_days.*'=> 'required|max:7',
            'default_theme'=> 'required',
            'default_language'=> 'required',
            'zero_plan_limit' => 'required|integer|min:1',
            'tenant_admin_default_username' => 'nullable|min:3',
            'tenant_admin_default_password' => 'nullable|min:6',
            'file_input' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'has_file' => 'nullable',
        ]);

        update_static_option('package_expire_notify_mail_days',json_encode($request->package_expire_notify_mail_days));
        update_static_option('default_theme', $request->default_theme);
        update_static_option('zero_plan_limit', $request->zero_plan_limit);
        update_static_option_central('default_language', $request->default_language);
        update_static_option_central('tenant_admin_default_username', $request->tenant_admin_default_username);
        update_static_option_central('tenant_admin_default_password', $request->tenant_admin_default_password);

        if ($request->has_file === 'no' && get_static_option_central('plan_default_logo')) {
            $this->removeImage();
        }

        if ($request->hasFile('file_input')) {
            $this->manageDefaultImage($request);
        }

        return response()->success(ResponseMessage::SettingsSaved());
    }

    private function removeImage()
    {
        $image_name = get_static_option('plan_default_logo');
        $folder_path = global_assets_path("assets/landlord/uploads/default-uploads/{$image_name}");

        if (! is_dir($folder_path) && file_exists($folder_path)) {
            unlink($folder_path);
            delete_static_option_central('plan_default_logo');
        }
    }

    private function manageDefaultImage(Request $request)
    {
        $image = $request->file('file_input');

        $image_extension = $image->extension();
        [$image_width, $image_height] = getimagesize($image);

        $resize_full_image = Image::make($image)->resize($image_width, $image_height, function ($constraint) {
            $constraint->aspectRatio();
        });

        $image_new_name = "plan-default-logo.{$image_extension}";
        $folder_path = global_assets_path('assets/landlord/uploads/default-uploads');

        try {
            if (! is_dir($folder_path)) {
                mkdir($folder_path);
            }

            $previous_file = $folder_path.'/'.get_static_option_central('plan_default_logo');
            if (! is_dir($previous_file) && file_exists($previous_file)) {
                unlink($previous_file);
            }

            $resize_full_image->save("{$folder_path}/{$image_new_name}");

            if (file_exists("{$folder_path}/{$image_new_name}")) {
                update_static_option_central('plan_default_logo', $image_new_name);
            }
        } catch (\Exception $exception) {

        }
    }
}
