<?php

namespace Modules\Attributes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required',
            "name" => ["required","string"],
            "slug" => ["nullable","string"],
            "description" => ["nullable","string"],
            "url" => ["nullable","string"],
            "image_id" => ["required","string"],
            "banner_id" => ["nullable","string"],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
