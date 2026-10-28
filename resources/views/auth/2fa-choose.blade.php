
@extends('layouts.app')

@section('pageTitle', 'ศูนย์รับข้อร้องเรียนและข้อชมเชย กรมอนามัย')

@section('content')

<div class="py-8 px-5 md:px-8 mb-[80px] mx-4 md:mx-8 lg:mx-16 2xl:mx-[326px]
            rounded-3xl border border-slate-100 bg-white shadow-soft">

    <div class="max-w-md mx-auto">

        <h2 class="text-center text-xl md:text-2xl text-brand-600 font-bold mb-3">
            เลือกวิธีการยืนยันตัวตน 2 ขั้นตอน
        </h2>

        <p class="text-center text-sm text-gray-500 mb-8">
            กรุณาเลือกวิธีที่ต้องการใช้ในการยืนยันตัวตน
        </p>

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-red-50 text-red-600 text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.select') }}">
            @csrf

            <div class="space-y-4">

                <!-- Email OTP -->
                <label class="flex items-center gap-4 p-4 border border-gray-200
                              rounded-xl cursor-pointer hover:border-brand-600
                              hover:bg-gray-50 transition">

                    <input
                        type="radio"
                        name="method"
                        value="email"
                        class="w-5 h-5 accent-brand-600"
                        {{ old('method', 'email') === 'email' ? 'checked' : '' }}
                    >

                    <div>
                        <div class="font-semibold text-gray-800">
                            <i class="fas fa-envelope mr-2 text-brand-600"></i>
                            ยืนยันผ่าน Email
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            รับรหัส OTP 6 หลักผ่านอีเมล
                        </p>
                    </div>
                </label>

                <!-- Google Authenticator -->
                @if ($user->two_factor_enabled && $user->two_factor_confirmed_at)
                    <label class="flex items-center gap-4 p-4 border border-gray-200
                                  rounded-xl cursor-pointer hover:border-brand-600
                                  hover:bg-gray-50 transition">

                        <input
                            type="radio"
                            name="method"
                            value="authenticator"
                            class="w-5 h-5 accent-brand-600"
                            {{ old('method') === 'authenticator' ? 'checked' : '' }}
                        >

                        <div>
                            <div class="font-semibold text-gray-800">
                                <i class="fas fa-shield-alt mr-2 text-brand-600"></i>
                                Google Authenticator
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                ใช้รหัส OTP จากแอป Google Authenticator
                            </p>
                        </div>
                    </label>
                @endif

            </div>

            <!-- ปุ่มอยู่กึ่งกลาง -->
            <div class="flex justify-center mt-8">
                <button
                    type="submit"
                    class="w-full sm:w-56 px-7 py-3.5 text-white font-semibold
                           bg-brand-600 border border-brand-600 rounded-lg
                           hover:opacity-90 transition cursor-pointer">
                    ดำเนินการต่อ
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
