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

            @if (auth()->check() && auth()->user()->is_admin)
              <button class="tab" data-target="admin">Admin</button>
            @endif
            <button class="tab active" data-target="user">Staff</button>

        </div>
    </div>
</head>

</html>
