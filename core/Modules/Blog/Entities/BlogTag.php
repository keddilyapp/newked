<?php

namespace Modules\Blog\Entities;

use App\Events\SlugHandleEvent;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug'];

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
        return \Modules\Blog\Database\factories\BlogtagFactory::new();
    }
}
