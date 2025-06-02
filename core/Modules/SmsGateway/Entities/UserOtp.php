<?php

namespace Modules\SmsGateway\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOtp extends Model
{
    protected $table = 'user_otps';
    protected $fillable = ['user_id', 'user_type', 'otp_code', 'expire_date'];
    protected $dates = ['expire_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
