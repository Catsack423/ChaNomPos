<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PosChaNom</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=prompt:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { 
            font-family: 'Prompt', sans-serif; 
            background-color: #FFF0E0; /* พื้นหลังครีมอ่อนตามรูป Staff */
        }
        .login-card {
            background-color: white;
            border-radius: 3rem; /* โค้งมนมากตามสไตล์ปุ่ม Admin */
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 480px; /* ล็อคความกว้างไม่ให้เต็มจอ */
            padding: 3rem;
        }
        .btn-brown {
            background-color: #7A533E;
            color: white;
            border-radius: 1.5rem;
            transition: all 0.3s ease;
        }
        .btn-brown:hover {
            background-color: #5D3F2F;
            transform: translateY(-2px);
        }
        .input-pos {
            background-color: #F9F9F9;
            border: 1px solid #E5E7EB;
            border-radius: 1.25rem;
            padding: 0.75rem 1.25rem;
        }
        .input-pos:focus {
            border-color: #7A533E;
            ring: 0;
            box-shadow: 0 0 0 2px rgba(122, 83, 62, 0.1);
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center p-6">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>