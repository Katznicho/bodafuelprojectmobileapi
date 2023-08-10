<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanModel extends Model
{
    use HasFactory;
    protected $table = 'loan';


    protected $fillable = [
        'loanId',
        'loanAmount',
        'loanInterest',
        'boadUserId',
        'fuelStationId',
        'agentId',
        'status',
        'loan_penalty'
    ];
}
