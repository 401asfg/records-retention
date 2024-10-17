<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'name',     // FIXME: replace with external_user_id when connected to auth system
        'email',    // FIXME: replace with external_user_id when connected to auth system
        'is_receiving_emails',
        'role_id'
    ];
}
