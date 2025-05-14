<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::orderBy('name')->paginate(20);
        
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);
        
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'created_by' => Auth::id(),
        ]);
        
        // Log the action
        Log::create([
            'employee_id' => Auth::id(),
            'action' => 'create_department',
            'description' => 'Created department: ' . $department->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $department->load(['employees' => function($query) {
            $query->where('status', 'active');
        }]);
        
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);
        
        $department->name = $request->name;
        $department->description = $request->description;
        $department->is_active = $request->has('is_active');
        $department->updated_by = Auth::id();
        $department->save();
        
        // Log the action
        Log::create([
            'employee_id' => Auth::id(),
            'action' => 'update_department',
            'description' => 'Updated department: ' . $department->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete department with active employees. Please reassign employees first.');
        }
        
        // Log the action before deletion
        Log::create([
            'employee_id' => Auth::id(),
            'action' => 'delete_department',
            'description' => 'Deleted department: ' . $department->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        $department->delete();
        
        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}