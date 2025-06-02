<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Helpers\ResponseMessage;
use App\Models\StaticOption;
use App\Models\Tenant;
use App\Services\AdminTheme\MetaDataHelpers;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class TenantDashboardThemeManageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $themes = MetaDataHelpers::Init();
        $all_themes = $themes->getThemesInfo();

        /* Experiment  */

        $themes->getThemesStyles('metronic','header');
        $themes->getThemesScriptsJs('metronic','footer');

        /* Experiment  */

        return view('tenant.admin.pages.dashboard.index',compact('all_themes'));
    }

    public function create()
    {
        return view('thememanage::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('thememanage::show');
    }


    public function edit($id)
    {
        return view('thememanage::edit');
    }

    public function update(Request $request, $slug)
    {
        $themes = MetaDataHelpers::Init();
        $all_themes = $themes->getThemesInfo();

        foreach ($all_themes as $theme_slug) {
            if ($slug == $theme_slug->slug) {
                update_static_option('tenant_admin_dashboard_theme', $slug);
                return response()->success(ResponseMessage::SettingsSaved());
            }
        }


        $theme_setting_type = $request->theme_setting_type;
        $requested_theme = $request->tenant_default_theme;

//        try {
//            $tenant_id = \tenant()->id;
//            Tenant::where('id', $tenant_id)->update([
//                'theme_slug' => $requested_theme
//            ]);
//        } catch (\Exception $exception) {}


//        if($theme_setting_type == 'set_theme_with_demo_data'){
//            $data_imported = $this->set_new_home($requested_theme);
//
//            if (!$data_imported['status'])
//            {
//                return response()->json($data_imported);
//            }
//        }

    }

    public function destroy($id)
    {
        //
    }
}
