<?php

namespace Plugins\PageBuilder\Addons\Tenants\Aromatic\Common;

use App\Helpers\InstagramFeedHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\SanitizeInput;
use Illuminate\Support\Facades\Cache;
use Modules\Campaign\Entities\Campaign;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class InstagramWidget extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/themes/aromatic/home/instagram_feed.jpeg';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();

        $instagram_access_token = get_static_option('instagram_access_token');

        if ($instagram_access_token)
        {
            $tenant_id = tenant()->id;
            $cache_key = "tenant::{$tenant_id}::instagram_feed";

            $instagram_data = Cache::remember($cache_key, now()->addDays(2), function () {
                return (new InstagramFeedHelper())->fetch();
            });

            if (! $instagram_data['status']) {
                Cache::forget($cache_key);
                $output .= "<p class='bg-danger text-white p-3 rounded-1'>{$instagram_data['message']}</p>";
            }

            $output .= Text::get([
                'name' => 'title',
                'label' => __('Title'),
                'value' => $widget_saved_values['title'] ?? null,
            ]);

            $output .= Number::get([
                'name' => 'post_items',
                'label' => __('Post Item'),
                'value' => $widget_saved_values['post_items'] ?? null,
            ]);

            $output .= Switcher::get([
                'name' => 'media_redirection',
                'label' => __('Media Redirection'),
                'value' => $widget_saved_values['media_redirection'] ?? null,
                'info' => __('Open in a new tab?')
            ]);

            // add padding option
            $output .= $this->padding_fields($widget_saved_values);
            $output .= $this->admin_form_submit_button();
        } else {
            $url = route('tenant.integration');
            $warning_text = __("It seems like an Instagram access token wasn't found. Please set your token through the integration settings to");
            $continue_text = __("continue");

            $output .= "<p class='bg-white p-4 fw-normal fs-6'>{$warning_text} <a href='{$url}#instagram' class='text-primary fs-6' style='text-decoration: underline'>{$continue_text}</a></p>";
        }

        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));
        $widget_saved_values = $this->get_settings();

        $widget_title = SanitizeInput::esc_html(($this->setting_item('title') ?? ''));
        $post_items = $widget_saved_values['post_items'] ?? '';
        $media_redirection= $widget_saved_values['media_redirection'] ?? null;

        $instagram_data = Cache::remember('instagram_feed',now()->addDays(2),function () use($post_items) {
            return (new InstagramFeedHelper())->fetch($post_items);
        });

        $data = [
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'title' => $widget_title,
            'media_redirection' => $media_redirection,
            'instagram_data' => $instagram_data
        ];

        return self::renderView('tenant.aromatic.common.instagram-feed', $data);
    }

    public function addon_title()
    {
        return __('Aromatic: Instagram Feed');
    }
}

