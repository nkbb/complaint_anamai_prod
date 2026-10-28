<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GoogleAuthenticatorController extends Controller
{
    public function setup(Request $request)
{
    $user = $request->user();

    if ($user->two_factor_enabled) {
        return redirect('/dashboard');
    }

    $google2fa = new \PragmaRX\Google2FA\Google2FA();

    // สร้าง Secret เฉพาะครั้งแรก
    if (empty($user->two_factor_secret)) {
        $user->two_factor_secret = $google2fa->generateSecretKey();
        $user->save();
    }

    // สร้าง TOTP URL มาตรฐาน
    $appName = 'Anamai Complaint System';

    $qrUrl = $google2fa->getQRCodeUrl(
        $appName,
        $user->email,
        $user->two_factor_secret
    );

    // สร้าง QR Code
    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(300)
        ->margin(4)
        ->errorCorrection('M')
        ->generate($qrUrl);

    return view('auth.google-setup', [
        'qrCode' => $qrCode,
        'secretKey' => $user->two_factor_secret,
    ]);
}

    public function confirm(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return redirect()->route('google.setup');
        }

        $key = 'google-setup:' . $user->id;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'otp' => 'ลองผิดหลายครั้ง กรุณารอ 5 นาที',
            ]);
        }

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            $user->two_factor_secret,
            $request->otp
        );

        if (!$valid) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 300);

            return back()->withErrors([
                'otp' => 'รหัส Google Authenticator ไม่ถูกต้อง',
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($key);

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect('/dashboard')
            ->with('success', 'เปิด Google Authenticator สำเร็จ');
    }
}