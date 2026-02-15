<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PosChaNom</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bubble-tea.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminmenu.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body >
    <x-navbar />
    <div class="min-h-screen">
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
