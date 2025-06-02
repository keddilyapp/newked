<?php

namespace Modules\Attributes\Entities;

use App\Events\SlugHandleEvent;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Entities\ProductInventoryDetail;
use Modules\Product\Entities\ProductSize;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ["name","size_code","slug"];

    public function product_sizes(): HasMany
    {
        return $this->hasMany(ProductInventoryDetail::class, 'size', 'id');
    }

    public function slug()
    {
        return $this->morphOne(Slug::class, 'morphable');
    }

    public static function boot()
    {
        parent::boot();
        static::deleted(function ($category) {
            SlugHandleEvent::dispatch($category);
        });
    }

    protected static function newFactory()
    {
        return \Modules\Attributes\Database\factories\SizeFactory::new();
    }
}
