<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    /**
     * Display a listing of the staff.
     */
    public function index()
    {
        $staff = User::whereIn('role', ['admin', 'staff'])
            ->with(['assignedService'])
            ->orderBy('name')
            ->get();
            
        $services = Service::where('is_active', true)->orderBy('display_order')->get();
        
        return view('admin.staff.index', compact('staff', 'services'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,staff',
            'employee_id' => 'nullable|string|max:50',
            'assigned_service_id' => 'nullable|exists:services,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'employee_id' => $validated['employee_id'] ?? null,
            'assigned_service_id' => $validated['assigned_service_id'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Staff member created successfully.');
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,staff',
            'employee_id' => 'nullable|string|max:50',
            'assigned_service_id' => 'nullable|exists:services,id',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'employee_id' => $validated['employee_id'] ?? null,
            'assigned_service_id' => $validated['assigned_service_id'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $staff->update($data);

        return back()->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $staff->delete();

        return back()->with('success', 'Staff member deleted successfully.');
    }
}
