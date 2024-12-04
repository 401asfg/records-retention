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
            'id' => $this->id,
            'requestorName' => $this->requestor_name,
            'requestorEmail' => $this->requestor_email,
            'managerName' => $this->manager_name,
            'departmentName' => Department::find($this->department_id)->name,
            'createdAt' => $this->created_at
        ];
    }
}
