<?php

namespace Modules\Attributes\Http\Controllers;

use App\Enums\SlugMorphableTypeEnum;
use App\Helpers\FlashMsg;
use App\Helpers\SanitizeInput;
use App\Http\Services\DynamicCustomSlugValidation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Attributes\Entities\Category;
use Modules\Attributes\Entities\SubCategory;
use Modules\Attributes\Http\Requests\StoreSubCategoryRequest;
use Modules\Attributes\Http\Requests\UpdateCategoryRequest;
use Modules\Attributes\Http\Requests\UpdateSubCategoryRequest;

class SubCategoryController extends Controller
{
    private const BASE_PATH = 'attributes::backend.';

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:product-sub-category-list|product-category-create|product-category-edit|product-category-delete', ['only', ['index']]);
        $this->middleware('permission:product-sub-category-create', ['only', ['store']]);
        $this->middleware('permission:product-sub-category-edit', ['only', ['update']]);
        $this->middleware('permission:product-sub-category-delete', ['only', ['destroy', 'bulk_action']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        $data = [];
        $data['all_category'] = Category::with(["image:id,path","status"])
            ->where('status_id', 1)
            ->get();

        $data['all_sub_category'] = SubCategory::with(["image:id,path","status","category:id,name"])
            ->where('status_id', 1)
            ->get();

        return view(self::BASE_PATH.'sub-category.all', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSubCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSubCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DynamicCustomSlugValidation::validate(
            slug: $data['slug']
        );

        $data['name'] = SanitizeInput::esc_html($data['name']);
        $data['description'] = SanitizeInput::esc_html($data['description']);

        $sluggable_text = Str::slug($data['slug'] ?? $data['name'], '-', null);
        $slug = create_slug($sluggable_text, model_name: 'Slug');
        $data['slug'] = $slug;

        $product_category = SubCategory::create($data);
        $product_category->slug()->create(['slug' => $product_category->slug]);

        return $product_category
            ? back()->with(FlashMsg::create_succeed(__('Product Sub Category')))
            : back()->with(FlashMsg::create_failed(__('Product Sub Category')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSubCategoryRequest $request
     * @return RedirectResponse
     */
    public function update(UpdateSubCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DynamicCustomSlugValidation::validate(
            slug: $data['slug'] ?? Str::slug($data['name'], '-', null),
            id: $data['id'],
            type: SlugMorphableTypeEnum::PRODUCT_SUBCATEGORY
        );

        $data['name'] = SanitizeInput::esc_html($data['name']);
        $data['description'] = SanitizeInput::esc_html($data['description']);

        $subcategory = SubCategory::findOrFail($request->id);
        if ($subcategory->slug != $data['slug'])
        {
            $sluggable_text = Str::slug($data['slug'] ?? $data['name'], '-', null);
            $new_slug = create_slug($sluggable_text, 'Slug');
            $data['slug'] = $new_slug;
        }

        $updated = $subcategory->update($data);
        $subcategory->slug()->update(['slug' => $subcategory->slug]);

        return $updated
            ? back()->with(FlashMsg::update_succeed(__('Product Sub Category')))
            : back()->with(FlashMsg::update_failed(__('Product Sub Category')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SubCategory $item
     * @return JsonResponse
     */
    public function destroy(SubCategory $item): JsonResponse
    {
        return response()->json([
            'success' => (bool) $item->delete(),
        ]);
    }

    public function bulk_action(Request $request): JsonResponse
    {
        SubCategory::WhereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }

    public function getSubcategoriesForSelect(Request $request)
    {
        $sub_category = SubCategory::where('status_id', 1)->where("category_id",$request->category_id)->get();
        $options = view(self::BASE_PATH."sub-category.sub-category-option", compact("sub_category"))->render();
        $lists = view(self::BASE_PATH."sub-category.sub_category-list", compact("sub_category"))->render();

        return response()->json(["option" => $options,"list" => $lists]);
    }


    public function trash(): View
    {
        $all_category = SubCategory::onlyTrashed()->get();
        return view(self::BASE_PATH.'sub-category.trash')->with(['all_subcategory' => $all_category]);
    }

    public function trash_restore($id)
    {
        $restored = SubCategory::onlyTrashed()->findOrFail($id)->restore();

        return $restored
            ? back()->with(FlashMsg::restore_succeed(__('Product Sub Category')))
            : back()->with(FlashMsg::restore_failed(__('Product Sub Category')));
    }

    public function trash_delete($id)
    {
        try {
            $deleted = SubCategory::onlyTrashed()->findOrFail($id)->forceDelete();
        } catch (\Exception $exception) {
            return back()->with(FlashMsg::explain('danger',__('The subcategory can not be deleted due to its association with another child categories or products. Please delete them first.')));
        }

        return $deleted
            ? back()->with(FlashMsg::delete_succeed(__('Product Sub Category')))
            : back()->with(FlashMsg::delete_failed(__('Product Sub Category')));
    }

    public function trash_bulk_delete(Request $request): JsonResponse
    {
        try {
            SubCategory::onlyTrashed()->WhereIn('id', $request->ids)->forceDelete();
        } catch (\Exception $exception)
        {
            return response()->json(['error_msg' => __('The subcategory can not be deleted due to its association with another subcategories, child categories or products. Please delete them first.')], 550);
        }

        return response()->json(['status' => 'ok']);
    }
}
