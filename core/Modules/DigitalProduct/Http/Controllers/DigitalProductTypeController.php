<?php

namespace Modules\DigitalProduct\Http\Controllers;

use App\Enums\SlugMorphableTypeEnum;
use App\Enums\StatusEnums;
use App\Helpers\FlashMsg;
use App\Http\Services\DynamicCustomSlugValidation;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\DigitalProduct\Entities\DigitalProductType;
use Modules\DigitalProduct\Http\Services\DigitalType;
use function GuzzleHttp\Promise\all;

class DigitalProductTypeController extends Controller
{
    private array $type;
    private array $extension;
    public function __construct()
    {
        $this->type = (new DigitalType())->digitalType();
        $this->extension = (new DigitalType())->extensionType();
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $types = $this->type;
        $extensions = $this->extension;
        $digital_product_types = DigitalProductType::all();

        return view('digitalproduct::admin.product-type.index', compact('digital_product_types', 'types', 'extensions'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'slug' => 'nullable|unique:slugs,slug|unique:digital_product_types,slug',
            'type' => 'required',
            'extensions' => 'required|array',
            'image_id' => 'nullable',
            'status' => 'required|boolean'
        ],
        [
            'status.boolean' => __('The status field must be public or draft.')
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'] ?? Str::slug($validatedData['name'], '-', null),
        );

        abort_if(!array_key_exists($validatedData['type'], $this->type), 403);
        $exist = $this->arrayValidation($validatedData['extensions'], $this->extension[$validatedData['type']]);
        abort_if(!$exist, 403);

        $slug = $validatedData['slug'] ?? $validatedData['name'];

        $digital_product_type = new DigitalProductType();
        $digital_product_type->name = $validatedData['name'];
        $digital_product_type->slug = create_slug(
            sluggable_text: $slug,
            model_name: 'Slug'
        );
        $digital_product_type->product_type = $validatedData['type'];
        $digital_product_type->extensions = json_encode($validatedData['extensions']);
        $digital_product_type->status = $validatedData['status'];
        $digital_product_type->image_id = $validatedData['image_id'];
        $digital_product_type->save();

        $digital_product_type->slug()->create(['slug' => $digital_product_type->slug]);

        return back()->with(FlashMsg::create_succeed(__('Digital Product Type')));
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
            'id' => 'required|numeric',
            'name' => 'required',
            'slug' => 'nullable',
            'type' => 'required',
            'extensions' => 'required|array',
            'image_id' => 'nullable',
            'status' => 'required|boolean'
        ],
            [
                'status.boolean' => __('The status field must be public or draft.')
            ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'] ?? Str::slug($validatedData['name'], '-', null),
            id: $validatedData['id'],
            type: SlugMorphableTypeEnum::PRODUCT_DIGITAL_TYPE
        );

        abort_if(!array_key_exists($validatedData['type'], $this->type), 403);
        $exist = $this->arrayValidation($validatedData['extensions'], $this->extension[$validatedData['type']]);
        abort_if(!$exist, 403);

        $slug = $validatedData['slug'] ?? $validatedData['name'];

        $digital_product_type = DigitalProductType::find($request->id);
        $digital_product_type->name = $validatedData['name'];
        $digital_product_type->slug = create_slug(
            sluggable_text: $slug,
            model_name: 'Slug'
        );
        $digital_product_type->product_type = $validatedData['type'];
        $digital_product_type->extensions = json_encode($validatedData['extensions']);
        $digital_product_type->status = $validatedData['status'];
        $digital_product_type->image_id = $validatedData['image_id'];
        $digital_product_type->save();

        $digital_product_type->slug()->update(['slug' => $digital_product_type->slug]);

        return back()->with(FlashMsg::update_succeed(__('Digital Product Type')));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        DigitalProductType::find($id)->delete();
        return back()->with(FlashMsg::delete_succeed(__('Digital Product Type')));
    }

    public function bulk_action(Request $request): JsonResponse
    {
        DigitalProductType::WhereIn('id', $request->ids)->delete();

        return response()->json(['status' => 'ok']);
    }

    public function arrayValidation($array_to_check, $array_on_check)
    {
        $exist = true;
        foreach ($array_to_check as $item)
        {
            $exist = in_array($item, $array_on_check);
            if (!$exist)
            {
                break;
            }
        }

        return $exist;
    }

    public function typeBasedExtension(Request $request)
    {
        $request->validate([
            'type' => 'required'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->extension[$request->type] ?? []
        ]);
    }
}
