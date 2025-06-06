<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Enums\SlugMorphableTypeEnum;
use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Http\Services\DynamicCustomSlugValidation;
use App\Models\Page;
use App\Models\PageBuilder;
use App\Models\PaymentLogs;
use App\Models\PricePlan;
use Artesaos\SEOTools\SEOMeta;
use Artesaos\SEOTools\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use function GuzzleHttp\Promise\all;

class PagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:page-list|page-edit|page-delete',['only' => ['all_pages','page_builder']]);
        $this->middleware('permission:page-create',['only' => ['create_page','store_new_page']]);
        $this->middleware('permission:page-edit',['only' => ['edit_page','update']]);
        $this->middleware('permission:page-delete',['only' => ['delete']]);
    }

    public function all_pages()
    {
        $all_pages = Page::orderBy('id','desc')->get();
        return view('landlord.admin.pages.index',compact('all_pages'));
    }

    public function create_page()
    {
        return view('landlord.admin.pages.create');
    }

    public function page_builder($id)
    {
        $page = Page::with('metainfo')->findOrfail($id);
        return view('landlord.admin.pages.page-builder',compact('page'));
    }

    public function edit_page($id)
    {
        $page = Page::with('metainfo')->findOrfail($id);
        return view('landlord.admin.pages.edit',compact('page'));
    }

    public function store_new_page(Request $request)
    {
        $validatedData = $this->validate($request, [
            'status' => 'required|integer',
            'visibility' => 'required|integer',
            'title' => 'required|string',
            'page_content' => 'nullable|string',
            'navbar_variant' => 'nullable|string',
            'footer_variant' => 'nullable|string',
            'slug' => 'required|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_image' => 'nullable|integer',
            'tw_image' => 'nullable|integer',
            'fb_image' => 'nullable|integer',
            'meta_tw_title' => 'nullable|string',
            'meta_tw_description' => 'nullable|string',
            'meta_fb_title' => 'nullable|string',
            'meta_fb_description' => 'nullable|string',
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug']
        );

        $page_data = new Page();
        $slug = $request->slug ?? $request->name;
        $page_data->slug = create_slug($slug, 'Slug');
        $page_data->title = esc_html($request->title);
        $page_data->page_content = str_replace('script','',$request->page_content);
        $page_data->visibility = $request->visibility;
        $page_data->status = $request->status;

        if(tenant()){
            $page_data->navbar_variant = $request->navbar_variant;
            $page_data->footer_variant = $request->footer_variant;
        }

        $page_data->page_builder = is_null( $request->page_builder) ? 0 : 1;
        $page_data->breadcrumb = is_null( $request->breadcrumb) ? 0 : 1;

        $Metas = [
            'title' => esc_html($request->meta_title),
            'description' => esc_html($request->meta_description),
            'image' => $request->meta_image,
            //twitter
            'tw_image' => $request->tw_image,
            'tw_title' => esc_html($request->meta_tw_title),
            'tw_description' => esc_html($request->meta_tw_description),
            //facebook
            'fb_image' => $request->fb_image,
            'fb_title' =>  esc_html($request->meta_fb_title),
            'fb_description' =>  esc_html($request->meta_fb_description),
        ];

        $page_data->save();
        $page_data->metainfo()->create($Metas);
        $page_data->slug()->create(['slug' => $page_data->slug]);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function update(Request $request)
    {
        $validateData = $this->validate($request, [
            'id' => 'required|integer',
            'status' => 'required|integer',
            'visibility' => 'required|integer',
            'title' => 'required|string',
            'page_content' => 'nullable|string',
            'navbar_variant' => 'nullable|string',
            'footer_variant' => 'nullable|string',
            'slug' => ['required','string'],
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_image' => 'nullable|integer',
            'tw_image' => 'nullable|integer',
            'fb_image' => 'nullable|integer',
            'meta_tw_title' => 'nullable|string',
            'meta_tw_description' => 'nullable|string',
            'meta_fb_title' => 'nullable|string',
            'meta_fb_description' => 'nullable|string',
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validateData['slug'],
            id: $validateData['id'],
            type: SlugMorphableTypeEnum::PAGE
        );

        $page_data = Page::find($request->id);
        \Cache::forget('page_id-'.$page_data->id);

        if ($request->slug !== $page_data->slug)
        {
            $page_data->slug = create_slug($request->slug ?? $request->name, 'Slug');
        }

        $page_data->title = esc_html($request->title);
        $page_data->page_content = str_replace('script','',$request->page_content);
        $page_data->visibility = $request->visibility;
        $page_data->status = $request->status;

        if(tenant()){
            $page_data->navbar_variant = $request->navbar_variant;
            $page_data->footer_variant = $request->footer_variant;
        }

        $page_data->page_builder = is_null( $request->page_builder) ? 0 : 1;
        $page_data->breadcrumb = is_null( $request->breadcrumb) ? 0 : 1;
        $page_data->save();
        $page_data->slug()->update(['slug' => $page_data->slug]);

        $meta_data = [
            'title' => esc_html($request->meta_title),
            'description' => esc_html($request->meta_description),
            'image' => $request->meta_image,
            //twitter
            'tw_image' => $request->tw_image,
            'tw_title' =>  esc_html($request->meta_tw_title),
            'tw_description' => esc_html($request->meta_tw_description),
            //facebook
            'fb_image' => $request->fb_image,
            'fb_title' => esc_html($request->meta_fb_title),
            'fb_description' => esc_html($request->meta_fb_description),
        ];

        $page_data->metainfo()->updateOrCreate(["metainfoable_id" => $page_data->id] ,$meta_data);
        $page_data->slug()->update(['slug' => $page_data->slug]);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function delete($id){
        $page = Page::find($id);
        $page->metainfo()->delete();
        $page->delete();
        return response()->danger(ResponseMessage::delete());
    }

    public function download($id)
    {
        \Debugbar::disable();

        $page = Page::findorFail($id);

        if ($page->page_builder)
        {
            $page_contents = PageBuilder::where('addon_page_id', $page->id)->orderBy('id', 'ASC')->get()->toJson();
        } else {
            $array = [
                [
                    'text' => $page->page_content,
                    'addon_page_type' => 'simple_page'
                ]
            ];
            $page_contents = json_encode($array);
        }

        $fileName = $page->slug. '-layout.json';

        header('Content-Disposition: attachment; filename='.$fileName.'');
        header('Content-Type: application/json');
        echo $page_contents;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'page_layout' => 'required|mimes:json',
            'page_id' => 'required'
        ]);


        DB::beginTransaction();
        try {
            $file_contents = json_decode(file_get_contents($request->file('page_layout')));

            $contentArr = [];
            if (current($file_contents)->addon_page_type == 'dynamic_page')
            {
                foreach ($file_contents as $key => $content)
                {
                    unset($content->id);
                    $content->addon_page_id = (int)trim($request->page_id);
                    $content->created_at = now();
                    $content->updated_at = now();

                    foreach ($content as $key2 => $con)
                    {
                        $contentArr[$key][$key2] = $con;
                    }
                }

                Page::findOrFail($request->page_id)->update(['page_builder' => 1]);

                PageBuilder::where('addon_page_id', $request->page_id)->delete();
                PageBuilder::insert($contentArr);
            } else {
                Page::findOrFail($request->page_id)->update([
                    'page_builder' => 0,
                    'page_content' => current($file_contents)->text
                ]);
            }

            DB::commit();
            $type = 'success';
            $message = 'Page layout uploaded successfully.';
        } catch (\Exception $exception) {
            DB::rollBack();
            $type = 'danger';
            $message = 'Please upload correct format of file';
        }

        return back()->with([
            'type' => $type,
            'msg' => $message
        ]);
    }
}
