<?php

namespace Modules\ShippingPlugin\Http\PageBuilder\Addons;

use Plugins\PageBuilder\Fields\ColorPicker;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Fields\Textarea;
use Plugins\PageBuilder\PageBuilderBase;

class ShippingTracker extends PageBuilderBase
{
    // This function return the image name of the addon
    public function preview_image()
    {
        return 'track-order.png';
    }

    // This function points the location of the image, It accept only module name
    public function setAssetsFilePath()
    {
        return externalAddonImagepath('ShippingPlugin');
    }

    // This function contains addon settings while using the addon in the page builder
    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'section_title',
            'label' => __('Section Title'),
            'value' => $widget_saved_values['section_title'] ?? null,
        ]);

        $output .= Select::get([
            'name' => 'section_title_position',
            'label' => __('Section Title Alignment'),
            'options' => [
                'start' => __('Left'),
                'center' => __('Center'),
                'end' => __('Right'),
            ],
            'value' => $widget_saved_values['section_title_position'] ?? null,
        ]);

        $output .= Text::get([
            'name' => 'label_title',
            'label' => __('Label Title'),
            'value' => $widget_saved_values['label_title'] ?? null,
            'info' => 'If you do not want to use it, leave it empty'
        ]);

        $output .= Text::get([
            'name' => 'input_placeholder',
            'label' => __('Input Placeholder'),
            'value' => $widget_saved_values['input_placeholder'] ?? null,
        ]);

        $output .= Text::get([
            'name' => 'button_text',
            'label' => __('Button Text'),
            'value' => $widget_saved_values['button_text'] ?? null,
        ]);

        $output .= Select::get([
            'name' => 'button_position',
            'label' => __('Button Alignment'),
            'options' => [
                'start' => __('Left'),
                'center' => __('Center'),
                'end' => __('Right'),
            ],
            'value' => $widget_saved_values['button_position'] ?? null,
        ]);

        $output .= ColorPicker::get([
            'name' => 'button_color',
            'label' => __('Button Color'),
            'value' => $widget_saved_values['button_color'] ?? null,
            'info' => '<div class="text-primary mt-1">' . __('Keep empty if you want to keep theme default colors') . '</div>'
        ]);

        // add padding option
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    // This function will render the addon on frontend, you can get the inputted values passed from the admin_render function
    public function frontend_render()
    {
        $section_title = esc_html($this->setting_item('section_title')) ?? '';
        $section_title_position = esc_html($this->setting_item('section_title_position')) ?? '';
        $label_title = esc_html($this->setting_item('label_title')) ?? '';
        $input_placeholder = esc_html($this->setting_item('input_placeholder')) ?? '';
        $button_text = esc_html($this->setting_item('button_text')) ?? '';
        $button_position = esc_html($this->setting_item('button_position')) ?? '';
        $button_color = esc_html($this->setting_item('button_color')) ?? '';

        $padding_top = esc_html($this->setting_item('padding_top'));
        $padding_bottom = esc_html($this->setting_item('padding_bottom'));

        // readable values must be pass via an array
        $data = [
            'section_title'=> $section_title,
            'section_title_position'=> $section_title_position,
            'label_title'=> $label_title,
            'input_placeholder'=> $input_placeholder,
            'button_text'=> $button_text,
            'button_position'=> $button_position,
            'button_color'=> $button_color,
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
        ];

        // self::renderView function will render the view file, this function will take three parameter, your view file name, passed array, module name
        return self::renderView('shipping-tracker', $data, 'ShippingPlugin');
    }

    // Only tenant will get the addon if you use this function, otherwise landlord will also able to use the same addon
    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    // This function sets the addon name
    public function addon_title()
    {
        return __("Shipping Tracker");
    }
}
