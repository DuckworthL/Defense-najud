@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Employee</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <h5 class="border-bottom pb-2 mb-3 mt-4">Employment Information</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                        <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                            @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id', $employee->role_id) == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="shift_id" class="form-label">Shift <span class="text-danger">*</span></label>
                                        <select class="form-select @error('shift_id') is-invalid @enderror" id="shift_id" name="shift_id" required>
                                            @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id', $employee->shift_id) == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('shift_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="date_hired" class="form-label">Date Hired <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date_hired') is-invalid @enderror" id="date_hired" name="date_hired" value="{{ old('date_hired', $employee->date_hired->format('Y-m-d')) }}" required>
                                        @error('date_hired')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Profile Picture</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if($employee->profile_picture)
                                            <img id="preview-image" src="{{ asset('storage/' . $employee->profile_picture) }}" class="img-fluid rounded-circle" style="max-width: 200px; max-height: 200px;">
                                            @else
                                            <img id="preview-image" src="{{ asset('images/default-profile.png') }}" class="img-fluid rounded-circle" style="max-width: 200px; max-height: 200px;">
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <label for="profile_picture" class="form-label">Change Picture</label>
                                            <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" id="profile_picture" name="profile_picture" accept="image/*">
                                            <div class="form-text">Maximum size: 2MB</div>
                                            @error('profile_picture')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Employee ID</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-0">
                                            <strong>Employee ID:</strong> {{ $employee->employee_id }}
                                            <p class="mb-0 small">Employee ID cannot be changed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('employees.change-password', $employee) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password for {{ $employee->full_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters and contain letters, numbers, and symbols.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview uploaded image
        const profilePicture = document.getElementById('profile_picture');
        const previewImage = document.getElementById('preview-image');
        
        profilePicture.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection