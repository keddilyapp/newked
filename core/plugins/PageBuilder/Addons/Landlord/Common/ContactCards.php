<?php

namespace Plugins\PageBuilder\Addons\Landlord\Common;

use App\Facades\GlobalLanguage;
use App\Helpers\LanguageHelper;
use App\Helpers\SanitizeInput;
use App\Models\FormBuilder;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Repeater;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Slider;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Helpers\RepeaterField;
use Plugins\PageBuilder\PageBuilderBase;

class ContactCards extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Landlord/home/contact_card.jpg';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        //repeater
        $output .= Repeater::get([
            'multi_lang' => false,
            'settings' => $widget_saved_values,
            'id' => 'contact_repeater',
            'fields' => [
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_title',
                    'label' => __('Title')
                ],

                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_info',
                    'label' => __('Information')
                ],

                [
                    'type' => RepeaterField::IMAGE,
                    'name' => 'repeater_image',
                    'label' => __('Image')
                ],
            ]
        ]);

        // add padding option
        $output .= $this->section_id_and_class_fields($widget_saved_values);
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        $repeater_data = $this->setting_item('contact_repeater');
        $section_id = SanitizeInput::esc_html($this->setting_item('section_id'));

        $data = [
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'repeater_data'=> $repeater_data,
            'section_id'=> $section_id,
        ];

        return self::renderView('landlord.addons.common.contact_cards',$data);

    }

    public function enable(): bool
    {
        return (bool) is_null(tenant());
    }

    public function addon_title()
    {
        return __('Contact Cards');
    }
}
