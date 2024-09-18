<?php

namespace App\Http\Resources;

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
            'manager_name' => $this->manager_name,
            'requestor_name' => $this->requestor_name,
            'requestor_email' => $this->requestor_email,
            'department_id' => $this->department_id,
            'authorizing_user_id' => null
        ];
    }
}
