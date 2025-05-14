<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaveTypes = LeaveType::orderBy('name')->paginate(10);
        return view('leave_types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('leave_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types',
            'description' => 'required|string|max:1000',
            'is_paid' => 'required|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        
        LeaveType::create($validated);
        
        return redirect()->route('leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        return view('leave_types.show', compact('leaveType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        return view('leave_types.edit', compact('leaveType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $leaveType->id,
            'description' => 'required|string|max:1000',
            'is_paid' => 'required|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Set is_active to false if not present in the request
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }
        
        $leaveType->update($validated);
        
        return redirect()->route('leave_types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Check if this leave type is used in any leave request or leave credit
        $usedInLeave = \App\Models\Leave::where('leave_type_id', $leaveType->id)->exists();
        $usedInCredit = \App\Models\LeaveCredit::where('leave_type_id', $leaveType->id)->exists();
        
        if ($usedInLeave || $usedInCredit) {
            return back()->with('error', 'This leave type cannot be deleted as it is currently in use.');
        }
        
        $leaveType->delete();
        
        return redirect()->route('leave_types.index')
            ->with('success', 'Leave type deleted successfully.');
    }
}