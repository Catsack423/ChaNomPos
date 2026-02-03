<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href=" {{ asset('css/global.css') }}">
    <link rel="stylesheet" href=" {{ asset('css/navbar.css') }}">
    <div class="nav">
        <a href="{{route('dashboard')}}"><div class="brand" >
            <div class="logo">
                <img id="logopic" src="{{ asset('img/logo.png') }}" alt="eror">
            </div>
            <div>
                <div >PosChaNom</div>
            </div>
        </div>
        </a>
        <div style="flex: 1;"></div>
        <div class="tabs" role="tablist" aria-label="Sections">
            <form method="POST" action="{{ route('logout') }}" x-data onsubmit="return confirm('ต้องการออกจากระบบใช่หรือไม่')">
                @csrf
                <button type="submit" class="tab" >
                    {{ __('Log Out') }}
                </button>
            </form>

            @if (auth()->check() && auth()->user()->admin)
                @if (Route::is('adminmenu') || Route::is('adminstock') || Route::is('admindashboard'))
                    <a href="{{route('admindashboard')}}"><button class="tab active" data-target="admin">Admin</button></a>
                @else
                    <a href="{{route('admindashboard')}}"><button class="tab" data-target="admin">Admin</button></a>
                @endif
            @endif

            @if (Route::is('adminmenu') || Route::is('adminstock') || Route::is('admindashboard'))
                    <a href="{{route('dashboard')}}"><button class="tab" data-target="user">Staff</button></a>
            @else
                    <a href="{{route('dashboard')}}"><button class="tab active" data-target="user">Staff</button></a>
            @endif
            

        </div>
    </div>
</head>

</html>
