<?php

namespace App\Http\Controllers;

use App\Mail\PendingRecordRetentionRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetentionRequestSuccessfullySubmitted;
use App\Models\Box;
use App\Models\RetentionRequest;
use App\Models\Role;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportException;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\Valuestore\Valuestore;
use Closure;

// TODO: test
class UserCanAuthorizeRequests implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $authorizingUserRoleIds = User::where('id', '=', $value)->get('users.role_id');
        $authorizingUsersCount = $authorizingUserRoleIds->count();

        if ($authorizingUsersCount != 1)
            $fail("The " . $attribute . " field identifies " . $authorizingUsersCount . " users instead of one user.");

        $authorizingUserRole = Role::findOrFail($authorizingUserRoleIds[0]);

        if ($this::canAuthorizeRequests($authorizingUserRole['permissions_code']))
            $fail("The " . $attribute . "field is not a user that can authorize requests.");
    }

    private static function canAuthorizeRequests($permissionsCode): bool
    {
        return ($permissionsCode >> Role::CAN_AUTHORIZE_REQUESTS_OFFSET) & Role::PERMISSION_MASK;
    }
}

class RetentionRequestController extends Controller
{
    // TODO: test that all admins and authorizors that haven't opted out of receiving emails get sent emails
    // TODO: test that the requestor recieves their email

    public function store(Request $request)
    {
        $request->validate([
            'retention_request.manager_name' => 'required|string',
            'retention_request.requestor_name' => 'required|string',
            'retention_request.requestor_email' => 'required|email',
            'retention_request.department_id' => 'required|exists:departments,id',
            'boxes' => 'required|array|min:1',
            'boxes.*.description' => 'required|string',
            'boxes.*.destroy_date' => 'nullable|date'
        ]);

        // TODO: test all exception types
        try {
            DB::beginTransaction();

            $retentionRequest = RetentionRequest::create($request->input('retention_request'));

            $retentionRequestId = $retentionRequest['id'];
            $boxesData = $request->input('boxes');

            foreach ($boxesData as $boxData) {
                $boxData['retention_request_id'] = $retentionRequestId;
                Box::create($boxData);
            }

            DB::commit();

            // FIXME: should this cause a rollback on failure?
            $this::emailInvolvedParties(
                [
                    "name" => $retentionRequest["requestor_name"],
                    "email" => $retentionRequest["requestor_email"]
                ],
                $this::getUserMailingList()
            );
        } catch (QueryException $exception) {
            // FIXME: is this the correct exception type?
            // FIXME: different exceptions for when retention request fails vs when box fails?
            DB::rollBack();
            return response($exception->getMessage(), 400)->header('Content-Type', 'text/plain');
        } catch (\LogicException $exception) {
            return response($exception->getMessage(), 207)->header('Content-Type', 'text/plain');
        } catch (TransportException $exception) {
            return response($exception->getMessage(), 207)->header('Content-Type', 'text/plain');
        }

        // FIXME: create error page
        return response(['status' => 'success'], 200)->header('Content-Type', 'text/plain');
    }

    // TODO: test
    public function update(string $id, Request $request)
    {
        // FIXME: use verification token for user instead of id

        $idValidator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:retention_requests,id'
        ]);

        // FIXME: does this trigger correctly?
        // FIXME: is this the correct status?
        if ($idValidator->fails())
            return response($idValidator->errors(), 422)->header('Content-Type', 'text/plain');

        $request->validate([
            'authorizing_user_id' => ['required', 'numeric', 'exists:users,id', new UserCanAuthorizeRequests],
            'boxes' => 'required|array|min:1',
            'boxes.*.id' => 'required|numeric|exists:boxes,id,retention_request_id,' . $id, // FIXME: does checking the rr id this way work?
            'boxes.*.description' => 'required|string',
            'boxes.*.destroy_date' => 'nullable|date'
        ]);

        // FIXME: do the exceptions created by this need to be handled?
        $settings = Valuestore::make(config_path('settings.json'));

        if (!$settings->has('next_tracking_number'))
            return response("settings.json doesn't contain the next_tracking_number field.", 400)->header('Content-Type', 'text/plain');

        $requestBoxes = $request->input('boxes');
        usort($requestBoxes, function ($a, $b) {
            return $a['id'] <=> $b['id'];   // FIXME: is the right order in which to sort? (asc?)
        });

        $dbBoxes = Box::where("retention_request_id", "=", $id)->orderBy('id')->get('id');
        // FIXME: do the exceptions created by this need to be handled?
        $nextTrackingNumber = $settings->get('next_tracking_number');   // FIXME: is this going to be returned as an int?
        $requestBoxCount = count($requestBoxes);
        $requestBoxIndex = 0;

        try {
            DB::beginTransaction();

            foreach ($dbBoxes as $dbBox) {
                $box['tracking_number'] = $nextTrackingNumber;
                $nextTrackingNumber++;

                if ($requestBoxIndex < $requestBoxCount && $dbBox['id'] == $requestBoxes[$requestBoxIndex]['id']) {
                    $requestBox = $requestBoxes[$requestBoxIndex];

                    $box['description'] = $requestBox['description'];
                    $box['destory_date'] = $requestBox['destory_date'];

                    $requestBoxIndex++;
                }

                Box::findOrFail($dbBox['id'])->update($box);
            }

            RetentionRequest::findOrFail($id)->update(['authorizing_user_id' => $request->input('authorizing_user_id')]);
            // FIXME: handle put exceptions
            $settings->put('next_tracking_number', $nextTrackingNumber);

            DB::commit();
        } catch (QueryException $exception) {
            // FIXME: is this the correct exception type?
            // FIXME: different exceptions for when retention request fails vs when box fails?
            DB::rollBack();
            return response($exception->getMessage(), 400)->header('Content-Type', 'text/plain');
        }

        return response(['status' => 'success'], 200)->header('Content-Type', 'text/plain');
    }

    // FIXME: handle failure case
    private static function emailInvolvedParties($requestor, $authorizers)
    {
        $requestorName = $requestor['name'];
        $requestorEmail = $requestor['email'];

        Mail::to($requestorEmail)->send(new RetentionRequestSuccessfullySubmitted([
            "name" => $requestorName
        ]));

        foreach ($authorizers as $authorizer) {
            Mail::to($authorizer['email'])->send(new PendingRecordRetentionRequest([
                "authorizer_name" => $authorizer['name'],
                "requestor_name" => $requestorName,
                "requestor_email" => $requestorEmail,
            ]));
        }
    }

    // FIXME: handle failure case
    private static function getUserMailingList()
    {
        // FIXME: need to modify users and roles tables to match this specification
        $users = DB::table('users')
            ->select(['users.name AS name', 'users.email AS email'])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereRaw(
                "users.is_receiving_emails = 1 AND (roles.permissions_code >> ?) & ? = 1",
                [Role::CAN_RECIEVE_EMAILS_OFFSET, Role::PERMISSION_MASK]
            )
            ->get();

        return $users->map(function ($user) {
            return ["name" => (string) $user->name, "email" => (string) $user->email];
        });
    }
}
