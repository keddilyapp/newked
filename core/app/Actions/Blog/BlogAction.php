<?php

namespace App\Actions\Blog;
use App\Enums\SlugMorphableTypeEnum;
use App\Facades\GlobalLanguage;
use App\Http\Services\DynamicCustomSlugValidation;
use App\Models\MetaInfo;
use Modules\Blog\Entities\Blog;
use App\Helpers\LanguageHelper;
use App\Helpers\SanitizeInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogAction
{
    public function store_execute(Request $request) :void {
            $blog = new Blog();
            $blog->title = esc_html($request->title);
            $blog->blog_content = str_replace('script', '', $request->blog_content);
            $blog->excerpt = esc_html($request->excerpt);

            $slug = !empty($request->slug) ? $request->slug : Str::slug($request->title, '-', null);
            $slug = create_slug($slug, 'Slug');

            $blog->slug = esc_html($slug);
            $blog->category_id = $request->category_id;
            $blog->featured = $request->featured;
            $blog->visibility = $request->visibility;
            $blog->status = $request->status;
            $blog->admin_id = Auth::guard('admin')->user()->id;
            $blog->user_id = null;
            $blog->author = Auth::guard('admin')->user()->name;
            $blog->image = $request->image;
            $blog->image_gallery = $request->image_gallery;
            $blog->views = 0;
            $blog->tags = esc_html($request->tags);
            $blog->video_url = esc_html($request->video_url);
            $blog->created_by = 'admin';

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
                'fb_title' =>esc_html($request->meta_fb_title),
                'fb_description' => esc_html($request->meta_fb_description),
            ];

            $blog->save();
            $blog->metainfo()->create($Metas);
            $blog->slug()->create(['slug' => $blog->slug]);
    }


    public function update_execute(Request $request ,$id) : void
    {
        $blog_update =  Blog::findOrFail($id);

        $blog_update->title = esc_html($request->title);
        $blog_update->blog_content = $request->blog_content;
        $blog_update->excerpt = esc_html($request->excerpt);

        $slug = !empty($request->slug) ? Str::slug($request->slug, '-', null) : Str::slug($request->title);

        if ($blog_update->slug != $slug)
        {
            $slug = create_slug($slug, 'Slug');
        }

        $blog_update->slug = esc_html($slug);

        $blog_update->category_id = $request->category_id;
        $blog_update->featured = $request->featured;
        $blog_update->visibility = $request->visibility;
        $blog_update->status = $request->status;
        $blog_update->image = $request->image;
        $blog_update->image_gallery = $request->image_gallery;
        $blog_update->views = 0;
        $blog_update->tags = esc_html($request->tags);
        $blog_update->video_url =$request->video_url;
        $blog_update->save();

        $blog_update->slug()->update(['slug' => $blog_update->slug]);

        $blog_update->metainfo()->updateOrCreate(["metainfoable_id" => $blog_update->id], [
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
        ]);

    }

    public function clone_blog_execute(Request $request)
    {
        $blog_details = Blog::findOrFail($request->item_id);

        $slug = !empty($blog_details->slug) ? $blog_details->slug : Str::slug($blog_details->title, '-', null);
        $slug = create_slug($slug, 'Slug');

        DynamicCustomSlugValidation::validate(
            slug: $slug,
            id: $request->item_id,
            type: SlugMorphableTypeEnum::BLOG
        );

        $cloned_data = Blog::create([
            'category_id' =>  $blog_details->category_id,
            'slug' => $slug,
            'blog_content' => $blog_details->blog_content ?? 'draft blog content',
            'title' => $blog_details->title ,
            'status' => 0,
            'excerpt' => $blog_details->excerpt,
            'image' => $blog_details->image,
            'image_gallery' => $blog_details->image,
            'views' => 0,
            'tags' => $blog_details->tags,
            'user_id' => null,
            'admin_id' => Auth::guard('admin')->user()->id,
            'author' => Auth::guard('admin')->user()->name,
            'featured' => $blog_details->featured,
            'video_url' => $blog_details->video_url,
            'created_by' => $blog_details->created_by,
        ]);


        $meta_object = optional($blog_details->meta_info);
        $Metas = [
            'title' => $meta_object->meta_title,
            'description' => $meta_object->meta_description,
            'image' => $meta_object->meta_image,
            //twitter
            'tw_image' => $meta_object->tw_image,
            'tw_title' => $meta_object->meta_tw_title,
            'tw_description' => $meta_object->meta_tw_description,
            //facebook
            'fb_image' => $meta_object->fb_image,
            'fb_title' => $meta_object->meta_fb_title,
            'fb_description' => $meta_object->meta_fb_description,
        ];

        $cloned_data->metainfo()->create($Metas);
    }
}
