<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!Auth::user()->isSuperAdmin()) {
                    abort(403, 'Aksi ini hanya untuk Super Admin.');
                }
                return $next($request);
            }),
        ];
    }

    public function index(): View
    {
        $admins = User::with('branch')
            ->where('role', 'admin_branch')
            ->latest()
            ->get();
        return view('admin.users.index', compact('admins'));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.users.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'admin_branch',
            'branch_id' => $request->branch_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Admin Cabang berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        // Pastikan yang diedit adalah admin_branch
        if ($user->role !== 'admin_branch') {
            abort(404);
        }

        $branches = Branch::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'admin_branch') {
            abort(404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'branch_id' => $request->branch_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Admin Cabang berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== 'admin_branch') {
            abort(404);
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Admin Cabang berhasil dihapus.');
    }
}
