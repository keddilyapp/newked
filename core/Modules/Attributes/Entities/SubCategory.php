<?php

namespace Modules\Attributes\Entities;

use App\Events\SlugHandleEvent;
use App\Models\MediaUploader;
use App\Models\Slug;
use App\Models\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["category_id","name","slug","description","image_id","status_id"];

    public function slug()
    {
        return $this->morphOne(Slug::class, 'morphable');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class,"id","category_id");
    }

    public function childCategory(): HasMany
    {
        return $this->hasMany(ChildCategory::class, "sub_category_id", "id");
    }

    public function image(): HasOne
    {
        return $this->hasOne(MediaUploader::class,"id","image_id");
    }

    public function status(): HasOne
    {
        return $this->hasOne(Status::class,"id","status_id");
    }

    protected static function newFactory()
    {
        return \Modules\Attributes\Database\factories\SubCategoryFactory::new();
    }

    public static function boot()
    {
        parent::boot();
        static::forceDeleted(function ($model) {
            SlugHandleEvent::dispatch($model);
        });
    }
}
