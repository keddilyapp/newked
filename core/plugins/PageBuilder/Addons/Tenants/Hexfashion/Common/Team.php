<?php

namespace Plugins\PageBuilder\Addons\Tenants\Hexfashion\Common;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Repeater;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Helpers\RepeaterField;
use Plugins\PageBuilder\PageBuilderBase;
use function __;

class Team extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/themes/hexfashion/about/team.jpg';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);

        //repeater
        $output .= Repeater::get([
            'multi_lang' => false,
            'settings' => $widget_saved_values,
            'id' => 'repeater_data',
            'fields' => [
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_name',
                    'label' => __('Team Member Name')
                ],
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_designation',
                    'label' => __('Team Member Designation')
                ],
                [
                    'type' => RepeaterField::IMAGE,
                    'name' => 'repeater_image',
                    'label' => __('Team Member Image')
                ]
            ]
        ]);

        // add padding option
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $title = SanitizeInput::esc_html($this->setting_item('title')) ?? '';
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        $order_by = SanitizeInput::esc_html($this->setting_item('order_by'));
        $order = SanitizeInput::esc_html($this->setting_item('order'));

        $repeater = $this->setting_item('repeater_data') ?? '';

        $data = [
            'title' => $title,
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'repeater' => $repeater,
        ];

        return self::renderView('tenant.hexfashion.common.team', $data);
    }

    public function enable(): bool
    {
        return (bool)!is_null(tenant());
    }

    public function addon_title()
    {
        return __('Theme HexFashion : Team (01)');
    }
}
