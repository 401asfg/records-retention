<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Response;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => 'required|string'
        ]);

        $query = $request->input('query');

        try {
            // FIXME: is this vulnerable to SQL injections?
            $departments = Department::where('name', 'like', '%' . $query . '%')->get();
        } catch (QueryException $exception) {
            return response($exception->getMessage(), 400)->header('Content-Type', 'text/plain');
        }

        // FIXME: return this in a response
        // FIXME: handle failure cases
        return DepartmentResource::collection($departments);
    }
}
