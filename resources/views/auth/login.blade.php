<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ticketing System - Login</title>

    <!-- Main combined CSS containing typography & Tabler icons -->
    <link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}?v={{ time() }}">
    <!-- Custom login responsive styles -->
    <link rel="stylesheet" href="{{ asset('uilibs/css/login-style.css') }}?v={{ time() }}">
    <!-- Logo  -->
    <link rel="shortcut icon" type="" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
</head>

<body>

    <!-- Dynamic Animated Canvas Background -->
    <canvas id="particleCanvas"></canvas>

    <div class="login-card">
        <div class="card-header">
            <div class="brand-logo">
                <i class="ti ti-git-pull-request"></i>
            </div>
            <h2>Ticketing System</h2>
            <p>Enter your credentials to access your portal</p>
        </div>

        <form class="login-form" action="{{ route('postLogin') }}" method="POST" novalidate>
            @csrf
            <!-- Email / Username Field -->
            <div class="input-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="ti ti-mail input-icon"></i>
                    <input type="text" id="email" name="email" placeholder="name@company.com" required>
                </div>
            </div>

            <!-- Password Field -->
            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="ti ti-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" id="togglePasswordBtn" class="toggle-password" aria-label="Toggle Password">
                        <i class="ti ti-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Options -->
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">
                <span>Sign In</span>
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>

        <div class="card-footer">
            <h6 style="font-size: 7pt">Maintained and Managed by Management Information System Office (MISO) under the Leadership of Dr. Aladino C. Moraca.</h6>
        </div>
    </div>

    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('uilibs/js/main.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('uilibs/js/login.js') }}"></script>
</body>

</html>
