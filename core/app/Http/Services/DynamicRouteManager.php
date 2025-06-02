<?php

namespace App\Http\Services;

use App\Enums\SlugMorphableTypeEnum;
use App\Models\Page;
use App\Models\PricePlan;
use App\Models\Slug;
use App\Models\StaticOption;
use App\Traits\SeoDataConfig;
use Artesaos\SEOTools\Traits\SEOTools;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Attributes\Entities\Category;
use Modules\Attributes\Entities\ChildCategory;
use Modules\Attributes\Entities\Color;
use Modules\Attributes\Entities\Size;
use Modules\Attributes\Entities\SubCategory;
use Modules\Blog\Entities\Blog;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Entities\BlogComment;
use Modules\Blog\Entities\BlogTag;
use Modules\DigitalProduct\Entities\AdditionalField;
use Modules\DigitalProduct\Entities\DigitalAuthor;
use Modules\DigitalProduct\Entities\DigitalCategories;
use Modules\DigitalProduct\Entities\DigitalChildCategories;
use Modules\DigitalProduct\Entities\DigitalLanguage;
use Modules\DigitalProduct\Entities\DigitalProduct;
use Modules\DigitalProduct\Entities\DigitalProductCategories;
use Modules\DigitalProduct\Entities\DigitalProductChildCategories;
use Modules\DigitalProduct\Entities\DigitalProductReviews;
use Modules\DigitalProduct\Entities\DigitalProductSubCategories;
use Modules\DigitalProduct\Entities\DigitalProductTags;
use Modules\DigitalProduct\Entities\DigitalSubCategories;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductCategory;
use Modules\Product\Entities\ProductTag;
use Modules\Product\Entities\ProductUom;

class DynamicRouteManager
{
    use SeoDataConfig, SEOTools;

    public static function handle($slug)
    {
        $slugData = Slug::where('slug', $slug)->first();

        if ($slugData) {
            $type = $slugData->morphable_type;

            if (SlugMorphableTypeEnum::PAGE->value === $type)
            {
                return self::handlePage($slug);
            }
            elseif (SlugMorphableTypeEnum::BLOG->value === $type)
            {
                return self::handleBlog($slug);
            }
            elseif (SlugMorphableTypeEnum::BLOG_CATEGORY->value === $type)
            {
                return self::handleBlogCategories($slug);
            }
            elseif (SlugMorphableTypeEnum::BLOG_TAG->value === $type)
            {
                return self::handleBlogTags($slug);
            }
            elseif (in_array(
                $type, [
                    SlugMorphableTypeEnum::PRODUCT_CATEGORY->value,
                    SlugMorphableTypeEnum::PRODUCT_SUBCATEGORY->value,
                    SlugMorphableTypeEnum::PRODUCT_CHILDCATEGORY->value
                ]
            ))
            {
                return self::handleProductCategories($slug);
            }
            elseif (SlugMorphableTypeEnum::PRODUCT->value === $type)
            {
                return self::handleProduct($slug);
            }
            elseif (in_array(
                $type, [
                    SlugMorphableTypeEnum::PRODUCT_DIGITAL_CATEGORY->value,
                    SlugMorphableTypeEnum::PRODUCT_DIGITAL_SUBCATEGORY->value,
                    SlugMorphableTypeEnum::PRODUCT_DIGITAL_CHILDCATEGORY->value
                ]
            ))
            {
                return self::handleDigitalProductCategories($slug);
            }
            elseif (SlugMorphableTypeEnum::PRODUCT_DIGITAL_AUTHOR->value === $type)
            {
                return self::handleDigitalProductAuthor($slug);
            }
            elseif (SlugMorphableTypeEnum::PRODUCT_DIGITAL_TAG->value === $type)
            {
                return self::handleDigitalProductTag($slug);
            }
            elseif (SlugMorphableTypeEnum::PRODUCT_DIGITAL_LANGUAGE->value === $type)
            {
                return self::handleDigitalProductLanguage($slug);
            }
            elseif (SlugMorphableTypeEnum::PRODUCT_DIGITAL_PRODUCT->value === $type)
            {
                return self::handleDigitalProduct($slug);
            }
        }

        abort(404);
    }

