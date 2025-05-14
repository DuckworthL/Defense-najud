@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leave Types</h1>
        <a href="{{ route('leave-types.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Leave Type
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Leave Types</h6>
        </div>
        <div class="card-body">
            @if(count($leaveTypes) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Paid/Unpaid</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveTypes as $leaveType)
                                <tr>
                                    <td>{{ $leaveType->name }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($leaveType->description, 50) }}</td>
                                    <td>
                                        <span class="badge {{ $leaveType->is_active ? 'bg-success' : 'bg-danger' }} text-white">
                                            {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $leaveType->is_paid ? 'bg-primary' : 'bg-warning' }} text-white">
                                            {{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('leave-types.edit', $leaveType) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('leave-types.destroy', $leaveType) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this leave type? This may affect existing leave records.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $leaveTypes->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No leave types found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection