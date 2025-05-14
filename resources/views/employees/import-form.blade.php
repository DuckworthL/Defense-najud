@extends('layouts.app')

@section('title', 'Import Employees')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Import Employees from CSV/Excel</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle"></i> Instructions</h5>
                        <p>Upload a CSV or Excel file with employee data. The file should have the following columns:</p>
                        <ul>
                            <li><strong>first_name</strong> - Required</li>
                            <li><strong>last_name</strong> - Required</li>
                            <li><strong>email</strong> - Required, must be unique</li>
                            <li><strong>department</strong> - Required, department name (will be created if it doesn't exist)</li>
                            <li><strong>role</strong> - Optional, defaults to "Employee" if not provided</li>
                            <li><strong>shift</strong> - Optional, defaults to first available shift if not provided</li>
                            <li><strong>employee_id</strong> - Optional, will be auto-generated if not provided</li>
                            <li><strong>password</strong> - Optional, defaults to "Password123!" if not provided</li>
                            <li><strong>phone</strong> - Optional</li>
                            <li><strong>address</strong> - Optional</li>
                            <li><strong>date_hired</strong> - Optional, defaults to today if not provided</li>
                            <li><strong>status</strong> - Optional, defaults to "active" if not provided</li>
                        </ul>
                    </div>
                    
                    <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="file" class="form-label">Select File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required accept=".csv, .xlsx, .xls">
                            <div class="form-text">
                                Maximum file size: 10MB<br>
                                Accepted formats: CSV (.csv), Excel (.xlsx, .xls)
                            </div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="header_row" name="header_row" checked>
                                <label class="form-check-label" for="header_row">
                                    First row contains column headings
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Employee List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-import"></i> Import Employees
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Sample File Format</h5>
                </div>
                <div class="card-body">
                    <p>Download a sample template to ensure your data is formatted correctly:</p>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Download Sample CSV
                    </a>
                    <a href="#" class="btn btn-outline-success ms-2">
                        <i class="fas fa-download"></i> Download Sample Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection