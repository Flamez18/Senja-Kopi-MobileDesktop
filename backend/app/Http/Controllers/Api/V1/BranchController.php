<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Get list of all active branches.
     */
    public function index(): JsonResponse
    {
        $branches = Branch::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar cabang berhasil diambil',
            'data' => BranchResource::collection($branches)
        ]);
    }

    /**
     * Get details of a single branch.
     */
    public function show(int $id): JsonResponse
    {
        $branch = Branch::where('is_active', true)->find($id);

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail cabang berhasil diambil',
            'data' => new BranchResource($branch)
        ]);
    }
}
