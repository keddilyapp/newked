<?php

namespace Plugins\PageBuilder\Addons\Tenants\Electro\Product;

use App\Enums\StatusEnums;
use App\Helpers\SanitizeInput;
use Modules\Attributes\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductCategory;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class ProductTypeList extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/themes/electro/home/product_type_list.jpg';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Section Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);

        $categories = [];
        Category::published()->whereHas('product')->chunk(50, function ($chunked_categories) use (&$categories) {
            foreach ($chunked_categories as $category)
            {
                $categories[$category->id] = $category->name;
            }
        });

        $output .= NiceSelect::get([
            'multiple' => true,
            'name' => 'categories',
            'label' => __('Select Categories'),
            'options' => $categories,
            'value' => $widget_saved_values['categories'] ?? null,
            'info' => __('you can select your desired product categories or leave it empty')
        ]);

        $output .= Number::get([
            'name' => 'item_show',
            'label' => __('Product Show'),
            'value' => $widget_saved_values['item_show'] ?? null,
            'info' => 'How many products will be shown under the selected category'
        ]);

        $output .= Select::get([
            'name' => 'sort_by',
            'label' => __('Product Sort By'),
            'options' => [
                'id' => 'ID',
                'created_at' => 'Created Date',
                'sale_price' => 'Price'
            ],
            'value' => $widget_saved_values['sort_by'] ?? null,
        ]);

        $output .= Select::get([
            'name' => 'sort_to',
            'label' => __('Product Sort To'),
            'options' => [
                'desc' => 'Descending',
                'asc' => 'Ascending'
            ],
            'value' => $widget_saved_values['sort_to'] ?? null,
        ]);

        $output .= Text::get([
            'name' => 'view_all_button_text',
            'label' => __('View All Button Text'),
            'value' => $widget_saved_values['view_all_button_text'] ?? '',
        ]);

        $output .= Text::get([
            'name' => 'view_all_button_url',
            'label' => __('View All Button URL'),
            'value' => $widget_saved_values['view_all_button_url'] ?? '#',
            'info' => 'Copy and page any page link here'
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
        $categories_id = $this->setting_item('categories');
        $title = esc_html($this->setting_item('title') ?? '');
        $view_all_button_text = esc_html($this->setting_item('view_all_button_text') ?? '');
        $view_all_button_url = esc_html($this->setting_item('view_all_button_url') ?? '#');
        $item_show = esc_html($this->setting_item('item_show') ?? '');

        $sort_by = esc_html($this->setting_item('sort_by') ?? 'id');
        $sort_to = esc_html($this->setting_item('sort_to') ?? 'desc');

        $padding_top = esc_html($this->setting_item('padding_top'));
        $padding_bottom = esc_html($this->setting_item('padding_bottom'));

        $categories = Category::published();
        $products = Product::with('badge')->published();

        if (!empty($categories_id))
        {
            $categories = $categories->whereIn('id', $categories_id)->select('id', 'name', 'slug')->get();
            $products_id = ProductCategory::whereIn('category_id', $categories_id)->pluck('product_id')->toArray();
            $products->whereIn('id', $products_id);
        }

        $products = $products->orderBy($sort_by, $sort_to)->select('id', 'name', 'slug', 'price', 'sale_price', 'badge_id', 'image_id')->withSum('taxOptions', 'rate')->take($item_show ?? 6)->get();

        $data = [
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'title' => $title,
            'view_all_button_text' => __($view_all_button_text),
            'view_all_button_url' => $view_all_button_url,
            'categories'=> $categories,
            'products'=> $products,
            'product_limit' => $item_show ?? 6,
            'sort_by' => $sort_by,
            'sort_to' => $sort_to
        ];

        return self::renderView('tenant.electro.product.product-type-list', $data);
    }

    public function addon_title()
    {
        return __('Electro: Product Type List');
    }
}
