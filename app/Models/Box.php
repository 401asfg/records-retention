<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;

    protected $table = 'boxes';

    protected $fillable = [
        'description',
        'destroy_date',
        'tracking_number',  // TODO: make a way for the admin to specify the next tracking number (will be autoincremented every approved box)
        'retention_request_id'
    ];
}
