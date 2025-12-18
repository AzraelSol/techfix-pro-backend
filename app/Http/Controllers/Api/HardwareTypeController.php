<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HardwareType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HardwareTypeController extends Controller
{
    /**
     * Get all hardware types.
     */
    public function index(Request $request)
    {
        $query = HardwareType::query();

        if (!$request->user() || !$request->user()->isAdmin()) {
            $query->active();
        }

        $hardwareTypes = $query->withCount('bookings')->get();

        return response()->json($hardwareTypes);
    }

    /**
     * Create a new hardware type (admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $hardwareType = HardwareType::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Hardware type created successfully',
            'hardware_type' => $hardwareType,
        ], 201);
    }

    /**
     * Get a specific hardware type.
     */
    public function show(HardwareType $hardwareType)
    {
        return response()->json([
            'hardware_type' => $hardwareType->loadCount('bookings'),
        ]);
    }

    /**
     * Update a hardware type (admin only).
     */
    public function update(Request $request, HardwareType $hardwareType)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $hardwareType->update($validated);

        return response()->json([
            'message' => 'Hardware type updated successfully',
            'hardware_type' => $hardwareType->fresh(),
        ]);
    }

    /**
     * Delete a hardware type (admin only).
     */
    public function destroy(HardwareType $hardwareType)
    {
        if ($hardwareType->bookings()->exists()) {
            return response()->json([
                'message' => 'Cannot delete hardware type with existing bookings',
            ], 400);
        }

        $hardwareType->delete();

        return response()->json([
            'message' => 'Hardware type deleted successfully',
        ]);
    }
}

