<?php

namespace Modules\SmsGateway\Entities;

use App\Enums\StatusEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsGateway extends Model
{
    protected $fillable = ['name', 'image', 'status', 'otp_expire_time','credentials'];

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnums::PUBLISH);
    }
}
