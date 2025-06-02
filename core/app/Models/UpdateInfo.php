<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateInfo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'url',
        'custom',
        'version',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
