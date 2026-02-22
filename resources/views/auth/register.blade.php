<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold" style="color: #4A3427;">PosChaNom</h1>
        <p class="text-gray-500 mt-2">สร้างบัญชีพนักงานใหม่เพื่อเริ่มใช้งาน</p>
    </div>

    <div class="login-card mx-auto">
        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 ml-2" style="color: #4A3427;">ชื่อ-นามสกุล</label>
                    <input id="name" class="input-pos block w-full focus:outline-none" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="ระบุชื่อพนักงาน" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 ml-2" style="color: #4A3427;">อีเมล (Email)</label>
                    <input id="email" class="input-pos block w-full focus:outline-none" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="example@mail.com" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 ml-2" style="color: #4A3427;">รหัสผ่าน</label>
                    <input id="password" class="input-pos block w-full focus:outline-none" type="password" name="password" required autocomplete="new-password" placeholder="ระบุรหัสผ่าน" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 ml-2" style="color: #4A3427;">ยืนยันรหัสผ่านอีกครั้ง</label>
                    <input id="password_confirmation" class="input-pos block w-full focus:outline-none" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="ยืนยันรหัสผ่านอีกครั้ง" />
                </div>
            </div>

            <div class="mt-8">
                <button class="w-full btn-brown py-4 text-lg font-bold shadow-md transition duration-200">
                    ยืนยันการสมัครพนักงาน
                </button>
            </div>

            <div class="mt-6 text-center">
                <a class="text-sm text-gray-500 hover:text-[#4A3427] transition" href="{{ route('login') }}">
                    มีบัญชีอยู่แล้ว? <span class="font-bold text-[#7A533E] underline decoration-2 underline-offset-4">เข้าสู่ระบบที่นี่</span>
                </a>
            </div>
        </form>
    </div>

    <p class="mt-8 text-gray-400 text-sm text-center">© 2024 POSCHANOM SYSTEM</p>
</x-guest-layout>