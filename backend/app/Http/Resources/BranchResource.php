<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'open_time' => substr($this->open_time, 0, 5), // Format HH:MM
            'close_time' => substr($this->close_time, 0, 5), // Format HH:MM
            'is_active' => (bool)$this->is_active,
        ];
    }
}
