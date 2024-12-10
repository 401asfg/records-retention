<?php

namespace App\Http\Controllers;

use App\Http\Resources\BoxResource;
use App\Http\Resources\RetentionRequestResource;
use App\Mail\PendingRecordRetentionRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetentionRequestSuccessfullySubmitted;
use App\Models\Box;
use App\Models\RetentionRequest;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Symfony\Component\Mailer\Exception\TransportException;
use Spatie\Valuestore\Valuestore;
use App\Rules\UserCanAuthorizeRequests;
use LogicException;
use Exception;

class RetentionRequestController extends Controller
{
    // TODO: test
    public function index()
    {
        // FIXME: handle failure case
        $retentionRequests = RetentionRequest::whereNull('authorizing_user_id')
            ->orderBy('created_at', 'asc')
            ->get();

        $retentionRequestCollection = RetentionRequestResource::collection($retentionRequests);
        return view('app')->with('data', json_encode($retentionRequestCollection));
    }

    // TODO: test
    public function edit(string $id)
    {
        // FIXME: handle failure case
        $retentionRequest = RetentionRequest::findOrFail($id);
        $boxes = Box::where('retention_request_id', '=', $id)->get();

        $retentionRequestResource = new RetentionRequestResource($retentionRequest);
        $boxCollection = BoxResource::collection($boxes);

        $data = [
            'retentionRequest' => $retentionRequestResource,
            'boxes' => $boxCollection
        ];

        return view('app')->with('data', json_encode($data));
    }

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
        } catch (LogicException $exception) {
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
            return redirect()->back()->withErrors($idValidator->errors());

        $requestValidator = Validator::make(
            [
                'authorizing_user_id' => $request->input('authorizing_user_id'),
                'boxes' => $request->input('boxes')
            ],
            [
                'authorizing_user_id' => ['required', 'numeric', 'exists:users,id', new UserCanAuthorizeRequests],
                'boxes' => 'array',
                'boxes.*.id' => 'required|numeric|exists:boxes,id',
                'boxes.*.description' => 'string',
                'boxes.*.destroy_date' => 'nullable|date'
            ]
        );

        if ($requestValidator->fails())
            return redirect()->back()->withErrors($requestValidator->errors());

        // FIXME: do the exceptions created by this need to be handled?
        $settings = Valuestore::make(config_path('settings.json'));

        if (!$settings->has('next_tracking_number'))
            return response("settings.json doesn't contain the next_tracking_number field.", 400)->header('Content-Type', 'text/plain');

        $requestBoxes = $request->input('boxes');

        // ORDER THE UPDATE REQUEST BOXES AND THE DB BOXES BY ID
        usort($requestBoxes, function ($a, $b) {
            return $a['id'] <=> $b['id'];   // FIXME: is the right order in which to sort? (asc?)
        });

        $dbBoxes = Box::where("retention_request_id", "=", $id)->orderBy('id')->get('id');
        // FIXME: do the exceptions created by this need to be handled?
        $nextTrackingNumber = $settings->get('next_tracking_number');   // FIXME: is this going to be returned as an int?

        // ASSIGN THE USER ID TO THE RETENTION REQUEST, UPDATE THE DB BOXES WITH THE REQUEST BOX DATA, ASSIGN EACH DB BOX A TRACKING NUMBER
        try {
            DB::beginTransaction();

            $nextTrackingNumber = $this::updateBoxes($dbBoxes, $requestBoxes, $nextTrackingNumber);
            RetentionRequest::findOrFail($id)->update(['authorizing_user_id' => $request->input('authorizing_user_id')]);

            // FIXME: handle put exceptions
            // FIXME: move this into update?
            $settings->put('next_tracking_number', $nextTrackingNumber);

            DB::commit();
        } catch (LogicException $exception) {
            DB::rollBack();
            // FIXME: is this the correct way to do this?
            return redirect()->back()->withErrors(new MessageBag(['boxes.*.id' => $exception->getMessage()]));
        } catch (Exception $exception) {
            // FIXME: is this the correct exception type?
            // FIXME: different exceptions for when retention request fails vs when box fails?
            DB::rollBack();
            // FIXME: is there a better way to get an explicit error report?
            return response($exception->getMessage(), 400)->header('Content-Type', 'text/plain');
        }

        return response(['status' => 'success'], 200)->header('Content-Type', 'text/plain');
    }

    // ASSUMES THAT THE ORIGINAL AND TARGET BOXES ARE ORDERED BY ID; THERE ISN'T NECESSARILY A 1-1 CORRESPONDENCE BETWEEN THE TWO
    // ASSUMES THAT THE ORIGINAL BOXES PARAMETER CONTAINS ALL THE BOXES IN THE DB FOR A RETENTION REQUEST
    // PRODUCES THE NEW NEXT UNIQUE TRACKING NUMBER, AFTER ALL DB BOXES HAVE BEEN ASSIGNED A UNIQUE TRACKING NUMBER
    private static function updateBoxes($originalBoxes, $targetBoxes, $initNextTrackingNumber): int
    {
        $nextTrackingNumber = $initNextTrackingNumber;
        $targetBoxIndex = 0;
        $targetBoxCount = count($targetBoxes);

        foreach ($originalBoxes as $originalBox) {
            $box = $originalBox->toArray();
            $box['tracking_number'] = $nextTrackingNumber;
            $nextTrackingNumber++;

            if ($targetBoxIndex < $targetBoxCount) {
                if ($originalBox['id'] > $targetBoxes[$targetBoxIndex]['id'])
                    throw new LogicException("Attempted to update a box that doesn't correspond to any box assigned to the retention request that has the give id.");

                if ($originalBox['id'] == $targetBoxes[$targetBoxIndex]['id']) {
                    $targetBox = $targetBoxes[$targetBoxIndex];

                    if (array_key_exists('description', $targetBox))
                        $box['description'] = $targetBox['description'];

                    if (array_key_exists('destroy_date', $targetBox))
                        $box['destroy_date'] = $targetBox['destroy_date'];

                    $targetBoxIndex++;
                }
            }

            Box::findOrFail($originalBox['id'])->update($box);
        }

        return $nextTrackingNumber;
    }

    // FIXME: handle failure case
    // NOTIFIES REQUESTOR AND ALL USERS WHO CAN AUTHORIZE RECORD RETENTION REQUESTS THAT A NEW REQUEST HAS BEEN SUBMITTED
    private static function emailInvolvedParties($requestor, $authorizers)
    {
        // FIXME: queue emails
        $requestorName = $requestor['name'];
        $requestorEmail = $requestor['email'];

        Mail::to($requestorEmail)->queue(new RetentionRequestSuccessfullySubmitted([
            "name" => $requestorName
        ]));

        foreach ($authorizers as $authorizer) {
            Mail::to($authorizer['email'])->queue(new PendingRecordRetentionRequest([
                "authorizer_name" => $authorizer['name'],
                "requestor_name" => $requestorName,
                "requestor_email" => $requestorEmail,
            ]));
        }
    }

    // FIXME: handle failure case
    // PRODUCES A LIST OF ALL USERS WHO CAN AUTHORIZE RECORD RETENTION REQUESTS AND HAVE OPTED INTO RECEIVEING EMAILS
    private static function getUserMailingList()
    {
        // FIXME: need to modify users and roles tables to match this specification
        $users = DB::table('users')
            ->select(['users.name AS name', 'users.email AS email'])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereRaw(
                "users.is_receiving_emails = 1 AND (roles.permissions_code >> ?) & ? = 1",  // DECODES THE PERMISSIONS FROM THE NUMERIC CODE VALUE (FOR EXAMPLE: 5 = 101 = CAN RECEIVE EMAILS AND CAN CHANGE USER ROLES)
                [Role::CAN_RECIEVE_EMAILS_OFFSET, Role::PERMISSION_MASK]    // ONLY RETRIEVES USERS WHO HAVE A ROLE WITH EMAIL RECEIVING PERMISSIONS
            )
            ->get();

        return $users->map(function ($user) {
            return ["name" => (string) $user->name, "email" => (string) $user->email];
        });
    }
}