    public static function handlePage($slug)
    {
        if (tenant())
        {
            return self::handleTenantPage($slug);
        }
        else
        {
            return self::handleLandlordPage($slug);
        }
    }

    private static function handleLandlordPage($slug)
    {
        $page_post = Page::where('slug', $slug)->firstOrFail();

        self::staticSetMetaDataInfo($page_post);

        $price_page_slug = get_page_slug(get_static_option('pricing-plan'), 'price-plan');
        if ($slug === $price_page_slug) {
            $all_blogs = PricePlan::where(['status' => 'publish'])->paginate(10);
            return view('landlord.frontend.pages.dynamic-single')->with([
                'all_blogs' => $all_blogs,
                'page_post' => $page_post
            ]);
        }

        return view('landlord.frontend.pages.dynamic-single')->with([
            'page_post' => $page_post
        ]);
    }

    private static function handleTenantPage($slug)
    {
        $page_post = Page::where('slug', $slug)->first();

        $blog_page_slug = get_page_slug(get_static_option('blog_page'), 'blog_page');
        if ($slug === $blog_page_slug) {
            if (tenant()) {
                $sorting = blog_sorting(request());
                $order_by = $sorting['order_by'];
                $order = $sorting['order'];
                $order_type = $sorting['order_type'];

                $blogs = Blog::where('status', 1)->orderBy($order_by, $order)->paginate(get_static_option('blog_page_item_show') ?? 9);

                return view('blog::tenant.frontend.blog.blog-all')->with([
                    'page_post' => $page_post,
                    'blogs' => $blogs,
                    'order_type' => $order_type
                ]);
            }
        }

        $shop_page_slug = get_page_slug(get_static_option('shop_page'), 'shop_page');
        if ($slug === $shop_page_slug) {
            if (tenant()) {
                $product_object = Product::where('status_id', 1)->latest()->withSum('taxOptions', 'rate')->paginate(12);
                $categories = Category::whereHas('product', function ($query) {
                    $query->where('status_id', 1);
                })->select('id', 'name', 'slug')->withCount('product')->get();
                $sizes = Size::whereHas('product_sizes')->select('id', 'name', 'size_code', 'slug')->get();
                $colors = Color::select('id', 'name', 'color_code', 'slug')->get();
                $tags = ProductTag::whereHas('product')->select('tag_name')->distinct()->get();

                $create_arr = request()->all();
                $create_url = http_build_query($create_arr);

                $product_object->url(route('tenant.shop') . '?' . $create_url);
                $product_object->url(route('tenant.shop') . '?' . $create_url);

                $links = $product_object->getUrlRange(1, $product_object->lastPage());
                $current_page = $product_object->currentPage();

                $products = $product_object->items();

                $pagination = $product_object->withQueryString();
                return themeView('shop.all-products', compact(
                    'page_post',
                    'products',
                    'links',
                    'current_page',
                    'pagination',
                    'categories',
                    'sizes',
                    'colors',
                    'tags'
                ));
            }
        }

        $digital_shop_page_slug = get_page_slug(get_static_option('digital_shop_page'), 'digital_shop_page');
        if (tenant_has_digital_product() && $slug === $digital_shop_page_slug) {
            if (tenant()) {
                $product_object = DigitalProduct::where('status_id', 1)->latest()->paginate(12);
                $categories = DigitalCategories::whereHas('product', function ($query) {
                    $query->where('status_id', 1);
                })->select('id', 'name', 'slug')->withCount('product')->get();
                $authors = DigitalAuthor::where('status', 1)->get();
                $languages = DigitalLanguage::where('status', 1)->get();
                $tags = DigitalProductTags::select('tag_name')->distinct()->get();

                $create_arr = request()->all();
                $create_url = http_build_query($create_arr);

                $product_object->url(route('tenant.digital.shop') . '?' . $create_url);
                $product_object->url(route('tenant.digital.shop') . '?' . $create_url);

                $links = $product_object->getUrlRange(1, $product_object->lastPage());
                $current_page = $product_object->currentPage();

                $products = $product_object->items();

                $pagination = $product_object->withQueryString();
                return themeView('digital-shop.all-products', compact(
                    'page_post',
                    'products',
                    'links',
                    'current_page',
                    'pagination',
                    'categories',
                    'tags',
                    'languages',
                    'authors'
                ));
            }
        }

        $track_page_slug = get_page_slug(get_static_option('track_order'), 'track_order');
        if ($slug === $track_page_slug) {
            if (tenant()) {
                return themeView('shop.track-order');
            }
        }

        self::staticSetMetaDataInfo($page_post);

        return themeView('pages.dynamic-single')->with([
            'page_post' => $page_post
        ]);
    }

