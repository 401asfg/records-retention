<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const PERMISSION_MASK = 1;
    public const CAN_RECIEVE_EMAILS_OFFSET = 0;
    public const CAN_AUTHORIZE_REQUESTS_OFFSET = 1;
    public const CAN_CHANGE_USER_ROLES_OFFSET = 2;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'permissions_code'
    ];

    // TODO: test
    // PRODUCES A NUMBER THAT REPRESENTS THE PERMISSIONS
    // FOR EXAMPLE: 5 = 101 = CAN RECEIVE EMAILS AND CAN CHANGE USER ROLES
    // FOR EXAMPLE: 3 = 011 = CAN AUTHORIZE REQUESTS AND CAN CHANGE USER ROLES
    public static function encodePermissions(bool $canReceiveEmails, bool $canAuthorizeRequests, bool $canChangeUserRoles): int
    {
        return ($canChangeUserRoles << Role::CAN_CHANGE_USER_ROLES_OFFSET)
            | ($canAuthorizeRequests << Role::CAN_AUTHORIZE_REQUESTS_OFFSET)
            | ($canReceiveEmails << Role::CAN_RECIEVE_EMAILS_OFFSET);
    }
}
