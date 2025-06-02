<?php

namespace Modules\DigitalProduct\Entities;

use App\Events\SlugHandleEvent;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalChildCategories extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DigitalCategories::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(DigitalSubCategories::class, 'sub_category_id', 'id');
    }

    public function slug()
    {
        return $this->morphOne(Slug::class, 'morphable');
    }

    public static function boot()
    {
        parent::boot();
        static::deleted(function ($model) {
            SlugHandleEvent::dispatch($model);
        });
    }

    protected static function newFactory()
    {
        return \Modules\DigitalProduct\Database\factories\DigitalProductChildCategoriesFactory::new();
    }
}
