<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetentionRequest extends Model
{
    use HasFactory;

    protected $table = 'retention_requests';

    protected $fillable = [
        'manager_name',
        'requestor_name',
        'requestor_email',
        'department_id',
        'authorizing_user_id'
    ];
}
