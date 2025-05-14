<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = Shift::orderBy('start_time')->paginate(20);
        
        return view('shifts.index', compact('shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shifts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);
        
        $shift = Shift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period_minutes' => $request->grace_period_minutes,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'created_by' => Auth::id(),
        ]);
        
        // Log the action
        Log::create([
            'employee_id' => Auth::id(),
            'action' => 'create_shift',
            'description' => 'Created shift: ' . $shift->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $shift->load(['employees' => function($query) {
            $query->where('status', 'active');
        }]);
        
        return view('shifts.show', compact('shift'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        return view('shifts.edit', compact('shift'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);
        
        $shift->name = $request->name;
        $shift->start_time = $request->start_time;
        $shift->end_time = $request->end_time;
        $shift->grace_period_minutes = $request->grace_period_minutes;
        $shift->description = $request->description;
        $shift->is_active = $request->has('is_active');
        $shift->updated_by = Auth::id();
        $shift->save();
        
        // Log the action
        Log::create([
            'employee_id' => Auth::id(),
            'action' => 'update_shift',
            'description' => 'Updated shift: ' . $shift->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Shift $shift)
    {
        // Check if shift has employees
        if ($shift->employees()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete shift with active employees. Please reassign employees first.');
        }
        
        // Log the action before deletion
        Log::create([
            'employee_id' => Auth::id(),
            'action' => 'delete_shift',
            'description' => 'Deleted shift: ' . $shift->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        $shift->delete();
        
        return redirect()->route('shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}