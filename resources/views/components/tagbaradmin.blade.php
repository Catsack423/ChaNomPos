<body>
    <link rel="stylesheet" href="{{ asset('css/tagbar.css') }}">
    <div class="actions">
        @if (Route::is('admindashboard'))
            <a href="{{ route('admindashboard') }}" class='btn1 disable'>สรุปยอด</a>
        @else
            <a href="{{ route('admindashboard') }}" class='btn1 act'>สรุปยอด</a>
        @endif


        @if (Route::is('adminmenu'))
            <a href="{{ route('adminmenu') }}" class='btn1 disable'>จัดการเมนู</a>
        @else
            <a href="{{ route('adminmenu') }}" class='btn1  act'>จัดการเมนู</a>
        @endif

        @if (Route::is('adminstock'))
            <a href="{{ route('adminstock') }}" class='btn1 disable'>จัดการสต็อค</a>
        @else
            <a href="{{ route('adminstock') }}" class='btn1   act'>จัดการสต็อค</a>
        @endif

    </div>
</body>
