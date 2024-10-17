<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\RetentionRequest;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RetentionRequestController extends Controller
{
    // TODO: test that all admins and authorizors that haven't opted out of receiving emails get sent emails

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
            return response([
                'message' => $exception->getMessage(),
                'status' => 'failed'
            ], 400);
        }

        // TODO: email authorizors and admins (give people email opt-out option?) on successful submit (probably in the controller, pull emails from db)
        // TODO: email confirmation to requestor

        $emails = $this::getMailingList();
        dd($emails);

        // FIXME: is this the correct way to respond?
        // FIXME: create error page
        return response([
            'status' => 'success'
        ], 200);
    }

    // FIXME: refactor to get email from external database?
    private static function getMailingList()
    {
        // FIXME: need to modify users and roles tables to match this specification
        $users = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.email AS email')
            ->where([
                ['roles.name', 'in', '(Admin, Authorizer)'],
                ['users.is_receiving_emails', '=', 'true']
            ])
            ->get();

        $emails = $users->flatMap(function ($user) {
            return $user->email;
        });

        return $emails;
    }
}
