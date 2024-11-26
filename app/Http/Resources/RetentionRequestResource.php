<?php

namespace App\Http\Resources;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RetentionRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'requestor_name' => $this->requestor_name,
            'department_name' => Department::find($this->department_id)->name,
            'created_at' => $this->created_at
        ];
    }
}
