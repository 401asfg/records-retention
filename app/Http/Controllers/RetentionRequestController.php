<?php

namespace App\Http\Controllers;

use App\Http\Resources\RetentionRequestResource;
use App\Http\Resources\BoxCollection;
use App\Models\Box;
use App\Models\RetentionRequest;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RetentionRequestController extends Controller
{
    public function index(Request $request)
    {
        return view('index');
    }

    public function store(Request $request)
    {
        // FIXME: does this return the correct response upon failure?
        $request->validate([
            'retention_request.manager_name' => 'required|string',
            'retention_request.requestor_name' => 'required|string',
            'retention_request.requestor_email' => 'required|email',
            // FIXME: should this have two seperate error messages?
            'retention_request.department_id' => 'required|exists:departments,id',
            'boxes.*.description' => 'required|string',
            'boxes.*.destroy_date' => 'nullable|date'
        ]);

        $retentionRequestResource = RetentionRequestResource::toArray($request->input('retention_request'));
        $retentionRequest = RetentionRequest::create($retentionRequestResource);

        try {
            DB::beginTransaction();

            $retentionRequestSaved = $retentionRequest->save();

            // FIXME: should the exceptions be more specific subtypes?
            if (!$retentionRequestSaved)
                throw new Exeception('Failed to save a Retention Request');

            $boxCollection = new BoxCollection($request->input('boxes'), $retentionRequest['id']);

            foreach ($boxCollection as $boxResource) {
                $box = Box::create($boxResource);
                $boxSaved = $box->save();

                // FIXME: should the exceptions be more specific subtypes?
                if (!$boxSaved)
                    throw new Exception('Failed to save a Box');
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

        // FIXME: is this the correct way to respond?
        return response([
            'status' => 'success'
        ], 200);
    }
}
