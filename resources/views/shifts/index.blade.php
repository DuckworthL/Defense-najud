@extends('layouts.app')

@section('title', 'Shift Management')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1>Shifts</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Shift
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Time Schedule</th>
                                    <th>Grace Period</th>
                                    <th>Employees</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->name }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - 
                                        {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                    </td>
                                    <td>{{ $shift->grace_period_minutes }} minutes</td>
                                    <td>{{ $shift->employees()->count() }}</td>
                                    <td>
                                        <span class="badge bg-{{ $shift->is_active ? 'success' : 'danger' }}">
                                            {{ $shift->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $shift->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $shift->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $shift->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel{{ $shift->id }}">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete the shift: {{ $shift->name }}?
                                                        @if($shift->employees()->count() > 0)
                                                        <div class="alert alert-danger mt-3">
                                                            <i class="fas fa-exclamation-triangle"></i> Warning: This shift has {{ $shift->employees()->count() }} employees assigned to it. 
                                                            You need to reassign these employees before deleting this shift.
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('shifts.destroy', $shift) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" {{ $shift->employees()->count() > 0 ? 'disabled' : '' }}>Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No shifts found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $shifts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection