<!DOCTYPE html>
<html>
@include('frontend.partials._head')

<body  @stack('body-data')>
    <audio id="myAudio1">
        <source src="{{asset('beep.mp3')}}" type="audio/mpeg">
    </audio>

    <div id="main-wrapper">
        @include('frontend.partials._nav')

        @yield('main-content')
        @includeUnless(request()->is(['login', 'register']), 'frontend.partials._footer')

    </div>
    @include('frontend.partials._scripts')

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/68cc82b0d9b855192574b04c/1j5fdt0b8';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
        })();
    </script>
    <!--End of Tawk.to Script-->

</body>

</html>
