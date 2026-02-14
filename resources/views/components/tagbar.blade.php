<body>
    <link rel="stylesheet" href="{{ asset('css/tagbar.css') }}">
    <div class="actions">
        @if (Route::is('dashboard'))
            <a href="{{ route('dashboard') }}" class='btn1 disable'>🛒 สั่งเมนู</a>
        @else
            <a href="{{ route('dashboard') }}" class='btn1 act'>🛒 สั่งเมนู</a>
        @endif


        @if (Route::is('orderhistory'))
            <a href="{{ route('orderhistory') }}" class='btn1 disable'>🧾 ประวัติการสั่งซื้อ</a>
        @else
            <a href="{{ route('orderhistory') }}" class='btn1  act'>🧾 ประวัติการสั่งซื้อ</a>
        @endif

        @if (Route::is('storeedit'))
            <a href="{{ route('storeedit') }}" class='btn1 disable'>⚙️ สต็อค</a>
        @else
            <a href="{{ route('storeedit') }}" class='btn1   act'>⚙️ สต็อค</a>
        @endif

    </div>
</body>
