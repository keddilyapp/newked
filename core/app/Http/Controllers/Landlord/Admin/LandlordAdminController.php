<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\ModuleMetaData;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\PaymentLogs;
use App\Models\PricePlan;
use App\Models\Tenant;
use App\Models\Testimonial;
use App\Models\UpdateInfo;
use App\Models\User;
use App\Services\AdminTheme\MetaDataHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Blog\Entities\Blog;
use function view;

class LandlordAdminController extends Controller
{
    private const BASE_VIEW_PATH = 'landlord.admin.';

    public function dashboard(){
        $total_admin= Admin::count();

        $total_user = 0;

        $update_info = [];
        try{
             $total_user= User::count();
             $update_info = UpdateInfo::whereNull('read_at')->get();
        }catch(\Exception $e){

        }

        $all_tenants = Tenant::whereValid()->count();
        $total_price_plan = PricePlan::count();
        $total_brand = Brand::all()->count();
        $total_testimonial = Testimonial::all()->count();
        $recent_order_logs = PaymentLogs::orderBy('id','desc')->take(5)->get();

        return view(self::BASE_VIEW_PATH.'admin-home',compact(
            'total_admin',
            'total_user',
            'all_tenants',
            'total_brand',
            'total_price_plan',
            'total_testimonial',
            'recent_order_logs',
            'update_info'
        ));
    }

    public  function health()
    {
        $all_user = Admin::all()->except(Auth::id());
        return view(self::BASE_VIEW_PATH.'health')->with(['all_user' => $all_user]);
    }

    public function change_password(){
        $theme_meta_instance = MetaDataHelpers::Init();
        $theme_info = $theme_meta_instance->getThemesInfo();
        $render_view_file = $theme_meta_instance->getThemeOverrideViews('metronic','change_password',self::BASE_VIEW_PATH.'auth.change-password');

        return view($render_view_file);
//        return view(self::BASE_VIEW_PATH.'auth.change-password');
    }

    public function update_change_password(Request $request){
        $this->validate($request,[
            'password' => 'required|confirmed|min:8'
        ]);

        Admin::find(auth('admin')->id())->update(['password'=> Hash::make($request->password)]);
        //store this data in landlord database
        Auth::guard('admin')->logout();
        return response()->success(__('Password Change Success'));
    }

    public function edit_profile(){
        $theme_meta_instance = MetaDataHelpers::Init();
        $theme_info = $theme_meta_instance->getThemesInfo();
        $render_view_file = $theme_meta_instance->getThemeOverrideViews('metronic','edit_profile',self::BASE_VIEW_PATH.'auth.edit-profile');

        return view($render_view_file);
//        return view(self::BASE_VIEW_PATH.'auth.edit-profile');
    }

    public function update_edit_profile(Request $request){
        $this->validate($request,[
            'name' => 'required|string',
            'email' => 'required|email|unique:admins,email,'.auth('admin')->id(),
            'mobile' => 'nullable|numeric',
            'image' => 'nullable|integer',
        ]);

        Admin::find(auth('admin')->id())->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile ,
            'image' => $request->image ,
        ]);

        //store this data in landlord database
        return response()->success(__('Settings Saved'));
    }

    public function topbar_settings()
    {
        return view('landlord.admin.topbar-settings');
    }

    public function update_topbar_settings(Request $request)
    {
        $request->validate([
            'topbar_twitter_url'=>'nullable',
            'topbar_linkedin_url'=>'nullable',
            'topbar_facebook_url'=>'nullable',
            'topbar_youtube_url'=>'nullable',
            'landlord_frontend_language_show_hide'=>'nullable',
        ]);

        $data = [
            'topbar_twitter_url',
            'topbar_linkedin_url',
            'topbar_facebook_url',
            'topbar_youtube_url',
            'landlord_frontend_language_show_hide',
        ];

        foreach ($data as $item)
        {
            update_static_option($item, $request->$item);
        }

        return response()->success(__('Settings Saved'));
    }

    public function get_chart_data_month(Request $request){
        /* -------------------------------------
            TOTAL ORDER BY MONTH CHART DATA
        ------------------------------------- */
        $all_donation_by_month = PaymentLogs::select('package_price','created_at')->where(['payment_status' => 'complete'])
            ->whereYear('created_at',date('Y'))
            ->get()
            ->groupBy(function ($query){
                return Carbon::parse($query->created_at)->format('F');
            })->toArray();
        $chart_labels = [];
        $chart_data= [];
        foreach ($all_donation_by_month as $month => $amount){
            $chart_labels[] = $month;
            $chart_data[] =  array_sum(array_column($amount,'package_price'));
        }
        return response()->json( [
            'labels' => $chart_labels,
            'data' => $chart_data
        ]);
    }

    public function get_chart_by_date_data(Request $request){
        /* -----------------------------------------------------
           TOTAL ORDER BY Per Day In Current month CHART DATA
       -------------------------------------------------------- */
        $all_donation_by_month = PaymentLogs::select('package_price','created_at')->where(['payment_status' => 'complete'])
            // ->whereMonth('created_at',date('m'))
            ->whereDate('created_at', '>', Carbon::now()->subDays(30))
            ->get()
            ->groupBy(function ($query){
                return Carbon::parse($query->created_at)->format('D, d F Y');
            })->toArray();
        $chart_labels = [];
        $chart_data= [];
        foreach ($all_donation_by_month as $month => $amount){
            $chart_labels[] = $month;
            $chart_data[] =  array_sum(array_column($amount,'package_price'));
        }

        return response()->json( [
            'labels' => $chart_labels,
            'data' => $chart_data
        ]);
    }
}
