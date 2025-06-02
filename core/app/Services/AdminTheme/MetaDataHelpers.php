<?php

namespace App\Services\AdminTheme;

class MetaDataHelpers
{
    protected array $core_styles = [
        'materialdesignicons' => 'assets/landlord/admin/css/materialdesignicons.min',
        'vendor-bundle-base' => 'assets/landlord/admin/css/vendor.bundle.base',
        'style' => 'assets/landlord/admin/css/style',
        'select2' => 'assets/common/css/select2.min',
        'flatpickr' =>'assets/common/css/flatpickr.min',
        'toastr' => 'assets/common/css/toastr',
        'flaticon' => 'assets/tenant/frontend/css/flaticon',
        'line-awesome' => 'assets/landlord/frontend/css/line-awesome.min',
        'module-fix-style' => 'assets/tenant/backend/css/module-fix-style',
        'dark-mode' => 'assets/landlord/admin/css/dark-mode',
        'rtl' => 'assets/landlord/admin/css/rtl',
    ];

    protected array $core_styles_js = [
        'vendor-bundle-base-js' => 'assets/landlord/admin/js/vendor.bundle.base',
        'hoverable-collapse' => 'assets/landlord/admin/js/hoverable-collapse',
        'off-canvas' => 'assets/landlord/admin/js/off-canvas',
        'misc' => 'assets/landlord/admin/js/misc',
        'axios' =>'assets/landlord/common/js/axios.min',
        'sweetalert2' => 'assets/landlord/common/js/sweetalert2',
        'jquery' => 'assets/common/js/countdown.jquery',
        'flatpickr-js' => 'assets/common/js/flatpickr',
        'toastr-js' => 'assets/common/js/toastr.min',
        'select2-js' => 'assets/common/js/select2.min',
        'helpers-js' => 'assets/js/helpers',
    ];


    protected array $themes = [];
    protected $active_theme;
    private bool $instance;

    public static function Init(){
        if (!empty((new self())->instance)){
            return (new self())->instance;
        }else {
            return (new self())->fetchThemeInfo();
        }
    }

    public function getThemesInfo()
    {
        return $this->themes;
    }

    public static function getActiveTheme()
    {
        //todo:: implement active theme data
    }

    private function setActiveTheme($theme){}
    private function fetchThemeInfo()
    {
        // work on getting meta information from the theme meta files
        $allThemeData = [];
        $allDirectories = glob(base_path() . '/resources/views/admin-themes/*', GLOB_ONLYDIR);
        foreach ($allDirectories as $dire) {
            // scan all the json file
            $currFolderName = pathinfo($dire, PATHINFO_BASENAME);
            $themeInformation = $this->getIndividualThemeData($currFolderName);
            $allThemeData[$currFolderName] = $themeInformation;
        }

        //store data into class scope memory
        $this->themes = $allThemeData;
        return $this;
    }

    private function getIndividualThemeData($themeName, bool $returnType = false)
    {
        $filePath = $this->getThemePath($themeName) . '/theme.json';
        if (file_exists($filePath) && !is_dir($filePath)) {

            //cache data for 10days
            $themeinfo = json_decode(file_get_contents($filePath), $returnType);


            // check for theme screenshort
            $screenshot_path = $this->getThemeScreenshot($themeName);
            if (!is_null($screenshot_path)){
                $themeinfo->screenshort = $this->getThemeScreenshot($themeName); //need to work on the render path of the theme screenshot
            }
            return $themeinfo;
        }
    }

    private function getThemeScreenshot($themeName)
    {

        $filePath = $this->getThemePath($themeName);
        $screenshotExtensions = ['png', 'jpg', 'jpeg'];

        // Iterate through each extension to check for existence of the screenshot
        $screenshotPath = null;
        foreach ($screenshotExtensions as $ext) {
            $possiblePath = $filePath . '/screenshot.' . $ext;
            if (file_exists($possiblePath)) {
                $screenshotPath = 'screenshot.' . $ext;
                break;
            }
        }

        // If screenshot is found, $screenshotPath will contain the path to the file
        return $screenshotPath !== null ? route('tenant.admin.theme.image.file.url',[
            'theme_name' => $themeName,
            'image_path' => $screenshotPath
        ]) : null;
    }

