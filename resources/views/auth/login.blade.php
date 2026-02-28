<x-guest-layout>
    <div class="text-center mb-12">
        <h1 class="text-5xl font-bold" style="color: #4A3427; letter-spacing: -1px;">PosChaNom</h1>
        <p class="text-gray-500 mt-3 text-lg">กรุณาเข้าสู่ระบบเพื่อใช้งาน</p>
    </div>

    <div class="login-card">
        <x-validation-errors class="mb-6" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label>อีเมลพนักงาน</label>
                    <input id="email" class="input-pos focus:outline-none" type="email" name="email" :value="old('email')" required autofocus placeholder="example@mail.com" />
                </div>

                <div>
                    <label>รหัสผ่าน</label>
                    <input id="password" class="input-pos focus:outline-none" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                </div>
            </div>

            <div class="flex items-center justify-between mt-8 text-sm px-1">
                <label for="remember_me" class="flex items-center cursor-pointer">
                    <x-checkbox id="remember_me" name="remember" class="rounded text-[#7A533E] focus:ring-[#7A533E]" />
                    <span class="ms-2 text-gray-600">จดจำฉันไว้</span>
                </label>
                
                <a class="font-bold text-[#7A533E] hover:underline" href="{{ route('register') }}">
                    สมัครสมาชิกใหม่
                </a>
            </div>

            <div class="mt-10">
                <button class="btn-brown text-lg font-bold shadow-md">
                    เข้าสู่ระบบ
                </button>
            </div>
        </form>
    </div>
    
    <p class="mt-10 text-gray-400 text-sm">© 2024 POSCHANOM SYSTEM</p>
</x-guest-layout>