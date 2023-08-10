<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTotalModel extends Model
{
    use HasFactory;
    //add a table name
    protected $table = 'user_totals';

    //add fillable fields
    protected $fillable = [
        'user_id',
        'total_boda_riders',
        'total_fuel_stations',
        "total_boda_stages",
        'daily_boda_riders',
        'daily_fuel_stations',
        "daily_boda_stages",
        "today_boda_riders",
        "today_fuel_stations",
        "today_boda_stages",
    ];



}
