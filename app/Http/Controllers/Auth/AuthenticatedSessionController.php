<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:' . sha1(
            strtolower($credentials['email']) . '|' . $request->ip()
        );

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'เข้าสู่ระบบผิดหลายครั้ง กรุณารอ 5 นาที',
            ]);
        }

        $user = User::where('email', $credentials['email'])
            ->first();

        if (!$user || !Hash::check(
            $credentials['password'],
            $user->password
        )) {
            RateLimiter::hit($key, 300);

            throw ValidationException::withMessages([
                'email' => 'Email หรือ Password ไม่ถูกต้อง',
            ]);
        }

        RateLimiter::clear($key);

        // ยังไม่ Login จนกว่า OTP จะถูกต้อง
        $request->session()->regenerate();

        $request->session()->put([
            '2fa_user_id' => $user->id,
            '2fa_started_at' => now()->timestamp,
        ]);

        $request->session()->forget('2fa_method');

        return redirect()->route('2fa.choose');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}