    public static function handleBlog($slug)
    {
        $blog_post = Blog::with(['user', 'category', 'comments'])->where(['slug' => $slug, 'status' => 1])->firstOrFail();
        $blog_comments = BlogComment::where(['blog_id' => $blog_post->id, 'parent_id' => null])->orderByDesc('created_at')->take(3)->get();
        $blog_comments_count = BlogComment::where(['blog_id' => $blog_post->id, 'parent_id' => null])->count();

        $all_category = BlogCategory::withCount('blogs')->has('blogs')->get();
        $all_tags = BlogTag::orderByDesc('created_at')->select('id','title','slug')->take(15)->get();

        self::staticSetMetaDataInfo($blog_post);

        return view('blog::tenant.frontend.blog.blog-single', compact('blog_post', 'blog_comments', 'blog_comments_count', 'all_category', 'all_tags'));
    }

    public static function handleBlogCategories($slug)
    {
        $sorting = blog_sorting(request());
        $order_by = $sorting['order_by'];
        $order = $sorting['order'];
        $order_type = $sorting['order_type'];

        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $blogs = Blog::where(['category_id' => $category->id, 'status' => 1])->orderBy($order_by, $order)->paginate(get_static_option('category_page_item_show') ?? 9);
        $category_name = $category->title;

        return view('blog::tenant.frontend.blog.blog-category')->with([
            'blogs' => $blogs,
            'category_name' => $category_name,
            'order_type' => $order_type,
        ]);
    }

    public static function handleBlogTags($slug)
    {
        $sorting = blog_sorting(request());
        $order_by = $sorting['order_by'];
        $order = $sorting['order'];
        $order_type = $sorting['order_type'];

        $all_blogs = Blog::Where('tags', 'LIKE', '%' . $slug . '%')
            ->orderBy($order_by, $order)->paginate(get_static_option('blog_tag_item_show') ?? 9);

        return view('blog::tenant.frontend.blog.blog-category')->with([
            'blogs' => $all_blogs,
            'category_name' => ucfirst($slug),
            'order_type' => $order_type
        ]);
    }

    public static function handleProductCategories($slug)
    {
//        $categoryQuery = Category::select('id', 'slug', 'name', DB::raw("'Category' as type"))
//            ->where('slug', $slug);
//
//        $subcategoryQuery = SubCategory::select('id', 'slug', 'name', DB::raw("'SubCategory' as type"))
//            ->where('slug', $slug)
//            ->union($categoryQuery);
//
//        $childCategoryQuery = ChildCategory::select('id', 'slug', 'name', DB::raw("'ChildCategory' as type"))
//            ->where('slug', $slug)
//            ->union($subcategoryQuery);
//
//        $queryResult = $childCategoryQuery->first() ?? abort(404);

//        $type = $queryResult->type;

        $category = Category::select('id', 'slug', 'name', DB::raw("'Category' as type"))
            ->where('slug', $slug)
            ->first();

        if ($category) {
            $queryResult = $category;
        } else {
            // Try to find the record in the SubCategory model
            $subCategory = SubCategory::select('id', 'slug', 'name', DB::raw("'SubCategory' as type"))
                ->where('slug', $slug)
                ->first();

            if ($subCategory) {
                $queryResult = $subCategory;
            } else {
                // Try to find the record in the ChildCategory model
                $childCategory = ChildCategory::select('id', 'slug', 'name', DB::raw("'ChildCategory' as type"))
                    ->where('slug', $slug)
                    ->first();

                if ($childCategory) {
                    $queryResult = $childCategory;
                } else {
                    // If no record was found in any of the models, abort with a 404 error
                    abort(404);
                }
            }
        }

        $type = $queryResult->type;

        $model_name = "Product" . ucfirst(Str::camel($type));
        $model_name_space = "Modules\Product\Entities\\$model_name";
        $resolved_model = resolve($model_name_space);

        $target_column = match (strtolower($type)) {
            'category' => 'category_id',
            'subcategory' => 'sub_category_id',
            'childcategory' => 'child_category_id',
        };

        $products_id = $resolved_model::where($target_column, $queryResult->id)->select('product_id')->pluck('product_id');
        $products = Product::whereIn('id', $products_id)->paginate(12);

        abort_if(empty($products), 403);

        return themeView('shop.single_pages.category', ['category' => $queryResult, 'products' => $products]);
    }

