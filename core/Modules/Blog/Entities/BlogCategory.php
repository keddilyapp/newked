<?php

namespace Modules\Blog\Entities;

use App\Events\SlugHandleEvent;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    protected $table = 'blog_categories';
    protected $fillable = ['title','status', 'slug'];

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id', 'id');
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
}
