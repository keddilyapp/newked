<?php

namespace Modules\Blog\Http\Controllers\Landlord\Admin;
use App\Enums\SlugMorphableTypeEnum;
use App\Facades\GlobalLanguage;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Services\DynamicCustomSlugValidation;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Blog;
use Modules\Blog\Entities\BlogCategory;

class BlogCategoryController extends Controller
{
    public $languages = null;
    private const BASE_PATH = 'blog::landlord.admin.blog.';

    public function __construct()
    {

        $this->middleware('permission:blog-category-list|blog-category-create|blog-category-edit|blog-category-delete',['only' => ['index']]);
        $this->middleware('permission:blog-category-create',['only' => ['new_category']]);
        $this->middleware('permission:blog-category-edit',['only' => ['update_category']]);
        $this->middleware('permission:blog-category-delete',['only' => ['delete_category','bulk_action','delete_category_all_lang']]);
    }

    public function index(Request $request){
        $all_category = BlogCategory::select(['id','title','status','slug'])->get();
        return view(self::BASE_PATH.'category')->with([
            'all_blog_category' => $all_category
        ]);
    }

    public function new_category(Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string|max:191|unique:blog_categories',
            'status' => 'required|string|max:191',
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'] ?? Str::slug($validatedData['title'], '-', null),
        );

        $category = new BlogCategory();
        $category->title = esc_html($request->title);
        $category->status = $request->status;
        $category->slug = create_slug($request->slug ?? $request->title, 'Slug');
        $category->save();
        $category->slug()->create(['slug' => $category->slug]);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function update_category(Request $request){
        $validatedData = $request->validate([
            'id' => 'required',
            'title' => 'required|string|max:191',
            'status' => 'required|string|max:191',
        ]);

        DynamicCustomSlugValidation::validate(
            slug: $validatedData['slug'] ?? Str::slug($validatedData['title'], '-', null),
            id: $validatedData['id'],
            type: SlugMorphableTypeEnum::BLOG_CATEGORY
        );

        $category =  BlogCategory::findOrFail($request->id);
        $category->title = esc_html($request->title);

        if ($category->slug != $request->slug)
        {
            $slug = create_slug($request->slug ?? $request->title, 'Slug');
            $category->slug = $slug;
        }

        $category->status = $request->status;
        $category->save();

        $category->slug()->update([
            'slug' => !empty($category->slug) ? $category->slug :  $slug = create_slug($request->slug ?? $request->title, 'Slug')
        ]);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function delete_category_all_lang(Request $request,$id){

        if (Blog::where('category_id',$id)->first()){
            return redirect()->back()->with([
                'msg' => __('You can not delete this category, It is already associated with a post...'),
                'type' => 'danger'
            ]);
        }
        $category =  BlogCategory::where('id',$id)->first();
        $category->delete();

        return response()->danger(ResponseMessage::delete());
    }


    public function bulk_action(Request $request){
        BlogCategory::whereIn('id',$request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }

}
