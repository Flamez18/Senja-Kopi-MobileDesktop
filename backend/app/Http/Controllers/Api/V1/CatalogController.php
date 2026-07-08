<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Get active banners.
     */
    public function banners(): JsonResponse
    {
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'message' => 'Banner promo berhasil diambil',
            'data' => BannerResource::collection($banners)
        ]);
    }

    /**
     * Get all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil diambil',
            'data' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * Get products with search, category filtering, and branch stock status.
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['stocks' => function ($q) use ($request) {
            if ($request->has('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }
        }])->where('is_available', true);

        // Filter kategori
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Pencarian menu
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar menu berhasil diambil',
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Get single product detail.
     */
    public function showProduct(Request $request, int $id): JsonResponse
    {
        $product = Product::with('stocks')->where('is_available', true)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil',
            'data' => new ProductResource($product)
        ]);
    }
}
