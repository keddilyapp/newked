<?php

namespace Modules\Attributes\Entities;

use App\Events\SlugHandleEvent;
use App\Models\MediaUploader;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["name","slug","description","title","image_id","banner_id", "url"];

    public function slug()
    {
        return $this->morphOne(Slug::class, 'morphable');
    }

    public static function boot()
    {
        parent::boot();
        static::forceDeleted(function ($model) {
            SlugHandleEvent::dispatch($model);
        });
    }

    public function logo(): HasOne
    {
        return $this->hasOne(MediaUploader::class,"id","image_id");
    }

    public function banner(): HasOne
    {
        return $this->hasOne(MediaUploader::class,"id","banner_id");
    }

    protected static function newFactory()
    {
        return \Modules\Attributes\Database\factories\BrandFactory::new();
    }
}
