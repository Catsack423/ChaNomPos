<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PosChaNom</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">


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
