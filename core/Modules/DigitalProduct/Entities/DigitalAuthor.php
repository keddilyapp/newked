<?php

namespace Modules\DigitalProduct\Entities;

use App\Events\SlugHandleEvent;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DigitalAuthor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'status', 'image_id'];
    protected $table = 'digital_authors';

    public function additionalFields(): HasMany
    {
        return $this->hasMany(AdditionalField::class, 'author_id', 'id');
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
        return \Modules\DigitalProduct\Database\factories\DigitalAuthorFactory::new();
    }
}
