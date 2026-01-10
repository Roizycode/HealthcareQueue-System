<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->last_login_at = now();
            $user->save();

            // Redirect based on role
            return match($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'staff' => redirect()->intended(route('staff.dashboard')),
                'patient' => redirect()->intended(route('patient.dashboard')),
                default => redirect()->intended(route('home')),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['nullable', 'string', 'size:13', 'regex:/^\+639\d{9}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'phone.regex' => 'Phone number must start with +639 and contain 13 characters.',
            'phone.size' => 'Phone number must be exactly 13 characters including +63.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'patient',
        ]);

        // Create linked patient record
        $names = explode(' ', $validated['name'], 2);
        $patient = \App\Models\Patient::create([
            'user_id' => $user->id,
            'first_name' => $names[0],
            'last_name' => $names[1] ?? '',
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // Send welcome email notification
        try {
            $user->notify(new \App\Notifications\PatientRegistered($patient));
        } catch (\Exception $e) {
            \Log::error('Failed to send patient registration email: ' . $e->getMessage());
        }

        // Auto-login the user
        Auth::login($user);

        // Redirect to dashboard with success message
        return redirect()->route('patient.dashboard')->with('success', 'Account created successfully! Welcome to your dashboard.');
    }

    /**
     * Show admin login form
     */
    public function showAdminLogin(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user is admin
            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->with('error', 'Access denied. Admin credentials required.')->onlyInput('email');
            }

            $request->session()->regenerate();
            $user->last_login_at = now();
            $user->save();

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show patient login form
     */
    public function showPatientLogin(): View
    {
        return view('auth.patient-login');
    }

    /**
     * Handle patient login
     */
    public function patientLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user is a patient
            if ($user->role !== 'patient') {
                Auth::logout();
                return back()->with('error', 'This login is for patients only. Please use staff login.')->onlyInput('email');
            }

            $request->session()->regenerate();
            $user->last_login_at = now();
            $user->save();

            return redirect()->route('patient.dashboard')->with('success', 'You have successfully logged in!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $role = Auth::user()?->role;
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect based on role
        if ($role === 'patient') {
            return redirect()->route('patient.login');
        }
        
        return redirect()->route('login');
    }
}
