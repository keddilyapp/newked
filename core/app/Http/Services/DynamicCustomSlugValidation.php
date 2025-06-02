<?php

namespace App\Http\Services;

use App\Enums\SlugMorphableTypeEnum;
use App\Models\Slug;
use Illuminate\Validation\ValidationException;

class DynamicCustomSlugValidation
{
    public static function isExist(string $slug, ?int $id, ?SlugMorphableTypeEnum $type): bool
    {
        // check type value is required while id is not null
        if(! is_null($id) && is_null($type)) {
            throw (new \RuntimeException('Slug morphable type is required while id is passed.'));
        }

        return Slug::query()->where('slug', $slug)->when(! is_null($type), function ($query) use ($id, $type) {
            return $query->where(function ($query) use ($id, $type) {
                $query->where('morphable_id', '!=', $id)->where('morphable_type', $type->value);
            });
        })->exists();
    }

    public static function validate(string $slug, ?int $id = null, ?SlugMorphableTypeEnum $type = null): bool|ValidationException
    {
        if(self::isExist($slug, $id, $type)){
            throw ValidationException::withMessages(['slug' => 'This slug already exists.']);
        }

        return true;
    }
}