    public static function handleProduct($slug)
    {
        extract(self::productVariant($slug));

        // related products
        $product_category = $product?->category?->id;
        $product_id = $product->id;
        $related_products = Product::where('status_id', 1)
            ->whereIn('id', function ($query) use ($product_id, $product_category) {
                $query->select('product_categories.product_id')
                    ->from(with(new ProductCategory())->getTable())
                    ->where('product_id', '!=', $product_id)
                    ->where('category_id', '=', $product_category)
                    ->get();
            })
            ->withSum('taxOptions', 'rate')
            ->inRandomOrder()
            ->take(5)
            ->get();

        // sidebar data
        $all_category = ProductCategory::all();
        $all_units = ProductUom::all();
        $maximum_available_price = Product::query()->with('category')->max('price');
        $min_price = request()->pr_min ? request()->pr_min : Product::query()->min('price');
        $max_price = request()->pr_max ? request()->pr_max : $maximum_available_price;
        $all_tags = ProductTag::all();
        // todo:: now check product inventory set

        return themeView('shop.product_details.product-details', compact(
            'product',
            'related_products',
            'available_attributes',
            'product_inventory_set',
            'additional_info_store',
            'all_category',
            'all_units',
            'maximum_available_price',
            'min_price',
            'max_price',
            'all_tags',
            'productColors',
            'productSizes',
            'setting_text',
        ));
    }

