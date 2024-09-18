<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Response;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        // FIXME: does this return the correct response upon failure?
        $request->validate([
            'query' => 'required|string'
        ]);

        $query = $request->input('query');
        // FIXME: is this vulnerable to SQL injections?
        $departments = Department::where('name', 'like', '%?%')->setBindings([$query])->get();
        return DepartmentResource::collection($departments);
    }
}
