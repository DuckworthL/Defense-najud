@extends('layouts.app')

@section('title', 'Create Employee')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create New Employee</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <h5 class="border-bottom pb-2 mb-3 mt-4">Employment Information</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                        <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                                            <option value="">-- Select Department --</option>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
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
                                            <option value="">-- Select Role --</option>
                                            @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                                            <option value="">-- Select Shift --</option>
                                            @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
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
                                        <input type="date" class="form-control @error('date_hired') is-invalid @enderror" id="date_hired" name="date_hired" value="{{ old('date_hired', date('Y-m-d')) }}" required>
                                        @error('date_hired')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <h5 class="border-bottom pb-2 mb-3 mt-4">Account Information</h5>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    <div class="form-text">Password must be at least 8 characters and contain letters, numbers, and symbols.</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Profile Picture</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <img id="preview-image" src="{{ asset('images/default-profile.png') }}" class="img-fluid rounded-circle" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="profile_picture" class="form-label">Upload Picture</label>
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
                                        <p class="form-text mb-3">
                                            Employee ID will be automatically generated based on the department and current count.
                                        </p>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> The system will generate an ID in the format: DEPT-YYYY-XXXXX
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
                                <i class="fas fa-save"></i> Create Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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