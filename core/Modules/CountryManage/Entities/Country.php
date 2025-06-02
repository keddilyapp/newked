<?php

namespace Modules\CountryManage\Entities;

use App\Enums\StatusEnums;
use Illuminate\Database\Eloquent\Model;
use Modules\TaxModule\Entities\CountryTax;

class Country extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    public function scopePublished()
    {
        return $this->where('status', 'publish');
    }

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function countryTax()
    {
        return $this->hasOne(CountryTax::class);
    }

    public static function all_countries()
    {
        return self::select(['id', 'name', 'status'])->where('status', 'publish')->get();
    }

}
