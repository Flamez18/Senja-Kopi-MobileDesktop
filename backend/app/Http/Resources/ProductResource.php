<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Cek ketersediaan di cabang tertentu jika dilewatkan di query parameter atau request
        $branchId = $request->query('branch_id') ?? $request->input('branch_id');
        $isStockAvailable = true;

        if ($branchId) {
            // Kita cari dari relasi stocks yang sudah diload, atau query langsung
            $stock = $this->stocks->where('branch_id', $branchId)->first();
            $isStockAvailable = $stock ? (bool)$stock->is_available : false;
        }

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (int)$this->price,
            'image_url' => $this->image_url,
            'is_available' => (bool)$this->is_available,
            'is_stock_available' => $isStockAvailable && $this->is_available, // Tersedia jika global tersedia DAN stok cabang tersedia
        ];
    }
}
