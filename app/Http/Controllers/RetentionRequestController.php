<?php

namespace App\Http\Controllers;

use App\Mail\PendingRecordRetentionRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetentionRequestSuccessfullySubmitted;
use App\Models\Box;
use App\Models\RetentionRequest;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportException;

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

            // FIXME: should this be moved inside the transaction so it fails loudly?
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

        return $users->map(function ($user) {
            return ["name" => (string) $user->name, "email" => (string) $user->email];
        });
    }
}
