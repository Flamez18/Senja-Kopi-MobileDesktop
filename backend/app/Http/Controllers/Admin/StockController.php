<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Show stock management panel.
     */
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Super Admin dapat melihat grid produk vs cabang
            $products = Product::with('category')->orderBy('name')->get();
            $branches = Branch::where('is_active', true)->get();
            
            // Dapatkan semua stok dalam bentuk map key 'product_id-branch_id' => is_available
            $stocks = ProductStock::all()->mapWithKeys(function ($item) {
                return ["{$item->product_id}-{$item->branch_id}" => (bool) $item->is_available];
            })->toArray();

            return view('admin.stocks.super', compact('products', 'branches', 'stocks'));
        } else {
            // Admin Cabang hanya melihat stok di cabangnya sendiri
            $branchId = $user->branch_id;
            $branch = Branch::find($branchId);

            $stocks = ProductStock::with('product')
                ->where('branch_id', $branchId)
                ->get();

            return view('admin.stocks.branch', compact('branch', 'stocks'));
        }
    }

    /**
     * Toggle availability of a specific product stock at a branch.
     */
    public function toggle(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user = Auth::user();

        // Validasi jika dia Admin Cabang, dia hanya boleh toggle stok cabangnya sendiri
        if ($user->isAdminBranch() && $user->branch_id != $request->branch_id) {
            abort(403, 'Anda tidak memiliki hak untuk mengelola stok cabang lain.');
        }

        $stock = ProductStock::where('product_id', $request->product_id)
            ->where('branch_id', $request->branch_id)
            ->first();

        if (!$stock) {
            // Buat baru jika belum ada
            $stock = ProductStock::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'is_available' => true,
            ]);
        }

        // Toggle status
        $stock->update([
            'is_available' => !$stock->is_available,
        ]);

        $status = $stock->is_available ? 'Tersedia' : 'Habis';

        return back()->with('success', "Stok produk berhasil diubah menjadi {$status}.");
    }
}
