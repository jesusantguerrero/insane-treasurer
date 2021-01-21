<?php

namespace Insane\Treasurer\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [ "user_id", "name", "paypal_plan_id", "quantity", "details", "paypal_plan_status"];
}
