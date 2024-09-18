<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BoxCollection extends ResourceCollection
{
    public function __construct($resource, $retention_request_id)
    {
        parent::__construct($resource);
        $this->retention_request_id = $retention_request_id;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'description' => $this->description,
            'destroy_date' => $this->destroy_date,
            'retention_request_id' => $this->retention_request_id,
            'tracking_number' => null
        ];
    }
}
