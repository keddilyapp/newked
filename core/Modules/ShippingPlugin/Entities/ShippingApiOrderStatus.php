<?php

namespace Modules\ShippingPlugin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingApiOrderStatus extends Model
{
    protected $table = "shipping_api_order_statuses";
    protected $fillable = ['order_id', 'gateway', 'status', 'message'];
}
