<?php

namespace Modules\Attributes\Http\Controllers;

use App\Enums\SlugMorphableTypeEnum;
use App\Helpers\FlashMsg;
use App\Helpers\SanitizeInput;
use App\Http\Services\DynamicCustomSlugValidation;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Attributes\Entities\Color;

class ColorController extends Controller
{
    private const BASE_PATH = 'attributes::backend.color.';
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:product-color-list|product-color-create|product-color-edit|product-color-delete', ['only', ['index']]);
        $this->middleware('permission:product-color-create', ['only', ['store']]);
        $this->middleware('permission:product-color-edit', ['only', ['update']]);
        $this->middleware('permission:product-color-delete', ['only', ['destroy', 'bulk_action']]);
    }

    /**
     * Display a listing of the resource.
     * @return View|Factory
     */
    public function index(): Factory|View
    {
        $product_colors = Color::all();
        return view(self::BASE_PATH.'all-color', compact('product_colors'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:191',
            'color_code' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
        ]);

        DynamicCustomSlugValidation::validate(
            slug: Str::slug($validatedData['slug'] ?? $validatedData['name'], '-', null)
        );

        $sluggable_text = $request->slug == null ? trim($request->name) : $request->slug;
        $slug = create_slug($sluggable_text, model_name: 'Slug');
        $data['slug'] = $slug;

        $product_color = Color::create([
            'name' => esc_html($request->name),
            'color_code' => $request->color_code,
            'slug' => $data['slug'],
        ]);
        $product_color->slug()->create(['slug' => $product_color->slug]);

        return $product_color
            ? back()->with(FlashMsg::create_succeed('Product Color'))
            : back()->with(FlashMsg::create_failed('Product Color'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'required|string|max:191',
            'color_code' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'] ?? $validatedData['name'],
            id: $validatedData['id'],
            type: SlugMorphableTypeEnum::PRODUCT_COLOR
        );

        $product_color = Color::findOrFail($request->id);

        if ($product_color->slug != $request->slug)
        {
            $sluggable_text = Str::slug($request->slug ?? $request->name, '-', null);
            $new_slug = create_slug($sluggable_text, 'Slug');
            $request['slug'] = $new_slug;
        }

        $product__color = $product_color->update([
            'name' => esc_html($request->name),
            'color_code' => $request->color_code,
            'slug' => $request->slug,
        ]);

        $product_color->slug()->update(['slug' => $product_color->slug]);

        return $product__color
            ? back()->with(FlashMsg::update_succeed('Product Color'))
            : back()->with(FlashMsg::update_failed('Product Color'));
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $product_color = Color::findOrFail($id);

        return $product_color->delete()
            ? back()->with(FlashMsg::delete_succeed('Product Color'))
            : back()->with(FlashMsg::delete_failed('Product Color'));
    }

    public function bulk_action(Request $request): JsonResponse
    {
        $all_product_colors = Color::whereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }
}
