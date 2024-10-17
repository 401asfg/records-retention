<?php

namespace App\Http\Controllers;

use App\Mail\PendingRecordRetentionRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetentionRequestSuccessfullySubmitted;
use App\Models\Box;
use App\Models\RetentionRequest;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

        try {
            DB::beginTransaction();

            $retentionRequest = RetentionRequest::create($request->input('retention_request'));

            // FIXME: should the exceptions be more specific subtypes?
            if (!$retentionRequest)
                throw new \Exception('Failed to save a Retention Request');

            $retentionRequestId = $retentionRequest['id'];
            $boxesData = $request->input('boxes');

            foreach ($boxesData as $boxData) {
                $boxData['retention_request_id'] = $retentionRequestId;
                $box = Box::create($boxData);

                // FIXME: should the exceptions be more specific subtypes?
                if (!$box)
                    throw new \Exception('Failed to save a Box');
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            // FIXME: is this the correct way to respond?
            return response($exception->getMessage(), 400)->header('Content-Type', 'text/plain');
        }

        // FIXME: should this be moved inside the try block so it fails loudly?
        $this::emailInvolvedParties(
            [
                "name" => $retentionRequest["requestor_name"],
                "email" => $retentionRequest["requestor_email"]
            ],
            $this::getUserMailingList()
        );

        // FIXME: is this the correct way to respond?
        // FIXME: create error page
        return response(['status' => 'success'], 200);
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

    // FIXME: refactor to get email from external database?
    // FIXME: handle failure case
    private static function getUserMailingList()
    {
        // FIXME: need to modify users and roles tables to match this specification
        $users = DB::table('users')
            ->select(['users.name AS name', 'users.email AS email'])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where("users.is_receiving_emails", "=", 1)
            ->whereIn("roles.name", ["Admin", "Authorizer"])
            ->get();

        // FIXME: necessary?
        return $users->map(function ($user) {
            return ["name" => (string) $user->name, "email" => (string) $user->email];
        });
    }
}
