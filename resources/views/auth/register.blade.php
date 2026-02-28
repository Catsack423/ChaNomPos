<x-guest-layout>
    <div class="text-center mb-12">
        <h1 class="text-5xl font-bold" style="color: #4A3427; letter-spacing: -1px;">PosChaNom</h1>
        <p class="text-gray-500 mt-3 text-lg">สร้างบัญชีพนักงานใหม่เพื่อเริ่มใช้งาน</p>
    </div>

    <div class="login-card mx-auto">
        <x-validation-errors class="mb-6" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label>ชื่อ-นามสกุล</label>
                    <input id="name" class="input-pos focus:outline-none" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="ระบุชื่อพนักงาน" />
                </div>

                <div>
                    <label>อีเมล (Email)</label>
                    <input id="email" class="input-pos focus:outline-none" type="email" name="email" :value="old('email')" required placeholder="example@mail.com" />
                </div>

                <div>
                    <label>รหัสผ่าน</label>
                    <input id="password" class="input-pos focus:outline-none" type="password" name="password" required placeholder="ระบุรหัสผ่าน" />
                </div>

                <div>
                    <label>ยืนยันรหัสผ่านอีกครั้ง</label>
                    <input id="password_confirmation" class="input-pos focus:outline-none" type="password" name="password_confirmation" required placeholder="ยืนยันรหัสผ่านอีกครั้ง" />
                </div>
            </div>

            <div class="mt-10">
                <button class="btn-brown text-lg font-bold shadow-md">
                    ยืนยันการสมัครพนักงาน
                </button>
            </div>

            <div class="mt-8 text-center border-t pt-6 border-gray-100">
                <a class="text-sm text-gray-500 hover:text-[#4A3427] transition" href="{{ route('login') }}">
                    มีบัญชีอยู่แล้ว? <span class="font-bold text-[#7A533E] underline decoration-2 underline-offset-4">เข้าสู่ระบบที่นี่</span>
                </a>
            </div>
        </form>
    </div>

    <p class="mt-10 text-gray-400 text-sm text-center font-medium uppercase tracking-widest">© 2024 POSCHANOM SYSTEM</p>
</x-guest-layout>