<?php

namespace App\Http\Controllers;

use App\Mail\LoginOtpMail;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('2fa_user_id');
        $started = $request->session()->get('2fa_started_at');

        if (!$id || !$started || now()->timestamp - $started > 600) {
            $request->session()->forget([
                '2fa_user_id',
                '2fa_started_at',
                '2fa_method',
            ]);
            return null;
        }

        return User::find($id);
    }

    public function choose(Request $request)
    {
        $user = $this->pendingUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth.2fa-choose', compact('user'));
    }

    public function selectMethod(Request $request)
    {
        $request->validate([
            'method' => 'required|in:email,authenticator',
        ]);

        $user = $this->pendingUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        if (
            $request->method === 'authenticator' &&
            (!$user->two_factor_enabled ||
             !$user->two_factor_confirmed_at ||
             !$user->two_factor_secret)
        ) {
            return back()->withErrors([
                'method' => 'ยังไม่ได้เปิด Google Authenticator',
            ]);
        }

        $request->session()->put('2fa_method', $request->method);

        if ($request->method === 'email') {
            return $this->sendEmailOtp($request);
        }

        return redirect()->route('2fa.verify.form');
    }

    public function sendEmailOtp(Request $request)
    {
        $user = $this->pendingUser($request);

        if (!$user || session('2fa_method') !== 'email') {
            return redirect()->route('login');
        }

        $key = 'send-otp:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors([
                'otp' => 'ส่ง OTP บ่อยเกินไป กรุณารอสักครู่',
            ]);
        }

        RateLimiter::hit($key, 300);

        $otp = (string) random_int(100000, 999999);

        LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        LoginOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)
            ->send(new LoginOtpMail($otp));

        return redirect()
            ->route('2fa.verify.form')
            ->with('success', 'ส่ง OTP ไปยัง Email แล้ว');
    }

    public function showVerify(Request $request)
    {
        $user = $this->pendingUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $method = session('2fa_method');

        if (!in_array($method, ['email', 'authenticator'], true)) {
            return redirect()->route('2fa.choose');
        }

        return view('auth.2fa-verify', compact('method'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $this->pendingUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $method = session('2fa_method');

        if (!in_array($method, ['email', 'authenticator'], true)) {
            return redirect()->route('2fa.choose');
        }

        $key = 'verify-otp:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'otp' => 'กรอกผิดเกินจำนวนครั้งที่กำหนด กรุณารอ 5 นาที',
            ]);
        }

        $valid = false;
        $loginOtp = null;

        if ($method === 'email') {

            $loginOtp = LoginOtp::where('user_id', $user->id)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            $valid = $loginOtp &&
                Hash::check($request->otp, $loginOtp->otp_hash);

        } elseif ($method === 'authenticator') {

            if (
                $user->two_factor_enabled &&
                $user->two_factor_confirmed_at &&
                $user->two_factor_secret
            ) {
                $google2fa = new Google2FA();

                $valid = $google2fa->verifyKey(
                    $user->two_factor_secret,
                    $request->otp
                );
            }
        }

        if (!$valid) {
            RateLimiter::hit($key, 300);

            return back()->withErrors([
                'otp' => 'OTP ไม่ถูกต้องหรือหมดอายุ',
            ]);
        }

        if ($method === 'email' && $loginOtp) {
            $loginOtp->update([
                'used_at' => now(),
            ]);
        }

        RateLimiter::clear($key);

        Auth::login($user);

        $request->session()->regenerate();

        $request->session()->forget([
            '2fa_user_id',
            '2fa_started_at',
            '2fa_method',
        ]);

        return redirect()->intended('/dashboard');
    }
}