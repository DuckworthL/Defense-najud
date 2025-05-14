@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="mb-3">
                                @if($employee->profile_picture)
                                    <img src="{{ asset('storage/' . $employee->profile_picture) }}" class="img-fluid rounded-circle" style="max-width: 150px;" alt="{{ $employee->full_name }}">
                                @else
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; font-size: 56px;">
                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $employee->full_name }}</h4>
                            <p class="text-muted mb-1">{{ $employee->role->name }}</p>
                            <p class="mb-4">{{ $employee->employee_id }}</p>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4 fw-bold">
                                    <i class="fas fa-envelope text-muted me-2"></i> Email:
                                </div>
                                <div class="col-sm-8">
                                    {{ $employee->email }}
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4 fw-bold">
                                    <i class="fas fa-building text-muted me-2"></i> Department:
                                </div>
                                <div class="col-sm-8">
                                    {{ $employee->department->name }}
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4 fw-bold">
                                    <i class="fas fa-clock text-muted me-2"></i> Shift:
                                </div>
                                <div class="col-sm-8">
                                    {{ $employee->shift->name }} ({{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }})
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4 fw-bold">
                                    <i class="fas fa-phone text-muted me-2"></i> Phone:
                                </div>
                                <div class="col-sm-8">
                                    {{ $employee->phone ?? 'Not provided' }}
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4 fw-bold">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i> Address:
                                </div>
                                <div class="col-sm-8">
                                    {{ $employee->address ?? 'Not provided' }}
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-4 fw-bold">
                                    <i class="fas fa-calendar text-muted me-2"></i> Date Hired:
                                </div>
                                <div class="col-sm-8">
                                    {{ $employee->date_hired->format('F d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Update Personal Information</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('profile.update') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $employee->phone }}">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3">{{ $employee->address }}</textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Information
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Change Password</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('password.change') }}" method="POST">
                                        @csrf
                                        
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadPhotoModalLabel">Upload Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Select Image</label>
                        <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
                        <small class="form-text text-muted">
                            Recommended size: 300x300 pixels. Maximum size: 2MB.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection