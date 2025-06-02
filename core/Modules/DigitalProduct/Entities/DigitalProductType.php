<?php

namespace Modules\DigitalProduct\Entities;

use App\Events\SlugHandleEvent;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DigitalProductType extends Model
{
    use HasFactory;

    protected $table = 'digital_product_types';
    protected $fillable = ['name', 'slug', 'product_type', 'extensions','image_id', 'status'];

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
        return \Modules\DigitalProduct\Database\factories\DigitalProductTypeFactory::new();
    }
}
