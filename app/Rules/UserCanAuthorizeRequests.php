<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;
use App\Models\Role;

// TODO: test
class UserCanAuthorizeRequests implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $authorizingUserRoleIds = User::where('id', '=', $value)->pluck('users.role_id');
        $authorizingUsersCount = $authorizingUserRoleIds->count();

        if ($authorizingUsersCount != 1) {
            $fail("The " . $attribute . " field identifies " . $authorizingUsersCount . " users instead of one user.");
            return;
        }

        $authorizingUserRole = Role::find($authorizingUserRoleIds[0]);

        if (!$authorizingUserRole) {
            $fail("The " . $attribute . " field identifies a user that does not have a role.");
            return;
        }

        if (!$this::canAuthorizeRequests($authorizingUserRole['permissions_code'])) {
            $fail("The " . $attribute . " field is not a user that can authorize requests.");
            return;
        }
    }

    private static function canAuthorizeRequests($permissionsCode): bool
    {
        return ($permissionsCode >> Role::CAN_AUTHORIZE_REQUESTS_OFFSET) & Role::PERMISSION_MASK;
    }
}
