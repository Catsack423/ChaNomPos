<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="preload" as="image" href="{{ asset('img/logo.png') }}" fetchpriority="high">
    <link rel="stylesheet" href=" {{ asset('css/global.css') }}">
    <link rel="stylesheet" href=" {{ asset('css/navbar.css') }}">
</head>
<style>
    #logopic {
        /* กำหนดขนาดสูงสุดที่ยอมรับได้ */
        width: 100%;
        max-width: 60px;
        /* ปรับขนาดตามต้องการ */
        height: auto;
        /* ให้ความสูงปรับตามอัตราส่วนของรูปจริง */

        /* กรณีต้องการล็อคขนาดพื้นที่แต่ไม่ให้รูปเบี้ยว */
        aspect-ratio: 1 / 1;
        object-fit: cover;

        border-radius: 15%;
        margin-left: 5%;
        /* ปรับ margin เป็น % เพื่อให้ dynamic */
    }
</style>

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
                    <img id="logopic" loading="eager" fetchpriority="high"src="{{ asset('img/logo.png') }}"
                        alt="eror">
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
