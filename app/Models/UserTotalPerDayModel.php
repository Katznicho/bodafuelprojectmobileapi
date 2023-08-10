<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTotalPerDayModel extends Model
{
    use HasFactory;
    protected $table = 'user_totals_per_day';

    //add fillable fields
    protected $fillable = [
        'user_id',
        'today_boda_riders',
        'today_fuel_stations',
        "today_boda_stages",
    ];
}
