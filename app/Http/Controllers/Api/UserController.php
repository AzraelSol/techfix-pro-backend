<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Get all users (admin only).
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($users);
    }

    /**
     * Get incharges list (for assignment).
     */
    public function incharges(Request $request)
    {
        $incharges = User::where('user_type', 'incharge')
            ->withCount(['assignedBookings as active_bookings_count' => function ($query) {
                $query->whereIn('status', ['assigned', 'in_progress', 'waiting_parts']);
            }])
            ->get();

        return response()->json($incharges);
    }

    /**
     * Create a new user (admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'user_type' => ['required', 'in:user,incharge,admin'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => $validated['user_type'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Get a specific user.
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => $user->loadCount(['bookings', 'assignedBookings']),
        ]);
    }

    /**
     * Update a user (admin only).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'user_type' => ['sometimes', 'in:user,incharge,admin'],
            'password' => ['nullable', Password::defaults()],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Delete a user (admin only).
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        // Delete profile image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics()
    {
        return response()->json([
            'total' => User::count(),
            'users' => User::where('user_type', 'user')->count(),
            'incharges' => User::where('user_type', 'incharge')->count(),
            'admins' => User::where('user_type', 'admin')->count(),
        ]);
    }
}

