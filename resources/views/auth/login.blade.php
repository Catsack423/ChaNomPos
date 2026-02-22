<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold" style="color: #4A3427;">PosChaNom</h1>
        <p class="text-gray-500 mt-2">กรุณาเข้าสู่ระบบเพื่อใช้งาน</p>
    </div>

    <div class="login-card">
        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-1 ml-2" style="color: #4A3427;">อีเมลพนักงาน</label>
                    <input id="email" class="input-pos block w-full focus:outline-none" type="email" name="email" :value="old('email')" required autofocus />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 ml-2" style="color: #4A3427;">รหัสผ่าน</label>
                    <input id="password" class="input-pos block w-full focus:outline-none" type="password" name="password" required autocomplete="current-password" />
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 text-sm px-2">
                <label for="remember_me" class="flex items-center cursor-pointer">
                    <x-checkbox id="remember_me" name="remember" class="rounded text-[#7A533E] focus:ring-[#7A533E]" />
                    <span class="ms-2 text-gray-600">จดจำฉันไว้</span>
                </label>
                
                <a class="font-semibold text-[#7A533E] hover:underline" href="{{ route('register') }}">
                    สมัครสมาชิกใหม่
                </a>
            </div>

            <div class="mt-8">
                <button class="w-full btn-brown py-4 text-lg font-bold shadow-md">
                    เข้าสู่ระบบ
                </button>
            </div>
        </form>
    </div>
    
    <p class="mt-8 text-gray-400 text-sm">© 2024 POSCHANOM SYSTEM</p>
</x-guest-layout>