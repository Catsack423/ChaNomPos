<!DOCTYPE html>
<html lang="th">

<head>


    <link rel="preload" href="{{ asset('fonts/Prompt-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>

    <link rel="preload" as="image" href="{{ asset('img/logo.png') }}" fetchpriority="high">
    <link rel="preload" href="{{ asset('fonts/Prompt-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Prompt-Medium.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Prompt-Light.woff2') }}" as="font" type="font/woff2" crossorigin>




    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PosChaNom</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>
    <script>
        // ตรวจสอบ Success Session
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: "{{ session('success') }}",
                timer: 3000, // ปิดอัตโนมัติใน 3 วินาที
                showConfirmButton: false
            });
        @endif

        // ตรวจสอบ Error Session
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: "{{ session('error') }}",
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#d33'
            });
        @endif
    </script>
    <x-navbar />
    <div class="min-h-screen">
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')

</body>

</html>
