<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Log in to the CPSU Service Now ticketing system to file, track, and resolve IT requests.">

    <title>Log in — Service Now Ticketing System</title>

    <link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/login-ui.css') }}?v={{ time() }}">
    <link rel="shortcut icon" type="" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
</head>

<body class="login-b">
    <a class="skip-link" href="#login-form">Skip to login form</a>

    <main class="loginb-wrap">
        <div class="loginb-card">
            <span class="loginb-logo" aria-hidden="true"><img src="{{ asset('uilibs/images/cpsulogov4.png') }}" alt="" width="64" height="64"></span>
            <h1>Log in</h1>
            <p class="loginb-sub">Continue to Service Now ticketing.</p>

            @if (session('error'))
                <div class="loginb-alert" role="alert">
                    <i class="ti ti-alert-circle" aria-hidden="true"></i>
                    <span>{{ session('error') }}. Check your email and password, then try again.</span>
                </div>
            @endif

            <form id="login-form" class="loginb-form" action="{{ route('postLogin') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <div class="control">
                        <i class="ti ti-mail" aria-hidden="true"></i>
                        <input type="email" id="email" name="email" inputmode="email"
                            autocomplete="username email" placeholder="you@cpsu.edu.ph"
                            value="{{ old('email') }}" required autofocus aria-required="true"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    </div>
                    @error('email')
                        <small id="email-error" class="field-error" role="alert">{{ $message }}</small>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="control">
                        <i class="ti ti-lock" aria-hidden="true"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            autocomplete="current-password" required minlength="5" maxlength="12" aria-required="true"
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button type="button" id="togglePasswordBtn" class="toggle-password"
                            aria-label="Show password" aria-pressed="false" aria-controls="password">
                            <i class="ti ti-eye" id="toggleIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <small id="password-error" class="field-error" role="alert">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    <label class="remember" for="remember">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    <span class="contact-miso" title="Password resets are handled by the MISO office">Forgot password?</span>
                </div>

                <button type="submit" class="submit-btn" id="loginSubmit">
                    <span class="spin" aria-hidden="true"></span>
                    <span>Log in</span>
                    <i class="ti ti-arrow-right arrow" aria-hidden="true"></i>
                </button>
            </form>

            <p class="loginb-foot">Maintained by the Management Information System Office (MISO).</p>
        </div>
    </main>

    <script type="text/javascript" src="{{ asset('uilibs/js/main.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('uilibs/js/login.js') }}"></script>
</body>

</html>
