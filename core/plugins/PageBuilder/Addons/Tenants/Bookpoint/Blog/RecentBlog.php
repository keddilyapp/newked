<?php

namespace Plugins\PageBuilder\Addons\Tenants\Bookpoint\Blog;

use App\Helpers\SanitizeInput;
use Modules\Blog\Entities\Blog;
use Modules\Blog\Entities\BlogCategory;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class RecentBlog extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/themes/bookpoint/home/recent_blogs.jpg';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        $categories = BlogCategory::where(['status' => 1])->get()->mapWithKeys(function ($item){
            return [$item->id => $item->title];
        })->toArray();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);

        $output .= NiceSelect::get([
            'multiple' => true,
            'name' => 'categories',
            'label' => __('Select Category'),
            'options' => $categories,
            'value' => $widget_saved_values['categories'] ?? null,
            'info' => __('you can select your desired blog categories or leave it empty')
        ]);

        $output .= Select::get([
            'name' => 'order_by',
            'label' => __('Order By'),
            'options' => [
                'id' => __('ID'),
                'created_at' => __('Date'),
            ],
            'value' => $widget_saved_values['order_by'] ?? null,
            'info' => __('set order by')
        ]);
        $output .= Select::get([
            'name' => 'order',
            'label' => __('Order'),
            'options' => [
                'asc' => __('Accessing'),
                'desc' => __('Decreasing'),
            ],
            'value' => $widget_saved_values['order'] ?? null,
            'info' => __('set order')
        ]);
        $output .= Number::get([
            'name' => 'items',
            'label' => __('Items'),
            'value' => $widget_saved_values['items'] ?? null,
            'info' => __('enter how many item you want to show in frontend'),
        ]);

        $output .= Text::get([
            'name' => 'button_text',
            'label' => __('Button Text'),
            'value' => $widget_saved_values['button_text'] ?? null,
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
        $category = $this->setting_item('categories');
        $items = SanitizeInput::esc_html($this->setting_item('items'));
        $title = SanitizeInput::esc_html($this->setting_item('title'));
        $button_text = SanitizeInput::esc_html($this->setting_item('button_text'));
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));


        $order_by = !empty(SanitizeInput::esc_html($this->setting_item('order_by'))) ? SanitizeInput::esc_html($this->setting_item('order_by')) : 'id';
        $order = !empty(SanitizeInput::esc_html($this->setting_item('order'))) ? SanitizeInput::esc_html($this->setting_item('order')) : 'asc';

        $blogs = Blog::where('status', 1);

        if(!empty($category)) {
            $blogs->whereIn('category_id',$category);
        }

        if (!empty($items)) {
            $blogs =  $blogs->orderBy($order_by,$order)->take($items)->get();
        } else {
            $blogs =  $blogs->orderBy($order_by,$order)->take(4)->get();
        }

        $data = [
            'title'=> $title,
            'blogs'=> $blogs,
            'button_text'=> $button_text,
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
        ];

        return self::renderView('tenant.bookpoint.blog.blog-one',$data);
    }

    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    public function addon_title()
    {
        return __('Theme Bookpoint: Recent Blogs(01)');
    }
}
