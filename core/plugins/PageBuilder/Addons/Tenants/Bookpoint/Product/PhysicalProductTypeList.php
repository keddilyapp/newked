<?php

namespace Plugins\PageBuilder\Addons\Tenants\Bookpoint\Product;

use App\Enums\StatusEnums;
use App\Helpers\SanitizeInput;
use Modules\Attributes\Entities\Category;
use Modules\DigitalProduct\Entities\DigitalCategories;
use Modules\DigitalProduct\Entities\DigitalProduct;
use Modules\DigitalProduct\Entities\DigitalProductCategories;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductCategory;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class PhysicalProductTypeList extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/themes/hexfashion/home/product_type_list.jpg';
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

        $categories = Category::where(['status_id' => StatusEnums::PUBLISH])->get()->mapWithKeys(function ($item){
            return [$item->id => $item->name];
        })->toArray();

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
                'regular_price' => 'Price'
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
            'name' => 'view_all_text',
            'label' => __('View All Text'),
            'value' => $widget_saved_values['view_all_text'] ?? 'View All',
            'info' => 'Place your view all button text'
        ]);

        $output .= Text::get([
            'name' => 'view_all_url',
            'label' => __('View All URL'),
            'value' => $widget_saved_values['view_all_url'] ?? '#',
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
        $title = SanitizeInput::esc_html($this->setting_item('title') ?? '');
        $item_show = SanitizeInput::esc_html($this->setting_item('item_show') ?? '');
        $view_all_text = SanitizeInput::esc_html($this->setting_item('view_all_text') ?? '');
        $view_all_url = SanitizeInput::esc_html($this->setting_item('view_all_url') ?? '');
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        $sort_by = SanitizeInput::esc_html($this->setting_item('sort_by') ?? 'id');
        $sort_to = SanitizeInput::esc_html($this->setting_item('sort_to') ?? 'desc');

        $categories = Category::where('status_id',StatusEnums::PUBLISH);
        $products = Product::where('status_id', StatusEnums::PUBLISH);

        if (!empty($categories_id))
        {
            $categories = $categories->whereIn('id', $categories_id);
            $products_id = ProductCategory::whereIn('category_id', $categories_id)->pluck('product_id')->toArray();
            $products->whereIn('id', $products_id);
        }

        $categories = $categories->select('id', 'name', 'slug')->get();
        $products->orderBy($sort_by, $sort_to);

        if(!empty($item_show)){
            $products->take($item_show);
        }else{
            $products->take(6);
        }

        $products = $products->withSum('taxOptions', 'rate')->get();

        $data = [
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'title' => $title,
            'view_all_text' => $view_all_text,
            'view_all_url' => $view_all_url,
            'categories' => $categories,
            'products' => $products,
            'product_limit' => $item_show ?? 6,
            'sort_by' => $sort_by,
            'sort_to' => $sort_to
        ];

        return self::renderView('tenant.bookpoint.product.physical_product_type_list', $data);
    }

    public function addon_title()
    {
        return __('Theme BookPoint: Product Type List (Normal Product)');
    }
}
