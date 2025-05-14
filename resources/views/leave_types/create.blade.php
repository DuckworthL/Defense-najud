@extends('layouts.app')

@section('title', 'Create Leave Type')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Leave Type</h1>
        <a href="{{ route('leave-types.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leave Types
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Leave Type Details</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('leave-types.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label>Is Paid Leave? <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_paid" id="is_paid_yes" value="1" {{ old('is_paid', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_paid_yes">
                            Yes - This leave type is paid
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_paid" id="is_paid_no" value="0" {{ old('is_paid') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_paid_no">
                            No - This leave type is unpaid
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active - This leave type is available for leave requests
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Create Leave Type
                </button>
                <a href="{{ route('leave-types.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection