<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PatientVerificationCode;
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
     * Handle registration - Send verification code instead of auto-login
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
            'is_verified' => false,
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

        // Generate verification code and send email
        $code = $user->generateVerificationCode();
        
        try {
            $user->notify(new PatientVerificationCode($code));
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        // Redirect to verification page with email in URL
        return redirect()->route('patient.verify.show', ['email' => $validated['email']]);
    }

    /**
     * Show verification code entry form
     */
    public function showVerify(Request $request): View
    {
        $email = $request->query('email') ?? session('pending_verification_email');
        
        if (!$email) {
            return redirect()->route('patient.login')
                ->with('error', 'No pending verification found. Please login or register.');
        }
        
        // Verify user exists
        $user = User::where('email', $email)->where('role', 'patient')->first();
        if (!$user) {
            return redirect()->route('patient.login')
                ->with('error', 'Invalid verification request.');
        }
        
        return view('auth.verify-code', compact('email'));
    }

    /**
     * Handle verification code submission
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if ($user->is_verified) {
            session()->forget('pending_verification_email');
            return redirect()->route('patient.login')
                ->with('success', 'Your account is already verified. Please login.');
        }

        if (!$user->isVerificationCodeValid($validated['code'])) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.'])->withInput();
        }

        // Mark as verified
        $user->markAsVerified();
        
        // Clear session
        session()->forget('pending_verification_email');

        // Auto-login the user
        Auth::login($user);
        $request->session()->regenerate();

        // Send welcome notification
        try {
            $user->notify(new \App\Notifications\PatientRegistered($user->patient));
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return redirect()->route('patient.dashboard')
            ->with('success', 'Email verified successfully! Welcome to your dashboard.');
    }

    /**
     * Resend verification code
     */
    public function resendCode(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return redirect()->route('patient.login')
                ->with('error', 'Email address is required.');
        }

        $user = User::where('email', $email)->where('role', 'patient')->first();

        if (!$user) {
            return redirect()->route('patient.verify.show', ['email' => $email])
                ->with('error', 'No patient account found with this email.');
        }

        if ($user->is_verified) {
            return redirect()->route('patient.login')
                ->with('info', 'Your account is already verified. Please login.');
        }

        // Generate new code and send
        $code = $user->generateVerificationCode();
        
        try {
            $user->notify(new PatientVerificationCode($code));
            return redirect()->route('patient.verify.show', ['email' => $email])
                ->with('success', 'A new verification code has been sent to your email.');
        } catch (\Exception $e) {
            \Log::error('Failed to resend verification email: ' . $e->getMessage());
            return redirect()->route('patient.verify.show', ['email' => $email])
                ->with('error', 'Failed to send verification code. Please try again.');
        }
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

            // Check if verified
            if (!$user->is_verified) {
                Auth::logout();
                
                // Resend code if expired
                if (!$user->verification_code_expires_at || $user->verification_code_expires_at->isPast()) {
                    $code = $user->generateVerificationCode();
                    try {
                        $user->notify(new PatientVerificationCode($code));
                    } catch (\Exception $e) {
                        \Log::error('Failed to resend verification email on login: ' . $e->getMessage());
                    }
                }
                
                return redirect()->route('patient.verify.show', ['email' => $user->email])
                    ->with('warning', 'Your account is not verified. Please enter the verification code sent to your email.');
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
     * Show change password form (for patients)
     */
    public function showChangePassword(): View
    {
        return view('patient.change-password');
    }

    /**
     * Handle password change (for patients)
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('patient.profile')
                ->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('patient.profile')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Show forgot password form (for patients)
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password (send reset code)
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->where('role', 'patient')->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No patient account found with this email.']);
        }

        // Generate reset code (reusing verification code mechanism)
        $code = $user->generateVerificationCode();
        
        try {
            $user->notify(new PatientVerificationCode($code));
            session(['reset_password_email' => $validated['email']]);
            session()->save();
            return redirect()->route('patient.reset-password.show')
                ->with('success', 'A password reset code has been sent to your email.');
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send reset code. Please try again.']);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(): View
    {
        $email = session('reset_password_email');
        
        if (!$email) {
            return redirect()->route('patient.forgot-password')
                ->with('error', 'Please request a password reset first.');
        }
        
        return view('auth.reset-password', compact('email'));
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->where('role', 'patient')->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if (!$user->isVerificationCodeValid($validated['code'])) {
            return back()->withErrors(['code' => 'Invalid or expired reset code.'])->withInput();
        }

        // Update password and clear code
        $user->password = Hash::make($validated['password']);
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();

        session()->forget('reset_password_email');

        return redirect()->route('patient.login')
            ->with('success', 'Password reset successfully! Please login with your new password.');
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