    public static function productVariant($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(
                'category',
                'tag',
                'color',
                'sizes',
                'inventoryDetail',
                'inventoryDetail.productColor',
                'inventoryDetail.productSize',
                'inventoryDetail.attribute',
                'reviews'
            )
            ->where("status_id", 1)
            ->withSum('taxOptions', 'rate')
            ->firstOrFail();

        // get selected attributes in this product ( $available_attributes )
        $inventoryDetails = optional($product->inventoryDetail);
        $product_inventory_attributes = $inventoryDetails->toArray();

        $all_included_attributes = array_filter(array_column($product_inventory_attributes, 'attribute', 'id'));
        $all_included_attributes_prd_id = array_keys($all_included_attributes);

        $available_attributes = [];  // FRONTEND : All displaying attributes
        $product_inventory_set = []; // FRONTEND : attribute store
        $additional_info_store = []; // FRONTEND : $additional info_store

        foreach ($all_included_attributes as $id => $included_attributes) {
            $single_inventory_item = [];
            foreach ($included_attributes as $included_attribute_single) {
                $available_attributes[$included_attribute_single['attribute_name']][$included_attribute_single['attribute_value']] = 1;

                // individual inventory item
                $single_inventory_item[$included_attribute_single['attribute_name']] = $included_attribute_single['attribute_value'];


                if (!empty(optional($inventoryDetails->find($id))->productColor)) {
                    $single_inventory_item['Color'] = optional(optional($inventoryDetails->find($id))->productColor)->name;
                }

                if (!empty(optional($inventoryDetails->find($id))->productSize)) {
                    $single_inventory_item['Size'] = optional(optional($inventoryDetails->find($id))->productSize)->name;
                }
            }

            $item_additional_price = optional(optional($product->inventoryDetail)->find($id))->additional_price ?? 0;
            $item_additional_stock = optional(optional($product->inventoryDetail)->find($id))->stock_count ?? 0;
            $image = get_attachment_image_by_id(optional(optional($product->inventoryDetail)->find($id))->image)['img_url'] ?? '';

            $product_inventory_set[] = $single_inventory_item;

            $sorted_inventory_item = $single_inventory_item;
            ksort($sorted_inventory_item);

            $additional_info_store[md5(json_encode($sorted_inventory_item))] = [
                'pid_id' => $id, //Info: ProductInventoryDetails id
                'additional_price' => calculatePrice($item_additional_price, $product),
                'stock_count' => $item_additional_stock,
                'image' => $image,
            ];
        }

        $productColors = $product->color->unique();
        $productSizes = $product->sizes->unique();

        $colorAndSizes = $product?->inventoryDetail?->whereNotIn("id", $all_included_attributes_prd_id);

        if (!empty($colorAndSizes)) {
            foreach ($colorAndSizes as $inventory) {
                $product_id = $inventory['id'] ?? $product->id;

                // if this inventory has attributes then it will fire continue statement
                if (in_array($inventory->product_id, $all_included_attributes_prd_id)) {
                    continue;
                }

                $single_inventory_item = [];

                if (!empty(optional($inventoryDetails->find($product_id))->color)) {
                    // todo:: this line will check if color name is exist then store it on $singleInventoryItem['Color'] array
                    optional($inventory->productColor)->name ? $single_inventory_item['Color'] = optional($inventory->productColor)->name : null;
                }

                if (!empty(optional($inventoryDetails->find($product_id))->size)) {
                    // todo:: this line will check if size name is exist then store it on $singleInventoryItem['Size'] array
                    optional($inventory->productSize)->name ? $single_inventory_item['Size'] = optional($inventory->productSize)->name : null;
                }

                $product_inventory_set[] = $single_inventory_item;

                $item_additional_price = optional($inventory)->additional_price ?? 0;
                $item_additional_stock = optional($inventory)->stock_count ?? 0;
                $image = get_attachment_image_by_id(optional($inventory)->image)['img_url'] ?? '';

                $sorted_inventory_item = $single_inventory_item;
                ksort($sorted_inventory_item);

                $additional_info_store[md5(json_encode($sorted_inventory_item))] = [
                    'pid_id' => $product_id,
                    'additional_price' => calculatePrice($item_additional_price, $product),
                    'stock_count' => $item_additional_stock,
                    'image' => $image,
                ];
            }
        }

        $available_attributes = array_map(fn($i) => array_keys($i), $available_attributes);


        $setting_text = StaticOption::whereIn('option_name', [
            'product_in_stock_text',
            'product_out_of_stock_text',
            'details_tab_text',
            'additional_information_text',
            'reviews_text',
            'your_reviews_text',
            'write_your_feedback_text',
            'post_your_feedback_text',
        ])->get()->mapWithKeys(fn($item) => [$item->option_name => $item->option_value])->toArray();

        return [
            "available_attributes" => $available_attributes,
            "product_inventory_set" => $product_inventory_set,
            "additional_info_store" => $additional_info_store,
            "productColors" => $productColors,
            "productSizes" => $productSizes,
            "product" => $product,
            "setting_text" => $setting_text,
        ];
    }

