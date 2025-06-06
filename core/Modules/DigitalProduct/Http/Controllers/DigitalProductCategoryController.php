<?php

namespace Modules\DigitalProduct\Http\Controllers;

use App\Enums\SlugMorphableTypeEnum;
use App\Helpers\FlashMsg;
use App\Http\Services\DynamicCustomSlugValidation;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\DigitalProduct\Entities\DigitalCategories;
use Modules\DigitalProduct\Entities\DigitalProductType;

class DigitalProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $all_category = DigitalCategories::all();
        $all_product_type = DigitalProductType::all();
        return view('digitalproduct::admin.category.all', compact('all_category', 'all_product_type'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('digitalproduct::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'nullable|max:255',
            'type_id' => 'required|integer',
            'description' => 'nullable|max:255',
            'status_id' => 'required|boolean',
            'image_id' => 'nullable|numeric'
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'] ?? Str::slug($validatedData['name']),
        );
        $slug = ! empty($validatedData['slug']) ? $validatedData['slug'] : $validatedData['name'];

        $digital_product_category = new DigitalCategories();
        $digital_product_category->name = $validatedData['name'];
        $digital_product_category->slug = create_slug(
            sluggable_text: $slug,
            model_name: 'Slug',
        );
        $digital_product_category->description = $validatedData['description'];
        $digital_product_category->digital_product_type = $validatedData['type_id'];
        $digital_product_category->status = $validatedData['status_id'];
        $digital_product_category->image_id = $validatedData['image_id'] ?? null;
        $digital_product_category->save();
        $digital_product_category->slug()->create(['slug' => $digital_product_category->slug]);

        return back()->with(FlashMsg::create_succeed(__('Product Category')));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('digitalproduct::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('digitalproduct::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'name' => 'required|max:255',
            'slug' => 'nullable|max:255',
            'type_id' => 'required|integer',
            'description' => 'nullable|max:255',
            'status_id' => 'required|boolean',
            'image_id' => 'nullable|numeric'
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'],
            id: $validatedData['id'],
            type: SlugMorphableTypeEnum::PRODUCT_DIGITAL_CATEGORY
        );
        $slug = ! empty($validatedData['slug']) ? $validatedData['slug'] : $validatedData['name'];

        $digital_product_category = DigitalCategories::find($request->id);
        $digital_product_category->name = $validatedData['name'];
        $digital_product_category->slug = create_slug(
            sluggable_text: $slug,
            model_name: 'Slug',
        );
        $digital_product_category->description = $validatedData['description'];
        $digital_product_category->digital_product_type = $validatedData['type_id'];
        $digital_product_category->status = $validatedData['status_id'];
        $digital_product_category->image_id = $validatedData['image_id'] ?? null;
        $digital_product_category->save();
        $digital_product_category->slug()->update(['slug' => $digital_product_category->slug]);

        return back()->with(FlashMsg::update_succeed(__('Product Category')));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $digital_product_category = DigitalCategories::findOrFail($id);
        $digital_product_category->delete();

        return back()->with(FlashMsg::delete_succeed(__('Product Category')));
    }

    public function bulk_action(Request $request): JsonResponse
    {
        DigitalCategories::WhereIn('id', $request->ids)->delete();

        return response()->json(['status' => 'ok']);
    }
}
