<?php

namespace App\Models;

use App\Events\SlugHandleEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $fillable = ['title','page_content','slug','visibility','page_builder','status','breadcrumb','navbar_variant','footer_variant'];

    public function metainfo()
    {
        return $this->morphOne(MetaInfo::class, 'metainfoable');
    }

    protected $casts = [
        'visibility' => 'integer',
        'page_builder' => 'integer',
        'breadcrumb' => 'integer',
        'status' => 'integer'
    ];

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