    public static function handleDigitalProductCategories($slug)
    {
//        $categoryQuery = DigitalCategories::select('id', 'slug', 'name', DB::raw("'DigitalProductCategories' as type"))
//            ->where('slug', $slug);
//
//        $subcategoryQuery = DigitalSubCategories::select('id', 'slug', 'name', DB::raw("'DigitalProductSubCategories' as type"))
//            ->where('slug', $slug)
//            ->union($categoryQuery);
//
//        $childCategoryQuery = DigitalChildCategories::select('id', 'slug', 'name', DB::raw("'DigitalProductChildCategories' as type"))
//            ->where('slug', $slug)
//            ->union($subcategoryQuery);
//
//        $queryResult = $childCategoryQuery->first() ?? abort(404);

        $category = DigitalCategories::select('id', 'slug', 'name', DB::raw("'DigitalProductCategories' as type"))
            ->where('slug', $slug)
            ->first();

        if ($category) {
            $queryResult = $category;
        } else {
            // Try to find the record in the DigitalSubCategories model
            $subCategory = DigitalSubCategories::select('id', 'slug', 'name', DB::raw("'DigitalProductSubCategories' as type"))
                ->where('slug', $slug)
                ->first();

            if ($subCategory) {
                $queryResult = $subCategory;
            } else {
                // Try to find the record in the DigitalChildCategories model
                $childCategory = DigitalChildCategories::select('id', 'slug', 'name', DB::raw("'DigitalProductChildCategories' as type"))
                    ->where('slug', $slug)
                    ->first();

                if ($childCategory) {
                    $queryResult = $childCategory;
                } else {
                    // If no record was found in any of the models, abort with a 404 error
                    abort(404);
                }
            }
        }

        $type = $queryResult->type;

        $model_name = ucfirst(Str::camel($type));
        $model_name_space = "Modules\DigitalProduct\Entities\\$model_name";
        $resolved_model = resolve($model_name_space);

        $target_column = match (strtolower($type)) {
            'digitalproductcategories' => 'category_id',
            'digitalproductsubcategories' => 'sub_category_id',
            'digitalproductchildcategories' => 'child_category_id',
        };

        $products_id = $resolved_model::where($target_column, $queryResult->id)->select('product_id')->pluck('product_id');
        $products = DigitalProduct::whereIn('id', $products_id)->paginate(12);

        abort_if(empty($products), 403);

        return themeView('digital-shop.single_pages.category', [
                'category' => $queryResult,
                'products' => $products,
                'type' => trim(str_replace(['_id', '_'], ' ', $target_column))
            ]
        );
    }

    public static function handleDigitalProductAuthor($slug)
    {
        $category = DigitalAuthor::where('slug', $slug)->select('id', 'name')->first();
        $products_id = AdditionalField::where('author_id', $category->id)->select('product_id')->pluck('product_id');

        $products = DigitalProduct::whereIn('id', $products_id)->paginate(12);

        abort_if(empty($products), 403);

        return themeView('digital-shop.single_pages.category', ['category' => $category, 'products' => $products, 'type' => 'Author']);
    }

    public static function handleDigitalProductTag($slug)
    {
        $products_id = DigitalProductTags::where('tag_name', $slug)->select('product_id')->pluck('product_id');
        $products = DigitalProduct::whereIn('id', $products_id)->paginate(12);

        abort_if(empty($products), 403);

        return themeView('digital-shop.single_pages.category', ['category' => (object) ['name' => $slug] ,'products' => $products, 'type' => 'Tags']);
    }

    public static function handleDigitalProductLanguage($slug)
    {
        $category = DigitalLanguage::where('slug', $slug)->select('id', 'name')->firstOrFail();
        $products_id = AdditionalField::where('language', $category->id)->select('product_id')->pluck('product_id');

        $products = DigitalProduct::whereIn('id', $products_id)->paginate(12);
        abort_if(empty($products), 403);

        return themeView('digital-shop.single_pages.category', ['category' => $category ,'products' => $products, 'type' => 'Languages']);
    }

    public static function handleDigitalProduct($slug)
    {
        if (! tenant_has_digital_product()) {
            abort(404);
        }

        $product = DigitalProduct::with('category', 'tag', 'tax', 'additionalFields', 'additionalCustomFields', 'gallery_images', 'refund_policy', 'downloads')
            ->withCount('downloads')
            ->where('slug', $slug)
            ->where('status_id', 1)
            ->firstOrFail();

        // related products
        $product_category = $product?->category?->id;
        $product_id = $product->id;
        $related_products = DigitalProduct::where('status_id', 1)
            ->whereIn('id', function ($query) use ($product_id, $product_category) {
                $query->select('digital_product_categories.product_id')
                    ->from(with(new DigitalProductCategories())->getTable())
                    ->where('product_id', '!=', $product_id)
                    ->where('category_id', '=', $product_category)
                    ->get();
            })
            ->inRandomOrder()
            ->take(3)
            ->get();

        $reviews = DigitalProductReviews::where('product_id', $product->id)->orderBy('id', 'desc')->take(5)->get();

        return themeView('digital-shop.product_details.product-details', compact(
            'product',
            'related_products',
            'reviews'
        ));
    }
}
