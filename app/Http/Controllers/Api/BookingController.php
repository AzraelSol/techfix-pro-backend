<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    /**
     * Get all bookings (admin only).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Booking::with(['user', 'incharge', 'hardwareType']);

        // Filter based on user type
        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isIncharge()) {
            $query->where('incharge_id', $user->id);
        }
        // Admin can see all bookings

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('hardware_type_id')) {
            $query->where('hardware_type_id', $request->hardware_type_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhere('device_brand', 'like', "%{$search}%")
                  ->orWhere('device_model', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sort by status priority (pending first) then by created_at
        $statusOrder = "CASE 
            WHEN status = 'pending' THEN 1
            WHEN status = 'assigned' THEN 2
            WHEN status = 'in_progress' THEN 3
            WHEN status = 'waiting_parts' THEN 4
            WHEN status = 'completed' THEN 5
            WHEN status = 'picked_up' THEN 6
            WHEN status = 'cancelled' THEN 7
            ELSE 8
        END";

        $bookings = $query
            ->orderByRaw($statusOrder)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($bookings);
    }

    /**
     * Create a new booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hardware_type_id' => ['required', 'exists:hardware_types,id'],
            'device_brand' => ['required', 'string', 'max:255'],
            'device_model' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'issue_description' => ['required', 'string', 'min:10'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
        ]);

        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagesPaths[] = $image->store('booking-images', 'public');
            }
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'hardware_type_id' => $validated['hardware_type_id'],
            'device_brand' => $validated['device_brand'],
            'device_model' => $validated['device_model'],
            'serial_number' => $validated['serial_number'] ?? null,
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'] ?? 'medium',
            'images' => $imagesPaths,
        ]);

        $booking->load(['user', 'hardwareType']);

        // Notify all admins about the new booking
        $admins = User::where('user_type', 'admin')->get();
        Notification::notifyMany(
            $admins,
            'booking_created',
            'New Repair Booking',
            "New booking #{$booking->booking_number} from {$booking->user->first_name} {$booking->user->last_name} for {$booking->device_brand} {$booking->device_model}",
            'clipboard-list',
            "/admin/bookings",
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number]
        );

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }

    /**
     * Get a specific booking.
     */
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();

        // Check access
        if ($user->isUser() && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->isIncharge() && $booking->incharge_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'booking' => $booking->load(['user', 'incharge', 'hardwareType']),
        ]);
    }

    /**
     * Update a booking.
     */
    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();

        $validated = $request->validate([
            'device_brand' => ['sometimes', 'string', 'max:255'],
            'device_model' => ['sometimes', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'issue_description' => ['sometimes', 'string', 'min:10'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
        ]);

        // Only allow update if booking is pending and user is the owner
        if ($user->isUser()) {
            if ($booking->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if ($booking->status !== 'pending') {
                return response()->json(['message' => 'Cannot update booking that is already being processed'], 400);
            }
        }

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking->fresh()->load(['user', 'incharge', 'hardwareType']),
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->isUser() && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$booking->canBeCancelled()) {
            return response()->json(['message' => 'This booking cannot be cancelled'], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking->fresh(),
        ]);
    }

    /**
     * Assign incharge to booking (admin only).
     */
    public function assignIncharge(Request $request, Booking $booking)
    {
        $request->validate([
            'incharge_id' => ['required', 'exists:users,id'],
        ]);

        $incharge = User::findOrFail($request->incharge_id);

        if (!$incharge->isIncharge()) {
            return response()->json(['message' => 'Selected user is not an incharge'], 400);
        }

        $booking->update([
            'incharge_id' => $incharge->id,
            'status' => 'assigned',
        ]);

        $booking->load(['user', 'hardwareType']);

        // Notify the assigned incharge
        Notification::notify(
            $incharge,
            'booking_assigned',
            'New Repair Assigned to You',
            "You have been assigned to repair {$booking->device_brand} {$booking->device_model} (#{$booking->booking_number})",
            'user-check',
            "/incharge",
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number]
        );

        // Notify the customer that a technician has been assigned
        Notification::notify(
            $booking->user,
            'technician_assigned',
            'Technician Assigned',
            "A technician has been assigned to your repair booking #{$booking->booking_number}",
            'wrench',
            "/bookings/{$booking->id}",
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number]
        );

        return response()->json([
            'message' => 'Incharge assigned successfully',
            'booking' => $booking->fresh()->load(['user', 'incharge', 'hardwareType']),
        ]);
    }

    /**
     * Update booking status (incharge/admin).
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $user = $request->user();

        $request->validate([
            'status' => ['required', 'in:pending,assigned,in_progress,waiting_parts,completed,cancelled,picked_up'],
            'diagnosis' => ['nullable', 'string'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'final_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_completion_date' => ['nullable', 'date'],
            'incharge_notes' => ['nullable', 'string'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        // Check permissions
        if ($user->isIncharge() && $booking->incharge_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $updateData = ['status' => $request->status];

        if ($request->has('diagnosis')) {
            $updateData['diagnosis'] = $request->diagnosis;
        }
        if ($request->has('estimated_cost')) {
            $updateData['estimated_cost'] = $request->estimated_cost;
        }
        if ($request->has('final_cost')) {
            $updateData['final_cost'] = $request->final_cost;
        }
        if ($request->has('estimated_completion_date')) {
            $updateData['estimated_completion_date'] = $request->estimated_completion_date;
        }
        if ($request->has('incharge_notes') && ($user->isIncharge() || $user->isAdmin())) {
            $updateData['incharge_notes'] = $request->incharge_notes;
        }
        if ($request->has('admin_notes') && $user->isAdmin()) {
            $updateData['admin_notes'] = $request->admin_notes;
        }

        if ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        }

        if ($request->status === 'picked_up') {
            $updateData['picked_up_at'] = now();
        }

        $oldStatus = $booking->status;
        $booking->update($updateData);

        // Notify the customer about status change
        if ($oldStatus !== $request->status) {
            $statusLabels = [
                'pending' => 'Pending',
                'assigned' => 'Assigned',
                'in_progress' => 'In Progress',
                'waiting_parts' => 'Waiting for Parts',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'picked_up' => 'Picked Up',
            ];

            $newStatusLabel = $statusLabels[$request->status] ?? $request->status;
            
            Notification::notify(
                $booking->user,
                'booking_status_updated',
                'Booking Status Updated',
                "Your booking #{$booking->booking_number} status has been updated to: {$newStatusLabel}",
                $request->status === 'completed' ? 'check-circle' : 'clock',
                "/bookings/{$booking->id}",
                ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number, 'status' => $request->status]
            );
        }

        return response()->json([
            'message' => 'Booking status updated successfully',
            'booking' => $booking->fresh()->load(['user', 'incharge', 'hardwareType']),
        ]);
    }

    /**
     * Get booking statistics.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        $baseQuery = Booking::query();

        if ($user->isUser()) {
            $baseQuery->where('user_id', $user->id);
        } elseif ($user->isIncharge()) {
            $baseQuery->where('incharge_id', $user->id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'assigned' => (clone $baseQuery)->where('status', 'assigned')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'waiting_parts' => (clone $baseQuery)->where('status', 'waiting_parts')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'picked_up' => (clone $baseQuery)->where('status', 'picked_up')->count(),
        ];

        if ($user->isAdmin()) {
            $stats['total_users'] = User::where('user_type', 'user')->count();
            $stats['total_incharges'] = User::where('user_type', 'incharge')->count();
            $stats['unassigned'] = Booking::whereNull('incharge_id')->where('status', 'pending')->count();
        }

        return response()->json($stats);
    }
}

