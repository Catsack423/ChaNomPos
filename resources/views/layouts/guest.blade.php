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
            background-color: #FAF3EA; /* ปรับครีมให้สว่างและนวลขึ้น */
            color: #4A3427;
        }
        .login-card {
            background-color: white;
            border-radius: 2.5rem; 
            box-shadow: 0 15px 35px rgba(74, 52, 39, 0.08);
            width: 100%;
            max-width: 460px; 
            padding: 3.5rem 2.5rem; /* เพิ่มพื้นที่หายใจรอบการ์ด */
        }
        /* แก้ตัวหนังสือชิดกล่อง: เพิ่ม Padding ด้านใน */
        .input-pos {
            background-color: #FDFDFD;
            border: 1.5px solid #E5E7EB;
            border-radius: 1rem;
            padding: 0.9rem 1.25rem; /* เพิ่มความสูงและระยะซ้ายขวา */
            width: 100%;
            transition: all 0.2s ease;
        }
        .input-pos:focus {
            border-color: #7A533E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(122, 83, 62, 0.1);
        }
        .btn-brown {
            background-color: #7A533E;
            color: white;
            border-radius: 1.25rem;
            padding: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            cursor: pointer;
            border: none;
        }
        .btn-brown:hover {
            background-color: #5D3F2F;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(122, 83, 62, 0.2);
        }
        /* จัดการระยะห่าง Label */
        label {
            display: block;
            margin-bottom: 0.6rem !important; /* ห่างจากกล่อง input พอดีๆ */
            font-weight: 500;
            font-size: 0.95rem;
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