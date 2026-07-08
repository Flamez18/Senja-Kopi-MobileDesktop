<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'order_status' => $this->order_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'subtotal' => (int)$this->subtotal,
            'service_fee' => (int)$this->service_fee,
            'total' => (int)$this->total,
            'paid_at' => $this->paid_at ? $this->paid_at->toIso8601String() : null,
            'midtrans_snap_token' => $this->midtrans_snap_token,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
