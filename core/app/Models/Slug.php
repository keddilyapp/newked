<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slug extends Model
{
    use HasFactory;

    protected $fillable = ['slug'];

    public function morphable()
    {
        return $this->morphTo();
    }
}
