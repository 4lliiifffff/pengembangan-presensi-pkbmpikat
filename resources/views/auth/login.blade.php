<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Smart Presensi</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.jpeg') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="manifest" href="__manifest.json">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #bdefff;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .login-illustration {
            background: #7fdcff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px 80px;
            border-radius: 24px;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.12);
        }

        .login-illustration img {
            max-width: 100%;
            height: auto;
        }

        .login-illustration-title {
            margin-top: 24px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            color: #222;
            line-height: 1.4;
        }

        .login-form-panel {
            margin-top: -60px;
            padding: 32px 28px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
        }

        .login-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 32px;
            text-align: center;
            color: #222;
            line-height: 1.4;
        }

        .login-label {
            font-size: 14px;
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
        }

        .login-input {
            width: 100%;
            padding: 10px 5px;
            border-radius: 8px;
            border: 1px solid #ffd3b6;
            box-shadow: 0 0 0 1px rgba(255, 140, 95, 0.1);
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }

        .login-input:focus {
            border-color: #ff8c5f;
            box-shadow: 0 0 0 1px rgba(255, 140, 95, 0.35);
            background-color: #fffaf7;
        }

        .login-field {
            margin-bottom: 18px;
        }

        .login-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .forgot-link {
            color: #ff8c5f;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            background: #0067ff;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 8px 18px rgba(0, 103, 255, 0.35);
        }

        .login-button:hover {
            background: #0054d0;
        }

        .login-button:active {
            transform: translateY(1px);
            box-shadow: 0 4px 10px rgba(0, 103, 255, 0.35);
        }

        .login-alert {
            margin-bottom: 16px;
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #ffe58f;
            background: #fffbe6;
            color: #ad6800;
            font-size: 13px;
        }

        .login-alert-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .login-alert-success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        @media (max-width: 480px) {
            .login-card {
                max-width: 360px;
            }

            .login-illustration {
                padding: 32px 20px 72px;
            }

            .login-form-panel {
                margin-top: -52px;
                padding: 28px 20px 24px;
            }

            .login-title {
                font-size: 20px;
            }
        }
    </style>
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
