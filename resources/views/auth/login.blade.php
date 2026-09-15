<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Smart Presensi</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-illustration">
                <img src="{{ asset('assets/img/login.jpg') }}" alt="Login Illustration">
            </div>

            <div class="login-form-panel">
                <div class="login-title">
                    PKBM Pintar Berbakat<br>
                    Homeschooling Bandung
                </div>

                @if (session('warning'))
                    <div class="login-alert">
                        {{ session('warning') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="login-alert login-alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="login-alert login-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login.process') }}" method="POST" autocomplete="on" id="loginForm">
                    @csrf

                    <div class="login-field">
                        <label for="username" class="login-label">Username or NIK</label>
                        <input type="text" name="username" id="username" class="login-input"
                            placeholder="Masukkan username atau NIK" value="{{ old('username') }}"
                            autocomplete="username" inputmode="text" enterkeyhint="next" autofocus>
                    </div>

                    <div class="login-field">
                        <label for="password" class="login-label">Password</label>
                        <input type="password" name="password" id="password" class="login-input"
                            placeholder="Masukkan password" autocomplete="current-password" enterkeyhint="go">
                    </div>

                    <div class="login-footer">
                        <span></span>
                        <a href="https://wa.me/6285156452939" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-button">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>



    <!-- ///////////// Js Files ////////////////////  -->
    <!-- Jquery -->
    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <!-- Bootstrap-->
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"></script>
    <!-- Owl Carousel -->
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <!-- jQuery Circle Progress -->
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <!-- Base Js File -->
    <script src="{{ asset('assets/js/base.js') }}"></script>

    <script>
        (function() {
            // Enter di field username → pindah fokus ke password
            document.getElementById('username').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('password').focus();
                }
            });

            // Enter di field password → submit form
            document.getElementById('password').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('loginForm').submit();
                }
            });

            // Scroll agar form tetap terlihat saat keyboard mobile muncul
            var inputs = document.querySelectorAll('.login-input');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    setTimeout(function() {
                        input.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 300);
                });
            });
        })();
    </script>


</body>

</html>
