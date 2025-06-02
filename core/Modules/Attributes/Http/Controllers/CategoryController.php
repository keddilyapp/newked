<?php

namespace Modules\Attributes\Http\Controllers;

use App\Enums\SlugMorphableTypeEnum;
use App\Helpers\FlashMsg;
use App\Helpers\SanitizeInput;
use App\Http\Services\DynamicCustomSlugValidation;
use App\Models\Status;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Attributes\Entities\Category;
use Modules\Attributes\Http\Requests\StoreCategoryRequest;
use Modules\Attributes\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:product-category-list|product-category-create|product-category-edit|product-category-delete', ['only', ['index']]);
        $this->middleware('permission:product-category-create', ['only', ['store']]);
        $this->middleware('permission:product-category-edit', ['only', ['update']]);
        $this->middleware('permission:product-category-delete', ['only', ['destroy', 'bulk_action']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        $all_category = Category::with(["image:id,path", "status"])->where('status_id', 1)->get();
        return view('attributes::backend.category.all')->with(['all_category' => $all_category]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DynamicCustomSlugValidation::validate(
            slug: $data['slug']
        );

        $data['name'] = esc_html($data['name']);
        $data['description'] = esc_html($data['description']);

        $sluggable_text = $data['slug'] == null ? trim($data['name']) : $data['slug'];
        $slug = create_slug($sluggable_text, model_name: 'Slug');
        $data['slug'] = $slug;

        $product_category = Category::create($data);
        $product_category->slug()->create(['slug' => $product_category->slug]);


        return $product_category
            ? back()->with(FlashMsg::create_succeed(__('Product Category')))
            : back()->with(FlashMsg::create_failed(__('Product Category')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCategoryRequest $request
     * @return RedirectResponse
     */
    public function update(UpdateCategoryRequest $request)
    {
        $data = $request->validated();
        DynamicCustomSlugValidation::validate(
            slug: $data['slug'],
            id: $data['id'],
            type: SlugMorphableTypeEnum::PRODUCT_CATEGORY
        );

        $data['name'] = esc_html($data['name']);
        $data['description'] = esc_html($data['description']);

        $category = Category::find($request->id);
        if ($category->slug != $data['slug'])
        {
            $sluggable_text = Str::slug($data['slug'] ?? $data['name'], '-', null);
            $new_slug = create_slug($sluggable_text, 'Slug');
            $data['slug'] = $new_slug;
        }

        $updated = $category->update($data);
        $category->slug()->update(['slug' => $category->slug]);

        return $updated
            ? back()->with(FlashMsg::update_succeed(__('Product Category')))
            : back()->with(FlashMsg::update_failed(__('Product Category')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Category $item
     * @return JsonResponse
     */
    public function destroy(Category $item): JsonResponse
    {
        return response()->json([
            'success' => (bool) $item->delete()
        ]);
    }

    public function bulk_action(Request $request): JsonResponse
    {
        Category::WhereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }


    public function trash(): View
    {
        $all_category = Category::onlyTrashed()->get();
        return view('attributes::backend.category.trash')->with(['all_category' => $all_category]);
    }

    public function trash_restore($id)
    {
        $restored = Category::onlyTrashed()->findOrFail($id)->restore();

        return $restored
            ? back()->with(FlashMsg::restore_succeed(__('Product Category')))
            : back()->with(FlashMsg::restore_failed(__('Product Category')));
    }

    public function trash_delete($id)
    {
        try {
            $deleted = Category::onlyTrashed()->findOrFail($id)->forceDelete();
        } catch (\Exception $exception) {
            return back()->with(FlashMsg::explain('danger',__('The category can not be deleted due to its association with another subcategories, child categories or products. Please delete them first.')));
        }

        return $deleted
            ? back()->with(FlashMsg::delete_succeed(__('Product Category')))
            : back()->with(FlashMsg::delete_failed(__('Product Category')));
    }

    public function trash_bulk_delete(Request $request): JsonResponse
    {
        try {
            Category::onlyTrashed()->WhereIn('id', $request->ids)->forceDelete();
        } catch (\Exception $exception)
        {
            return response()->json(['error_msg' => __('The category can not be deleted due to its association with another subcategories, child categories or products. Please delete them first.')], 550);
        }

        return response()->json(['status' => 'ok']);
    }
}