    public function getThemesStyles($theme_name,$type="header"|"footer")
    {
        $theme_data = $this->getIndividualThemeData($theme_name);

        $hook_name = $type === 'header' ? 'headerHook' : 'footerHook';
        //get headerHook data
        $header_hook_data = !is_null($theme_data) && property_exists($theme_data,$hook_name) ? $theme_data?->$hook_name : null;


        $styles = '';
        if (!is_null($header_hook_data) && property_exists($header_hook_data,'styles')){
            $theme_styles = $header_hook_data->styles;
            foreach( $theme_styles as $css_file){
                $decoded = (array)$css_file;
            $file_path = trim(current($decoded),'.css');
                if (get_static_option('tenant_admin_dashboard_theme') == 'metronic'){
                    $styles .= '<link href="'.route( 'tenant.admin.theme.css.file.url', [
                            'theme_name' => $theme_name,
                            'file_path' => str_replace('/','__',$file_path)
                        ]).'" rel="stylesheet">'."\n";
                }
            }
        }

        //todo:: write code for load dark mode styles from themes

        if (!is_null($header_hook_data) && property_exists($header_hook_data,'dark_mode')){
            $theme_styles = $header_hook_data->dark_mode;
            foreach( $theme_styles as $css_file){
                if (!empty(get_static_option('dark_mode_for_admin_panel'))){
                    $decoded = (array)$css_file;
                    $file_path = trim(current($decoded),'.css');
                    $styles .= '<link href="'.route( 'tenant.admin.theme.css.file.url', [
                            'theme_name' => $theme_name,
                            'file_path' => str_replace('/','__',$file_path)
                        ]).'" rel="stylesheet">'."\n";
                }
            }
        }

        //todo:: write code for load rtl styles from themes
        if (!is_null($header_hook_data) && property_exists($header_hook_data,'rtl_style')){
            $theme_styles = $header_hook_data->rtl_style;
                foreach( $theme_styles as $css_file){
                    if (\App\Enums\LanguageEnums::getdirection(get_user_lang_direction()) === 'rtl'){
                        $decoded = (array)$css_file;
                        $file_path = trim(current($decoded),'.css');
                        $styles .= '<link href="'.route( 'tenant.admin.theme.css.file.url', [
                                'theme_name' => $theme_name,
                                'file_path' => str_replace('/','__',$file_path)
                            ]).'" rel="stylesheet">'."\n";
                    }
            }
        }

        return $styles;
    }

    public function getThemesScriptsJs($theme_name,$type="header"|"footer")
    {
        $theme_data = $this->getIndividualThemeData($theme_name);

        $hook_name = $type === 'header' ? 'headerHook' : 'footerHook';
        //get headerHook data
        $footer_hook_data = !is_null($theme_data) && property_exists($theme_data,$hook_name) ? $theme_data->$hook_name : null;

        $scripts = '';
        if (!is_null($footer_hook_data) && property_exists($footer_hook_data,'scripts')){
            $theme_scripts = $footer_hook_data->scripts;
            foreach( $theme_scripts as $js_file){
                $decoded = (array)$js_file;
                $file_path = trim(current($decoded),'.js');
                if (get_static_option('tenant_admin_dashboard_theme') == 'metronic'){
                    $scripts .= '<script src="'.route( 'tenant.admin.theme.js.file.url', [
                            'theme_name' => $theme_name,
                            'file_path' => str_replace('/','__',$file_path)
                        ]).'"></script>'."\n";
                }
            }
        }

        return $scripts;
    }

    /*
        get existing view override blade file

    @return this will return a view blade file path which need to render with existing data
    */
    public function getThemeOverrideViews($theme_name,string $name,string $fallback=null)
    {
        $theme_data = $this->getIndividualThemeData($theme_name);

        //get headerHook data
        $overrideExistingViews = is_object($theme_data) && property_exists($theme_data,'overrideExistingViews') ? $theme_data->overrideExistingViews : null;

        $return_path = $fallback;
        if (is_object($theme_data) && property_exists($overrideExistingViews,$name)){

            //return actual view path...
            $blade_file_name = $overrideExistingViews->$name;
            $return_path = 'admin-themes.'.$theme_name.'.views.'.$blade_file_name;
//            dd($blade_file_name,'admin-themes.'.$theme_name.'.views.'.$blade_file_name);
//            dd($overrideExistingViews);
        }

        return $return_path;
    }

    private function getThemePath( $themeName)
    {
        return base_path() . '/resources/views/admin-themes/'.$themeName;
    }

    public function getRenderableCoreStyles(array $files=[]) : string
    {
        $all_core_styles = $this->core_styles;

        foreach($all_core_styles as $file){
            if(in_array($file, $files) && $files[$file] !== false){
                unset($all_core_styles[$file]);
            }
        }
        return $this->style_markup($all_core_styles);
    }

    protected function style_markup($files_array)
    {
        $style_markup = '';
        foreach ($files_array as $file_name => $file_path) {
            //check if the file is rlt or darkmode file.
            if ($file_name === 'dark-mode' && !empty(get_static_option('dark_mode_for_admin_panel'))){
                $style_markup .=  '<link href="'.global_asset($file_path) .'.css" rel="stylesheet">'."\n";
            }
            // check if the file is rlt or rtl file.
            elseif ($file_name === 'rtl' && \App\Enums\LanguageEnums::getdirection(get_user_lang_direction()) === 'rtl'){
                $style_markup .=  '<link href="'.global_asset($file_path) .'.css" rel="stylesheet">'."\n";
            }
            elseif (!in_array($file_name, ['dark-mode', 'rtl'])){
                $style_markup .=  '<link href="'.global_asset($file_path) .'.css" rel="stylesheet">'."\n";
            }
        }


        return $style_markup;
    }


    public function getRenderableCoreStylesJs(array $files=[]) : string
    {
        $all_core_styles_js = $this->core_styles_js;

        foreach($all_core_styles_js as $file){
            if(in_array($file, $files) && $files[$file] !== false){
                unset($all_core_styles_js[$file]);
            }
        }
        return $this->style_js_markup($all_core_styles_js);
    }

    protected function style_js_markup($files_array)
    {
        $style_js_markup = '';
        foreach ($files_array as $file_name => $file_path) {
            //todo::  check if the file is rlt or darkmode file.
            $style_js_markup .=  '<script src="'.global_asset($file_path) .'.js"></script>'."\n";
        }
        return $style_js_markup;
    }

}
