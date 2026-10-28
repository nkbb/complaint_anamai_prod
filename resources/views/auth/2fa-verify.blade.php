
@extends('layouts.app')

@section('pageTitle', 'ศูนย์รับข้อร้องเรียนและข้อชมเชย กรมอนามัย')

@section('content')

<div class="py-8 px-5 md:px-8 mb-[80px] mx-4 md:mx-8 lg:mx-16 2xl:mx-[326px]
            rounded-3xl border border-slate-100 bg-white shadow-soft">

    <div class="max-w-md mx-auto">

        <!-- Icon -->
        <div class="flex justify-center mb-5">
            <div class="w-16 h-16 rounded-full bg-blue-50
                        flex items-center justify-center">
                @if ($method === 'email')
                    <i class="fas fa-envelope-open-text text-3xl text-brand-600"></i>
                @else
                    <i class="fas fa-shield-alt text-3xl text-brand-600"></i>
                @endif
            </div>
        </div>

        <!-- Title -->
        <h2 class="text-center text-xl md:text-2xl
                   text-brand-600 font-bold mb-3">
            ยืนยันตัวตนด้วยรหัส OTP
        </h2>

        <!-- Description -->
        <p class="text-center text-sm text-gray-500 mb-8">
            @if ($method === 'email')
                กรุณากรอกรหัส OTP 6 หลักที่ส่งไปยังอีเมลของคุณ
            @else
                กรุณากรอกรหัส 6 หลักจากแอป Google Authenticator
            @endif
        </p>

        <!-- Success -->
        @if (session('success'))
            <div class="mb-5 p-4 rounded-lg bg-green-50
                        text-green-700 text-sm text-center">
                <i class="fas fa-check-circle mr-1"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Error -->
        @if ($errors->any())
            <div class="mb-5 p-4 rounded-lg bg-red-50
                        text-red-600 text-sm text-center">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Verify OTP -->
        <form method="POST" action="{{ route('2fa.verify') }}">
            @csrf

            <div class="mb-6">
                <label for="otp"
                       class="block text-sm font-semibold text-gray-700 mb-2 text-center">
                    รหัสยืนยัน 6 หลัก
                </label>

                <input
                    id="otp"
                    type="text"
                    name="otp"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    value="{{ old('otp') }}"
                    autofocus
                    required
                    class="w-full px-4 py-3.5 text-center text-2xl
                           font-semibold tracking-[0.5em]
                           border border-gray-300 rounded-xl
                           focus:outline-none focus:ring-2
                           focus:ring-brand-600 focus:border-brand-600
                           transition"
                >

                <p class="text-center text-xs text-gray-400 mt-3">
                    @if ($method === 'email')
                        รหัส OTP มีอายุ 5 นาที
                    @else
                        รหัสจาก Google Authenticator เปลี่ยนทุก 30 วินาที
                    @endif
                </p>
            </div>

            <!-- Verify Button -->
            <div class="flex justify-center">
                <button
                    type="submit"
                    class="w-full sm:w-56 px-7 py-3.5
                           bg-brand-600 border border-brand-600
                           text-white font-semibold rounded-lg
                           hover:opacity-90 transition cursor-pointer">
                    <i class="fas fa-check-circle mr-2"></i>
                    ยืนยัน OTP
                </button>
            </div>
        </form>

        <!-- Resend Email OTP -->
        @if ($method === 'email')
            <div class="text-center mt-7">
                <p class="text-sm text-gray-500 mb-2">
                    ยังไม่ได้รับรหัส OTP?
                </p>

                <form method="POST" action="{{ route('2fa.resend') }}">
                    @csrf

                    <button
                        type="submit"
                        class="text-brand-600 font-semibold text-sm
                               hover:underline cursor-pointer">
                        <i class="fas fa-redo-alt mr-1"></i>
                        ส่ง OTP อีกครั้ง
                    </button>
                </form>
            </div>
        @endif

        <!-- Back -->
        <div class="text-center mt-8 pt-5 border-t border-gray-100">
            <a href="{{ route('2fa.choose') }}"
               class="inline-flex items-center gap-2 text-sm
                      text-gray-500 hover:text-brand-600 transition">
                <i class="fas fa-arrow-left"></i>
                เปลี่ยนวิธียืนยันตัวตน
            </a>
        </div>

    </div>
</div>

@endsection
