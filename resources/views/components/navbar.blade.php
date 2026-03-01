<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href=" {{ asset('css/global.css') }}">
    <link rel="stylesheet" href=" {{ asset('css/navbar.css') }}">
</head>

<body>
    <div class="nav">

        <a
            href="
             @if (auth()->check() && auth()->user()->admin) {{ route('admindashboard') }}
               
            @else   
                    {{ route('dashboard') }} @endif
        
        ">
            <div class="brand">
                <div class="logo">
                    <img id="logopic" src="{{ asset('img/logo.png') }}" alt="eror">
                </div>
                <div>
                    <div>PosChaNom</div>
                </div>
            </div>
        </a>
        <div style="flex: 1;" class="spae"></div>
        <div class="tabs" role="tablist" aria-label="Sections">

            @if (auth()->check() && auth()->user()->admin)
                <a href="{{ route('admindashboard') }}"><button class="tab active"
                        data-target="admin">Admin</button></a>
            @else
                <a href="{{ route('dashboard') }}"><button class="tab active" data-target="user">Staff</button></a>
            @endif



            <form method="POST" action="{{ route('logout') }}" style="display: contents;">
                @csrf
                <button type="submit" class="tab">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</body>

</html>
