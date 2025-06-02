<?php

namespace Database\Seeders\Tenant;

use App\Http\Services\HandleImageUploadService;
use App\Models\MediaUploader;
use File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use stdClass;

class DefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $default_logo = get_static_option_central('plan_default_logo');

        if ($default_logo) {
            $path = public_path('../../assets/landlord/uploads/default-uploads');
            $full_path = $path . '/' . $default_logo;

            if (! is_dir($full_path) && file_exists($full_path)) {
            $file_info = pathinfo($full_path);
            $extension = $file_info["extension"];

                $file = new UploadedFile(
                    path: $full_path,
                    originalName: $default_logo,
                    mimeType: $extension,
                    error: null,
                    test: true
                );

                $custom_obj = new stdClass();
                $custom_obj->user_type = 'admin';

                $this->insert_media_image($file, $custom_obj);
            }
        }
    }

    public function insert_media_image($image, $request): void
    {
        $image_extenstion = $image->extension();
        $image_name_with_ext = $image->getClientOriginalName();

        $image_name = pathinfo($image_name_with_ext, PATHINFO_FILENAME);
        $image_name = strtolower(Str::slug($image_name));

        $image_db = $image_name . time() . '.' . $image_extenstion;

        $folder_path = global_assets_path('assets/tenant/uploads/media-uploader/' . tenant()->id);

        if (in_array($image_extenstion, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $image_id = HandleImageUploadService::handle_image_upload(
                $image_db,
                $image,
                $image_name_with_ext,
                $folder_path,
                $request
            );

            update_static_option('site_logo', $image_id);
            update_static_option('site_white_logo', $image_id);
        }
    }
}
