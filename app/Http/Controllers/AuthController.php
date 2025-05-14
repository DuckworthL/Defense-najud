<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $remember = $request->has('remember');
        
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 'active'], $remember)) {
            $request->session()->regenerate();
            
            // Log successful login
            Log::create([
                'employee_id' => Auth::id(),
                'action' => 'login',
                'description' => 'User logged in successfully',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Redirect based on role
            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->isHR()) {
                return redirect()->intended(route('hr.dashboard'));
            } else {
                return redirect()->intended(route('employee.dashboard'));
            }
        }
        
        // Track failed login attempts
        $this->incrementLoginAttempts($request);
        
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
    
    // Handle logout
    public function logout(Request $request)
    {
        // Log logout action
        if (Auth::check()) {
            Log::create([
                'employee_id' => Auth::id(),
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    // Track login attempts
    private function incrementLoginAttempts(Request $request)
    {
        $key = 'login_attempts_' . $request->ip();
        $attempts = session($key, 0);
        session([$key => $attempts + 1]);
        
        // If too many attempts, log it
        if ($attempts >= 5) {
            Log::create([
                'employee_id' => null,
                'action' => 'login_attempt_exceeded',
                'description' => 'Too many failed login attempts for email: ' . $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }
}